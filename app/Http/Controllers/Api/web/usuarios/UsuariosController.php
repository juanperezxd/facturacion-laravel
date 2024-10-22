<?php

namespace App\Http\Controllers\Api\web\usuarios;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;

use App\Transformers\UsersTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class UsuariosController extends Controller
{
    
    private $fractal;
    private $usersTransformer;

    function __construct(Manager $fractal, UsersTransformer $usersTransformer)
    {
        $this->fractal = $fractal;
        $this->usersTransformer = $usersTransformer;
    }

    public function index(Request $request)
    {
        //filtros
        $name = '';
        if ($request->name_like) {
            $name = $request->name_like;
        }

        $usersPaginator = User::where('name', 'like', '%' . $name . '%')
                        ->where('id', '!=', 1)
                        ->paginate(20);

        $users =  new Collection($usersPaginator->items(), $this->usersTransformer);
        $users->setPaginator(new IlluminatePaginatorAdapter($usersPaginator));

        $users = $this->fractal->createData($users);
        return $users->toArray();
    }
    
    
    public function store(Request $request)
    {
        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->documento = $request->documento;
        $usuario->password = bcrypt($request->password);
        $usuario->tipo_usuario = $request->tipo_usuario;
        $usuario->intentos = 0;
        $usuario->estado = $request->estado;
        $usuario->fill($request->all());
        if($usuario->save()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }
    
    public function show($id)
    {
        $user = User::where('id', $id)->first();    
        return response()->json($user);
    }

    
    public function update(Request $request, $id)
    {
        $usuario = User::find($id);
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->estado = $request->estado;
        $usuario->documento = $request->documento;
        $usuario->tipo_usuario = $request->tipo_usuario;
        if ($request->password != '') {
            $usuario->password = bcrypt($request->password);
        }
        $usuario->fill($request->all());
        if($usuario->save()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    
    public function destroy($id)
    {
        $usuario = User::find($id);
        if($usuario->delete()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }
}
