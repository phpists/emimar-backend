<?php

namespace App\Http\Resources\Group;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $return = [
            'id' => $this->id,
            'title' => $this->title,
            'create_at' => Carbon::parse($this->created_at)->format('d.m.Y'),
            'user' => $this->users
        ];

        return $return;
    }
}
