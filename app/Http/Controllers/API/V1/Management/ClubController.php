<?php

namespace App\Http\Controllers\API\V1\Management;

use App\Models\Club;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\API\V1\ClubResource;
use App\Services\API\V1\ApiResponseService;
use App\Http\Resources\API\V1\CoachResource;
use App\Http\Resources\API\V1\PlayerResource;
use Symfony\Component\HttpFoundation\Response;
use App\Actions\API\V1\Club\SignPlayer\Pipeline as ClubSignPlayerPipeline;
use App\Actions\API\V1\Club\SignCoach\Pipeline as ClubCoachPlayerPipeline;
use App\Exceptions\API\V1\ClubHasMembersException;
use App\Http\Requests\API\V1\Club\StoreClubRequest;
use App\Http\Requests\API\V1\Club\UpdateClubRequest;
use App\Http\Requests\API\V1\Club\ClubSignCoachRequest;
use App\Http\Requests\API\V1\Club\ClubSignPlayerRequest;
use App\Http\Requests\API\V1\Club\UpdateClubBudgetRequest;

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
        $data = $request->validated();
        data_set($data, 'club', $club);

        $passable = ClubSignPlayerPipeline::execute($data);

        return ApiResponseService::success(
            new PlayerResource($passable->getPlayer()),
            message: 'Club has signed the Player.'
        );
    }

    public function signCoach(ClubSignCoachRequest $request, Club $club): JsonResponse
    {
        $data = $request->validated();
        data_set($data, 'club', $club);

        $passable = ClubCoachPlayerPipeline::execute($data);

        return ApiResponseService::success(
            new CoachResource($passable->getCoach()),
            message: 'Club has signed the Coach.'
        );
    }
}
