<?php

namespace App\Http\Controllers\API\V1\Management;

use App\Http\Requests\API\V1\Coach\StoreCoachRequest;
use App\Http\Requests\API\V1\Coach\UpdateCoachRequest;
use App\Models\Coach;
use Illuminate\Http\JsonResponse;
use App\Services\API\V1\ApiResponseService;
use App\Http\Resources\API\V1\CoachResource;
use Symfony\Component\HttpFoundation\Response;

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
        $coach->club_id = null;
        $coach->salary = null;
        $coach->save();

        return ApiResponseService::success(
            new CoachResource($coach),
            message: 'Coach has been dismissed from Club successfully.'
        );
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
