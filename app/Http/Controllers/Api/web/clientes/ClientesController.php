<?php

namespace App\Http\Controllers\Api\web\clientes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use App\Models\User;
use App\Models\Clientes;

//paginacion fractal
use App\Transformers\ClientesTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

//Consulta sunat
use Peru\Sunat\Ruc;
use Peru\Http\ContextClient;
use Peru\Jne\Dni;

class ClientesController extends Controller
{
    private $fractal;
    private $clientesTransformer;

    function __construct(Manager $fractal, ClientesTransformer $clientesTransformer)
    {
        $this->fractal = $fractal;
        $this->clientesTransformer = $clientesTransformer;
    }

    public function index(Request $request)
    {
      //filtros
      $order='ASC';
      if ($request->_order) {
          $order = $request->_order;
      }
      $row='id';
      if ($request->_sort) {
          $row = $request->_sort;
      }
      $razon_social = '';
      if ($request->razon_social_like) {
          $razon_social = $request->razon_social_like;
      }
     
        //paginacion
        $clientesPaginator = Clientes::where('razon_social', 'like', '%' . $razon_social . '%')
                                     ->orderby($row,$order)
                                     ->paginate(10);

        $clientes =  new Collection($clientesPaginator->items(), $this->clientesTransformer);
        $clientes->setPaginator(new IlluminatePaginatorAdapter($clientesPaginator));

        $clientes = $this->fractal->createData($clientes);
        return $clientes->toArray();
    }


    public function store(Request $request)
    {
        //verificamos si el documento esxiste en la bd
        $consultar = Clientes::where('documento', $request->documento)->first();
        if ($consultar == null) {
            $cliente = new Clientes();
            $cliente->fill($request->all());
            $cliente->documento = (string)$request->documento;
            $cliente->created_for = Auth::user()->id;
            $cliente->updated_for = Auth::user()->id;
            if($cliente->save()){
                return response()->json(array(
                    'mensaje' => 1,
                    'cliente' => $cliente
                ));
            }else{
                return response()->json(array(
                    'mensaje' => 2,
                ));
            }
        }else {
            return response()->json(array(
                'mensaje' => 3,
                'cliente' => $consultar
            ));
        }

        
    }


    public function show($id)
    {
        $cliente = Clientes::find($id);
        $data = [];
        if ($cliente != null) {
            $create_for = User::find($cliente->created_for);
            $update_for = User::find($cliente->created_for);

            $data = [
                'id' => $cliente->id,
                'tipo_documento' => $cliente->tipo_documento,
                'documento' => $cliente->documento,
                'razon_social' => $cliente->razon_social,
                'direccion' => $cliente->direccion,
                'tipo_cliente' => $cliente->tipo_cliente,
                'created_for' => $create_for->name,
                'update_for' => $update_for->name,
            ];
        }

        return response()->json($data);
    }


    public function update(Request $request, $id)
    {
        $consultar = Clientes::where('documento', $request->documento)->where('id','!=',$id)->first();

        if ($consultar == null) {
            $cliente = Clientes::find($id);
            $cliente->fill($request->all());
            $cliente->documento = (string)$request->documento;
            $cliente->updated_for = Auth::user()->id;
            if($cliente->save()){
                return response()->json(array(
                    'mensaje' => 1,
                    'cliente' => $cliente
                ));
            }else{
                return response()->json(array(
                    'mensaje' => 2
                ));
            }
        }else {
            return response()->json(array(
                'mensaje' => 3,
                'cliente' => $cliente
            ));
        }

        
    }

    public function destroy($id)
    {
        $cliente = Clientes::find($id);
        if($cliente->delete()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    //Consulta DNI/RUC
    public function consultarDocumento($documento, $tipo)
    {
        //tipo de documento
        if ($tipo == 'RUC') {
            $client = new \GuzzleHttp\Client;
            $url2  = 'https://api.apis.net.pe/v1/ruc?numero=' . $documento;
            $client = new \GuzzleHttp\Client;
            $response2 = $client->get($url2);
            $respuesta2 = json_decode($response2->getBody()->getContents());
            $data = [
                'razonSocial' => $respuesta2->nombre,
                'direccion' => $respuesta2->direccion,
            ];
            return response()->json(array(
                'empresa' => $data,
                'tipo' => 1,
                'error' => ''
            ));

        }elseif($tipo == 2){
       
        }elseif($tipo == 'DNI'){
            //RUC - P NATURAL
            $client = new \GuzzleHttp\Client;
            $url2  = 'https://api.apis.net.pe/v1/dni?numero=' . $documento;
            $client = new \GuzzleHttp\Client;
            $response2 = $client->get($url2);
            $respuesta2 = json_decode($response2->getBody()->getContents());
            $data = [
                'apellidoPaterno' => $respuesta2->apellidoPaterno,
                'apellidoMaterno' => $respuesta2->apellidoMaterno,
                'nombres' => $respuesta2->nombres
            ];
            return response()->json(array(
                'persona' => $data,
                'tipo' => 3,
                'error' => ''
            ));
        }
        
    }
}
