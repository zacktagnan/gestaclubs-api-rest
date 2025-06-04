<?php

namespace App\Http\Controllers\API\V1\Management;

use App\Exceptions\API\V1\ClubAlreadyHasCoachException;
use App\Exceptions\API\V1\ClubBudgetExceededException;
use App\Exceptions\API\V1\ClubHasMembersException;
use App\Exceptions\API\V1\CoachAlreadyAssignedException;
use App\Exceptions\API\V1\PlayerAlreadyAssignedException;
use App\Http\Requests\API\V1\Club\ClubSignCoachRequest;
use App\Models\Club;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\API\V1\ClubResource;
use App\Services\API\V1\ApiResponseService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\API\V1\Club\StoreClubRequest;
use App\Http\Requests\API\V1\Club\UpdateClubRequest;
use App\Http\Requests\API\V1\Club\ClubSignPlayerRequest;
use App\Http\Requests\API\V1\Club\UpdateClubBudgetRequest;
use App\Http\Resources\API\V1\CoachResource;
use App\Http\Resources\API\V1\PlayerResource;
use App\Models\Coach;

class ClubController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $clubs = Club::with('coach')
            ->withCount('players')
            ->paginate();

        return ApiResponseService::success(
            ClubResource::collection($clubs),
            message: 'Clubs retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClubRequest $request): JsonResponse
    {
        $club = Club::create($request->validated());

        return ApiResponseService::success(
            new ClubResource($club),
            message: 'Club created successfully.',
            code: Response::HTTP_CREATED,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Club $club): JsonResponse
    {
        $club->load('coach')->loadCount('players');

        return ApiResponseService::success(
            new ClubResource($club),
            message: 'Club retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClubRequest $request, Club $club): JsonResponse
    {
        $club->update($request->validated());

        return ApiResponseService::success(
            new ClubResource($club),
            message: 'Club updated successfully.'
        );
    }

    public function updateBudget(UpdateClubBudgetRequest $request, Club $club): JsonResponse
    {
        $club->update([
            'budget' => data_get($request->validated(), 'budget', $club->budget),
        ]);

        return ApiResponseService::success(
            new ClubResource($club),
            message: 'Club budget updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club): JsonResponse
    {
        if ($club->players()->exists() || $club->coach) {
            throw new ClubHasMembersException(
                'This Club still has Players or a Coach assigned, so it cannot be deleted.'
            );
        }

        $club->delete();

        return ApiResponseService::success(
            data: null,
            message: 'Club deleted successfully.'
        );
    }

    public function signPlayer(ClubSignPlayerRequest $request, Club $club): JsonResponse
    {
        $validated = $request->validated();
        $playerId = $validated['player_id'];
        $playerSalary = $validated['salary'];

        $player = Player::findOrFail($playerId);

        if ($player->club_id) {
            if ($player->club_id === $club->id) {
                throw new PlayerAlreadyAssignedException(
                    "This Player is already assigned to this Club ({$club->name})."
                );
            }

            throw new PlayerAlreadyAssignedException(
                "This Player is already assigned to another Club ({$player->club->name})."
            );
        }

        $usedBudget = $club->players()->sum('salary') + optional($club->coach)->salary;
        if (($usedBudget + $playerSalary) > $club->budget) {
            throw new ClubBudgetExceededException('Club has not enough budget for this Player signing.');
        }

        $player->club_id = $club->id;
        $player->salary = $playerSalary;
        $player->save();

        return ApiResponseService::success(
            new PlayerResource($player),
            message: 'Club has signed the Player.'
        );
    }

    public function signCoach(ClubSignCoachRequest $request, Club $club): JsonResponse
    {
        $validated = $request->validated();
        $coachId = $validated['coach_id'];
        $coachSalary = $validated['salary'];

        $coach = Coach::findOrFail($coachId);

        if ($club->coach) {
            throw new ClubAlreadyHasCoachException("This Club already has a Coach assigned ({$club->coach->name}).");
        }

        if ($coach->club_id) {
            if ($coach->club_id === $club->id) {
                throw new CoachAlreadyAssignedException(
                    "This Coach is already assigned to this Club ({$club->name})."
                );
            }

            throw new CoachAlreadyAssignedException(
                "This Coach is already assigned to another Club ({$coach->club->name})."
            );
        }

        $usedBudget = $club->players()->sum('salary');
        if (($usedBudget + $coachSalary) > $club->budget) {
            throw new ClubBudgetExceededException('Club has not enough budget for this Coach signing.');
        }

        $coach->club_id = $club->id;
        $coach->salary = $coachSalary;
        $coach->save();

        return ApiResponseService::success(
            new CoachResource($coach),
            message: 'Club has signed the Coach.'
        );
    }
}
