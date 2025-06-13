<?php

namespace App\Http\Resources\API\V1;

use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Coach
 */
class CoachResource extends JsonResource
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

            'full_name' => $this->full_name,
            'email' => $this->email,
            'salary' => $this->salary,

            // -> Todos los datos
            // 'club' => new ClubResource($this->whenLoaded('club')),
            // -> Solo los datos necesarios
            'club' => $this->whenLoaded('club', function () {
                return [
                    'id' => $this->club->id,
                    'name' => $this->club->name,

                    'players_count' => $this->club->players_count,
                ];
            }),

            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}
