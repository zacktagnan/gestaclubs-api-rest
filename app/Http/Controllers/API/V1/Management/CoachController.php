<?php

namespace App\Http\Controllers\API\V1\Management;

use App\Models\Coach;
use Illuminate\Http\Request;
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
        $coachs = Coach::with('club')
            ->paginate();

        return ApiResponseService::success(
            CoachResource::collection($coachs),
            message: 'Coachs retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
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
    public function update(Request $request, Coach $coach): JsonResponse
    {
        $coach->update($request->validated());

        return ApiResponseService::success(
            new CoachResource($coach),
            message: 'Coach updated successfully.'
        );
    }

    public function unassignClub(Coach $coach): JsonResponse
    {
        $coach->club_id = null;
        $coach->save();

        return ApiResponseService::success(
            new CoachResource($coach),
            message: 'Coach unassigned from Club successfully.'
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
