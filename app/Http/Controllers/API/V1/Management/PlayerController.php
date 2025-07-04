<?php

namespace App\Http\Controllers\API\V1\Management;

use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\API\V1\ApiResponseService;
use App\Http\Resources\API\V1\PlayerResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\API\V1\Player\StorePlayerRequest;
use App\Http\Requests\API\V1\Player\UpdatePlayerRequest;
use App\Actions\API\V1\Player\RemoveFromClub\Pipeline as RemoveFromClubPipeline;
use App\DTOs\API\V1\Player\WithRelationsDTO as PlayerWithRelationsDTO;

class PlayerController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $players = Player::filteredWithPipeline()
            ->with([
                'club' => fn($query) => $query
                    ->with('coach')
                    ->withCount('players'),
            ])
            ->paginate();

        return ApiResponseService::success(
            PlayerResource::collection($players),
            message: 'Players retrieved successfully.'
        );
    }

    public function unassignedList()
    {
        $players = Player::filteredWithPipeline()
            ->whereNull('club_id')
            ->paginate();

        return ApiResponseService::success(
            PlayerResource::collection($players),
            message: 'Players without assigned club retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlayerRequest $request): JsonResponse
    {
        $player = Player::create($request->validated());

        return ApiResponseService::success(
            new PlayerResource($player),
            message: 'Player created successfully.',
            code: Response::HTTP_CREATED,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Player $player): JsonResponse
    {
        $player = PlayerWithRelationsDTO::from($player);

        return ApiResponseService::success(
            new PlayerResource($player),
            message: 'Player retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlayerRequest $request, Player $player): JsonResponse
    {
        $player->update($request->validated());

        return ApiResponseService::success(
            new PlayerResource($player),
            message: 'Player updated successfully.'
        );
    }

    public function removeFromClub(Player $player): JsonResponse
    {
        try {
            $passable = DB::transaction(
                fn() => RemoveFromClubPipeline::execute($player)
            );

            return ApiResponseService::success(
                new PlayerResource($passable->getPlayer()),
                message: 'Player has been released from Club successfully.'
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
    public function destroy(Player $player): JsonResponse
    {
        $player->delete();

        return ApiResponseService::success(
            data: null,
            message: 'Player deleted successfully.'
        );
    }
}
