<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\Traits\HasFullInfoFlag;
use App\Http\Resources\Traits\HasPaginatorResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectsResource extends JsonResource
{
    use HasPaginatorResourceCollection, HasFullInfoFlag;

    public function toArray(Request $request): array
    {
        return $this->returnPaginatedResource(function ($item, $key) {
            return new ProjectResource($item);
        });
    }
}
