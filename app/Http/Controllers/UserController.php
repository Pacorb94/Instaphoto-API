<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UploadProfileImageRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{

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
        $data = array_map('trim', $request->validated());
        //Modificamos la columna updated_at
        $user->touch();
        //Con tap obtenemos el usuario modificado
        $userUpdated = tap($user)->update($data);
        return response(new UserResource($userUpdated));
    }

    public function uploadProfileImage(UploadProfileImageRequest $request)
    {
        $data = $request->validated();
        $image = $data['profile_image'];
        //Debemos configurar la fecha y tiempo
        date_default_timezone_set('Europe/Madrid');
        $imageName = date('d-m-Y_H-i-s') . '_' .
            preg_replace('/\s+/', '_', $image->getClientOriginalName());
        //Almacenamos la imagen en la carpeta
        Storage::disk('profile-images')->put($imageName, File::get($image));
        return response(['image' => $imageName], 201);
    }

    public function getProfileImage($imageName)
    {
        $folder = Storage::disk('profile-images');
        $imageName = trim($imageName);
        if ($folder->exists($imageName)) {
            $image = Storage::disk('profile-images')->get($imageName);
            return new Response($image);
        }
        return response(['message' => 'Profile image not found'], 404);
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