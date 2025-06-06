<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Requests\User\CreateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends CoreController
{
    /**
     * @return mixed
     */
    public function getUsers(Request $request)
    {
        $data = $request->all();
        $users = User::where('role_id', User::CUSTOMER)
            ->orderBy('id', 'desc')
            ->paginate($data['perPage'] ?? 15);

        return $this->responseSuccess(['users' => $users]);
    }

    /**
     * Create User
     */
    public function createUser(CreateUserRequest $request)
    {
        $data = $request->all();
        $password = Hash::make('12345678');
        $user = User::create([
            'role_id' => User::CUSTOMER,
            'full_name' => $data['full_name'],
            'display_name' => $data['display_name'],
            'email' => $data['email'],
            'birth_day' => $data['birth_day'],
            'password' => $password,
        ]);

        return $this->responseSuccess(['user' => $user]);
    }
}
