<?php

namespace App\Http\Controllers\API\V1\Management;

use App\Models\Club;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\API\V1\ClubResource;
use App\Services\API\V1\ApiResponseService;
use App\Http\Requests\API\V1\Club\StoreClubRequest;
use App\Http\Requests\API\V1\Club\UpdateClubBudgetRequest;
use App\Http\Requests\API\V1\Club\UpdateClubRequest;
use Symfony\Component\HttpFoundation\Response;

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
        $club->delete();

        return ApiResponseService::success(
            data: null,
            message: 'Club deleted successfully.'
        );
    }
}
