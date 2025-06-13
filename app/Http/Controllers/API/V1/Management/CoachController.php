<?php

namespace App\Http\Controllers\API\V1\Management;

use App\Models\Coach;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\API\V1\ApiResponseService;
use App\Http\Resources\API\V1\CoachResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\API\V1\Coach\StoreCoachRequest;
use App\Http\Requests\API\V1\Coach\UpdateCoachRequest;
use App\Actions\API\V1\Coach\RemoveFromClub\Pipeline as RemoveFromClubPipeline;

class CoachController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $coaches = Coach::with('club')
            ->paginate();

        return ApiResponseService::success(
            CoachResource::collection($coaches),
            message: 'Coaches retrieved successfully.'
        );
    }

    public function unassignedList()
    {
        $coaches = Coach::whereNull('club_id')
            ->paginate();

        return ApiResponseService::success(
            CoachResource::collection($coaches),
            message: 'Coaches without assigned club retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoachRequest $request): JsonResponse
    {
        $coach = Coach::create($request->validated());

        return ApiResponseService::success(
            new CoachResource($coach),
            message: 'Coach created successfully.',
            code: Response::HTTP_CREATED,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Coach $coach): JsonResponse
    {
        $coach->load('club');

        return ApiResponseService::success(
            new CoachResource($coach),
            message: 'Coach retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoachRequest $request, Coach $coach): JsonResponse
    {
        $coach->update($request->validated());

        return ApiResponseService::success(
            new CoachResource($coach),
            message: 'Coach updated successfully.'
        );
    }

    public function removeFromClub(Coach $coach): JsonResponse
    {
        try {
            $passable = DB::transaction(
                fn() => RemoveFromClubPipeline::execute($coach)
            );

            return ApiResponseService::success(
                new CoachResource($passable->getCoach()),
                message: 'Coach has been fired from Club successfully.'
            );
        } catch (\Throwable $e) {
            return ApiResponseService::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coach $coach): JsonResponse
    {
        $coach->delete();

        return ApiResponseService::success(
            data: null,
            message: 'Coach deleted successfully.'
        );
    }
}
