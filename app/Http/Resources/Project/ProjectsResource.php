<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\Traits\HasFullInfoFlag;
use App\Http\Resources\Traits\HasResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectsResource extends JsonResource
{
    use HasResourceCollection, HasFullInfoFlag;

    public function toArray(Request $request): array
    {
        return $this->returnResource(function ($item, $key) {
            return new ProjectResource($item);
        });
    }
}
