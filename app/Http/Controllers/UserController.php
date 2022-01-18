<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;

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
        $data['password'] = hash('sha256', $data['password']);
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
        $data = $request->validated();
        $data = array_map('trim', $data);
        $user = User::where([
            'email' => $data['email'], 
            'password' => $data['password']
        ])->get();
        if ($user) {
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
    public function update($user, UpdateUserRequest $request)
    {
        //Modificamos la columna updated_at
        $user->touch();
        //Con tap obtenemos el usuario modificado
        $userUpdated=tap($user)->update($request->validated());
        return response(new UserResource($userUpdated));
    }

    /**
     * Función que sube una imagen de perfil
     * @param $request
     * @return
     */
    public function uploadProfileImage(Request $request)
    {
        $validator = $this->validations($request->all(), 'uploadProfileImage');
        if ($validator->fails()) return response($validator->errors(), 400);
        $image = $request->file('file0');
        //Debemos configurar la fecha y tiempo
        date_default_timezone_set('Europe/Madrid');
        $imageName = date('d-m-Y_H-i-s') . '_' . $image->getClientOriginalName();
        //Almacenamos la imagen en la carpeta
        Storage::disk('profile-images')->put($imageName, File::get($image));
        return response(['image' => $imageName], 201);
    }

    /**
     * Función que obtiene la imagen de perfil
     * @param $imageName
     * @return
     */
    public function getProfileImage($imageName)
    {
        if ($imageName) {
            $exists = Storage::disk('profile-images')->exists($imageName);
            if ($exists) {
                $image = Storage::disk('profile-images')->get($imageName);
                return new Response($image);
            }
            return response(['message' => 'No exists an image with that name'], 404);
        }
        return response(['message' => 'You must send an image name'], 400);
    }

    /**
     * Función que busca usuarios por una palabra
     * @param $search
     * @return
     */
    public function searchUsers($search = null)
    {
        if ($search) {
            //orWhere es un or
            $users = User::where('nick', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%")
                ->orWhere('surname', 'like', "%$search%")
                ->orderBy('id', 'desc')->paginate(5);
        } else {
            $users = User::orderBy('id', 'desc')->paginate(5);
        }
        return response($users);
    }

    /**
     * Función que hace la validación
     * @param $request
     * @param $nameFunction
     * @return
     */
    public function validations($request, $nameFunction)
    {
        if ($nameFunction == 'register') {
            $validator = Validator::make(
                $request,
                [
                    'name' => 'required|regex:/^[a-zA-ZñáéíóúÑÁÉÍÓÚ ]*$/',
                    'surname' => 'required|regex:/^[a-zA-ZñáéíóúÑÁÉÍÓÚ ]*$/',
                    'nick' => 'required|unique:users,nick',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required'
                ]
            );
        } else if ($nameFunction == 'login') {
            $validator = Validator::make(
                $request,
                [
                    'email' => 'required|email|exists:users,email',
                    'password' => 'required'
                ]
            );
        } else if ($nameFunction == 'update') {
            $validator = Validator::make(
                $request,
                [
                    'name' => 'regex:/^[a-zA-ZñáéíóúÑÁÉÍÓÚ ]*$/',
                    'surname' => 'regex:/^[a-zA-ZñáéíóúÑÁÉÍÓÚ ]*$/',
                    /*Si el usuario no modifica el email no fallará ya que unique hará una excepción
                    gracias al id*/
                    'email' => 'email|unique:users,email,' . auth()->user()->id,
                    //Igual que el email
                    'nick' => 'unique:users,nick,' . auth()->user()->id
                ]
            );
        } else if ($nameFunction == 'uploadProfileImage') {
            $validator = Validator::make(
                $request,
                [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
                ]
            );
        }
        return $validator;
    }
}
