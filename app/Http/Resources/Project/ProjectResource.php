<?php

namespace App\Http\Resources\Project;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $return = [
            'id' => $this->id,
            'title' => $this->title,
            'create_at' => Carbon::parse($this->created_at)->format('d.m.Y'),
            'user' => $this->users,
            'groups' => $this->groups,
        ];

        return $return;
    }
}
