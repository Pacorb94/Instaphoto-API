<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Europe/Madrid');
    }

    public function register(RegisterRequest $request)
    {
        //Obtenemos los datos validados
        $data = $request->validated();
        $data = array_map('trim', $data);
        //Ciframos la contraseña
        $data['password'] = Hash::make($data['password']);
        $user = new User($data);
        $user->save();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response([
            'user' => new UserResource($user),
            'token_saved_as' => 'HTTPS Cookie'
        ])->withCookie(cookie('token', $token, 525600, '/', null, true, true));
    }

    public function login(LoginRequest $request)
    {
        //Obtenemos los datos validados
        $data = array_map('trim', $request->input());
        $user = User::where('email', $data['emailOrNick'])
            ->orWhere('nick', $data['emailOrNick'])->first();
        if ($user && Hash::check($data['password'], $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response([
                'user' => new UserResource($user),
                'token_saved_as' => 'HTTPS Cookie'
            ])->withCookie(cookie('token', $token, 525600, '/', null, true, true));
        }
        return response(['message' => 'Wrong credentials'], 401);
    }

    public function update(User $user, UpdateUserRequest $request)
    {
        $data = $request->validated();
        $dataWithOutSpaces=$data;
        unset($dataWithOutSpaces['profile_image']);
        $dataAux=array_map('trim', $dataWithOutSpaces);
        $user->fill($dataAux);
        //Borramos la antigua imagen
        Storage::disk('profile-images')->delete($user->profile_image);
        $user->profile_image = $this->moveImage($data['profile_image']);
        //Modificamos la columna updated_at
        $user->touch();
        $user->update();
        return response(new UserResource($user));
    }

    private function moveImage($image)
    {
        $imageName = date('d-m-Y_H-i-s') . '_' .
            preg_replace('/\s+/', '_', $image->getClientOriginalName());
        $image->move(storage_path() . '\app\profile-images\\', $imageName);
        return $imageName;
    }

    public function getProfileImage($imageName)
    {
        $exists = Storage::disk('profile-images')->exists($imageName);
        if ($exists) {
            $image = Storage::disk('profile-images')->get($imageName);
            return new Response($image);
        }
        return response(['message' => 'No exists an image with that name'], 404);
    }

    public function getUser(User $user)
    {
        return response(new UserResource($user));
    }

    public function searchUsersByNick($nick)
    {
        $nick = trim($nick);
        $users = User::where('nick', 'like', "%$nick%")
            ->orderBy('id', 'desc')
            ->paginate(5);
        return response(new UserCollection($users));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response(['message' => 'Logout successful']);
    }
}