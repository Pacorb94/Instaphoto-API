<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
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
        $data=$request->validated();
        $data=array_map('trim', $data);
        //Ciframos la contraseña
        $data['password']=hash('sha256', $data['password']);
        $user=new User($data);
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
        $data=$request->validated();
        //Quitamos los espacios
        $data=array_map('trim', $data);
        //TODO: ver Laravel Santum
        return response('token'); 
    }

    /**
     * Función que modifica un usuario
     * @param $request
     * @return
     */
    public function update($id, Request $request)
    {
        //Si el id de la petición es igual al del usuario logueado
        if ($id && is_numeric($id) && $id == auth()->user()->id) {
            //Decodificamos el json a un array con el parámetro true
            $decodedRequest = json_decode($request->input('json', null), true);
            //Quitamos los espacios de delante y detrás
            $decodedRequest = array_map('trim', $decodedRequest);
            if ($decodedRequest) {
                $validator = $this->validations($decodedRequest, 'update');
                if ($validator->fails()) return response($validator->errors(), 400);
                //Si el usuario no modifica algún campo será el por defecto
                $decodedRequest['name']??auth()->user()->name;
                $decodedRequest['surname']??auth()->user()->surname;
                $decodedRequest['email']??auth()->user()->email;
                $decodedRequest['nick']??auth()->user()->nick;
                //Con tap podemos devolver el usuario modificado
                $userUpdated=tap(User::find(auth()->user()->id))->update($decodedRequest);
                if ($userUpdated) return response($userUpdated);
            }
            return response(['message' => 'Wrong json'], 400);
        }
        return response(['message' => 'Wrong id'], 400);
    }

    /**
     * Función que sube una imagen de perfil
     * @param $request
     * @return
     */
    public function uploadProfileImage(Request $request)
    {
        $validator=$this->validations($request->all(), 'uploadProfileImage');
        if ($validator->fails()) return response($validator->errors(), 400);
        $image=$request->file('file0');
        //Debemos configurar la fecha y tiempo
        date_default_timezone_set('Europe/Madrid');
        $imageName=date('d-m-Y_H-i-s').'_'.$image->getClientOriginalName();
        //Almacenamos la imagen en la carpeta
        Storage::disk('profile-images')->put($imageName, File::get($image));
        return response(['image'=>$imageName], 201);
    }

    /**
     * Función que obtiene la imagen de perfil
     * @param $imageName
     * @return
     */
    public function getProfileImage($imageName)
    {
        if ($imageName) {
            $exists=Storage::disk('profile-images')->exists($imageName);
            if ($exists) {
                $image=Storage::disk('profile-images')->get($imageName);
                return new Response($image);
            } 
            return response(['message'=>'No exists an image with that name'], 404); 
        }
        return response(['message'=>'You must send an image name'], 400);
    }

    /**
     * Función que obtiene un usuario
     * @param $id
     * @return
     */
    public function getUser($id)
    {
        if ($id&&is_numeric($id)) {
            $user=User::find($id);
            //Si existe y es el mismo que se ha logueado
            if ($user&&$user->id==auth()->user()->id) return response($user);         
            return response(['message'=>'Wrong user'], 500);
        }
        return response(['message'=>'Wrong id'], 400);
    }

     /**
     * Función que busca usuarios por una palabra
     * @param $search
     * @return
     */
    public function searchUsers($search=null)
    {
        if ($search) {
            //orWhere es un or
            $users=User::where('nick', 'like' , "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('surname', 'like', "%$search%")
                        ->orderBy('id', 'desc')->paginate(5);
        } else {
            $users=User::orderBy('id', 'desc')->paginate(5);
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
                    'email' => 'email|unique:users,email,'.auth()->user()->id,
                    //Igual que el email
                    'nick'=>'unique:users,nick,'.auth()->user()->id
                ]
            );
        }else if($nameFunction=='uploadProfileImage'){
            $validator=Validator::make(
                $request,
                [
                    'file0'=>'required|image|mimes:jpg,jpeg,png,gif'
                ]
            );
        }
        return $validator;
    }
}
