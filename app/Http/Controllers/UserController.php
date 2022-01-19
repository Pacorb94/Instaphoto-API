<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UploadProfileImageRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Función que hace el registro
     * @param $request
     * @return
     */
    public function register(RegisterRequest $request)
    {
        //Obtenemos los datos validados
        $data = $request->validated();
        $data = array_map('trim', $data);
        //Ciframos la contraseña
        $data['password'] = Hash::make($data['password']);
        $user = new User($data);
        $user->save();
        return response(new UserResource($user), 201);
    }

    /**
     * Función que hace el login
     * @param $request
     * @return
     */
    public function login(LoginRequest $request)
    {
        //Obtenemos los datos validados
        $data = array_map('trim', $request->input());
        $user = User::where('email', $data['email'])->first();
        if ($user && Hash::check($data['password'], $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response(['token' => $token]);
        }
        return response(['message' => 'Wrong credentials'], 422);
    }

    /**
     * Función que modifica un usuario
     * @param $user
     * @param $request
     * @return
     */
    public function update(User $user, UpdateUserRequest $request)
    {
        $data = array_map('trim', $request->validated());
        //Modificamos la columna updated_at
        $user->touch();
        //Con tap obtenemos el usuario modificado
        $userUpdated = tap($user)->update($data);
        return response(new UserResource($userUpdated));
    }

    /**
     * Función que sube una imagen de perfil
     * @param $request
     * @return
     */
    public function uploadProfileImage(UploadProfileImageRequest $request)
    {
        $data = $request->validated();
        $image = $data['file'];
        //Debemos configurar la fecha y tiempo
        date_default_timezone_set('Europe/Madrid');
        $imageName = date('d-m-Y_H-i-s') . '_' . $image->getClientOriginalName();
        //Almacenamos la imagen en la carpeta
        Storage::disk('profile-images')->put($imageName, File::get($image));
        return response(['image' => $imageName], 201);
    }

    /**
     * Función que obtiene la imagen de perfil
     * @param $request
     * @return
     */
    public function getProfileImage($imageName)
    {
        return response($imageName);
        $folder = Storage::disk('profile-images');
        if ($folder->exists($imageName)) {
            $image = Storage::disk('profile-images')->get($imageName);
            return new Response($image);
        }
        return response(['message' => 'Profile image not found'], 404);
    }
    
    /**
     * Función que obtiene un usuario
     * @param $user
     * @return
     */
    public function getUser(User $user)
    {
        return response(new UserResource($user));
    }

    /**
     * Función que busca usuarios por una palabra
     * @param $search
     * @return
     */
    public function searchUsers($nick)
    {
        $users = null;
        if ($nick) {
            $nick = trim($nick);
        }
        $users = User::where('nick', 'like', "%$nick%")
            ->orderBy('id', 'desc')
            ->paginate(5);
        return response($users);
    }
}
