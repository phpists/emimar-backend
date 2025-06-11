<?php

namespace App\Http\Resources\Group;


use App\Http\Resources\Traits\HasFullInfoFlag;
use App\Http\Resources\Traits\HasResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupsResource extends JsonResource
{
    use HasResourceCollection, HasFullInfoFlag;

    public function toArray($request)
    {
        return $this->returnResource(function ($item, $key) {
            return new GroupResource($item);
        });
    }
}
