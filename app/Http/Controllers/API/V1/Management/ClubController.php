<?php

namespace App\Http\Controllers\API\V1\Management;

use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\API\V1\ClubResource;
use App\Services\API\V1\ApiResponseService;
use App\Http\Resources\API\V1\CoachResource;
use App\Actions\API\V1\Club\DeleteClubAction;
use App\Http\Resources\API\V1\PlayerResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\API\V1\Club\StoreClubRequest;
use App\Http\Requests\API\V1\Club\UpdateClubRequest;
use App\Http\Requests\API\V1\Club\ClubSignCoachRequest;
use App\Http\Requests\API\V1\Club\ClubSignPlayerRequest;
use App\Http\Requests\API\V1\Club\UpdateClubBudgetRequest;
use App\DTOs\API\V1\Coach\WithRelationsDTO as CoachWithRelationsDTO;
use App\DTOs\API\V1\Player\WithRelationsDTO as PlayerWithRelationsDTO;
use App\Actions\API\V1\Club\SignCoach\Pipeline as ClubSignCoachPipeline;
use App\Actions\API\V1\Club\SignPlayer\Pipeline as ClubSignPlayerPipeline;
use App\Contracts\DomainUnprocessableException;

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
        $club = $club->loadCount('players');

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
        $club->loadCount('players');

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
        app(DeleteClubAction::class)->execute($club);

        return ApiResponseService::success(
            data: null,
            message: 'Club deleted successfully.'
        );
    }

    public function signPlayer(ClubSignPlayerRequest $request, Club $club): JsonResponse
    {
        $data = $request->validated();
        data_set($data, 'club', $club);

        try {
            // Toda la ejecución del pipeline se encierra en una transacción.
            // $passable = DB::transaction(function () use ($data) {
            //     return ClubSignPlayerPipeline::execute($data);
            // });
            // o
            $passable = DB::transaction(
                fn() => ClubSignPlayerPipeline::execute($data)
            );

            $player = PlayerWithRelationsDTO::from($passable->getPlayer());

            return ApiResponseService::success(
                new PlayerResource($player),
                message: 'Club has signed the Player.',
                code: Response::HTTP_CREATED,
            );
        } catch (DomainUnprocessableException $e) {
            return ApiResponseService::unprocessableEntity(
                message: $e->getMessage()
            );
        } catch (\Throwable $e) {
            // Cualquier excepción en el pipeline (incluyendo la notificación) revierte la transacción.
            return ApiResponseService::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public function signCoach(ClubSignCoachRequest $request, Club $club): JsonResponse
    {
        $data = $request->validated();
        data_set($data, 'club', $club);

        try {
            $passable = DB::transaction(
                fn() => ClubSignCoachPipeline::execute($data)
            );

            $coach = CoachWithRelationsDTO::from($passable->getCoach());

            return ApiResponseService::success(
                new CoachResource($coach),
                message: 'Club has signed the Coach.',
                code: Response::HTTP_CREATED,
            );
        } catch (DomainUnprocessableException $e) {
            return ApiResponseService::unprocessableEntity(
                message: $e->getMessage()
            );
        } catch (\Throwable $e) {
            // Cualquier excepción en el pipeline (incluyendo la notificación) revierte la transacción.
            return ApiResponseService::internalServerError(
                message: $e->getMessage()
            );
        }
    }
}
