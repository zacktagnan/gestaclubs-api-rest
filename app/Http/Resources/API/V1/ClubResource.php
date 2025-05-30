<?php

namespace App\Http\Resources\API\V1;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Club
 */
class ClubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'budget' => $this->budget,

            'coach' => new CoachResource($this->whenLoaded('coach')),
            // 'coach' => $this->whenLoaded('coach', function () {
            //     return [
            //         'full_name' => $this->coach->full_name,
            //         'email' => $this->coach->email,
            //     ];
            // }),
            'players_count' => $this->players_count,

            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
