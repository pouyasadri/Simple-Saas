<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{

    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => $this->image ?: null,
            'name' => $this->name,
            'route_name' => $this->route_name,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'required_credits' => $this->required_credits,
        ];
    }
}
