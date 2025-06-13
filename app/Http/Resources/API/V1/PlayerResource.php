<?php

namespace App\Http\Resources\API\V1;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Player
 */
class PlayerResource extends JsonResource
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

                    // -> Sacando los datos del Coach si existe, sino, NULL
                    // 'coach' => $this->club->relationLoaded('coach') && $this->club->coach
                    //     ? [
                    //         'full_name' => $this->club->coach->full_name,
                    //         'email' => $this->club->coach->email,
                    //     ]
                    //     : null,
                    // -> Sacando los datos del Coach si existe, sino, esta clave no se incluirá
                    'coach' => $this->when(
                        $this->club->relationLoaded('coach') && $this->club->coach,
                        fn() => [
                            'full_name' => $this->club->coach->full_name,
                            'email' => $this->club->coach->email,
                        ]
                    ),

                    'players_count' => $this->club->players_count,
                ];
            }),

            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}
