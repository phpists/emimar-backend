<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
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

    public function updateUser(UpdateUserRequest $request)
    {
        $data = $request->all();
        $user = User::find($data['user_id']);

        if ($user) {
            $user->update([
                'role_id' => User::CUSTOMER,
                'full_name' => $data['full_name'],
                'display_name' => $data['display_name'],
                'email' => $data['email'],
                'birth_day' => $data['birth_day'],
            ]);
        }

        return $this->responseSuccess([
            'message' => 'Данные успешно обновлены!',
            'user' => $user
        ]);
    }

    /**
     * Delete user
     * @param Request $request
     * @return mixed
     */
    public function deleteUser(Request $request)
    {
        $data = $request->all();
        $user = User::where('role_id', User::CUSTOMER)
            ->where('id', $data['id'])
            ->first();

        if ($user) {
            $user->delete();
        } else {
            return $this->responseError('Пользователь не найден');
        }

        return $this->responseSuccess([
            'message' => 'Пользователь успешно удален!',
        ]);
    }
}
