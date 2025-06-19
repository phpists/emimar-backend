<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $return = [
            'id' => $this->id,
            'role_id' => $this->role_id,
            'full_name' => $this->full_name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'birth_day' => $this->birth_day,
            'phone' => $this->phone,
            'create_at' => Carbon::parse($this->created_at)->format('d.m.Y'),
        ];

        return $return;
    }
}
