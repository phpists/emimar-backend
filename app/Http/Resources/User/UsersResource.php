<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Traits\HasFullInfoFlag;
use App\Http\Resources\Traits\HasPaginatorResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    use HasPaginatorResourceCollection, HasFullInfoFlag;

    public function toArray($request)
    {
        return $this->returnPaginatedResource(function ($item, $key) {
            return new UserResource($item);
        });
    }
}
