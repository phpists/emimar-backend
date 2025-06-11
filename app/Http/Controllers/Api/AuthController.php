<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\CoreController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends CoreController
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->getCredentials();

        if (!Auth::validate($credentials)) {
            return $this->responseError('Данные не найдены');
        };

        $user = Auth::getProvider()->retrieveByCredentials($credentials);
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->responseSuccess([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        $user = User::find(Auth::id());
        return $this->responseSuccess(['user' => $user]);
    }

    /**
     * Розлогінювання
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->responseSuccess([
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * @param ResetPassword $request
     * @return mixed
     */
    public function resetPassword(ResetPassword $request)
    {
        $input = $request->all();
        $user = User::where('email', $input['email'])->first();

        if ($user) {
            $user->password = Hash::make($input['password']);
            if ($user->update()) {
                $token = $user->createToken('api-token')->plainTextToken;

                return $this->responseSuccess([
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ], 200);
            }
        } else {
            return $this->responseSuccess([
                'message' => 'Такого пользователя не существует',
            ], 429);
        }
    }
}


