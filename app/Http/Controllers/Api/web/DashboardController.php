<?php

namespace App\Http\Controllers\Api\web;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Colaboradores;
use App\Models\Contratos;
use App\Models\ColaboradoresContratos;
use App\Models\AsesoresContratos;
use App\Models\PreciosColaboradores;
use App\Models\HojaUnica;
use App\Models\Habilitacion;

class DashboardController extends Controller{
  public function dashboardEstados(){
    $contratos=Contratos::count();
    $datos=DB::select( DB::raw("SELECT es.vista,count(co.estados_id) as conteo FROM contratos as co inner join estados as es on co.estados_id=es.id group by co.estados_id order by co.estados_id asc") );
    return response()->json(array(
        'datos' => $datos,
        'contratos' => $contratos,
    ));
  }

  //AREA COMERCIAL
  public function obtenerAsesores(){
    $datos=DB::select( DB::raw("SELECT cl.nombre,count(co.asesores) as conteo FROM contratos as co inner join colaboradores as cl on co.asesores=cl.id group by co.asesores order by conteo desc") );
    return response()->json(array(
        'asesores' => $datos,
    ));
  }
  public function obtenerDistritos(){
    $distritos=DB::select( DB::raw("SELECT distrito,count(distrito) as conteo FROM contratos group by distrito order by conteo desc") );
    return response()->json(array(
        'distri' => $distritos,
    ));
  }
  public function obtenerEstratos(){
    $datos=DB::select( DB::raw("SELECT es.descripcion,count(co.estratos_id) as conteo FROM contratos as co inner join estratos as es on co.estratos_id=es.id group by co.estratos_id order by conteo desc") );
    return response()->json(array(
        'estratos' => $datos,
    ));
  }
  public function obtenerTipoInstalacion(){
    $datos=DB::select( DB::raw("SELECT pe.descripcion,count(co.precios_id) as conteo FROM contratos as co inner join precios as pe on co.precios_id=pe.id group by co.precios_id order by conteo desc") );
    return response()->json(array(
        'instalacion' => $datos,
    ));
  }
  //AREA INTERNA
  public function obtenerTecnicosInterna(){
    $datos=DB::select( DB::raw("SELECT cl.nombre,count(co.tecnicos) as conteo FROM contratos as co inner join colaboradores as cl on co.tecnicos=cl.id where co.estados_id >=5 and co.estados_id != 8  group by co.tecnicos order by conteo desc") );
    return response()->json(array(
        'tecnicos' => $datos,
    ));
  }
  public function obtenerDistritosInterna(){
    $distritos=DB::select( DB::raw("SELECT distrito,count(distrito) as conteo FROM contratos where estados_id >=5 and estados_id != 8 group by distrito order by conteo desc") );
    return response()->json(array(
        'distri' => $distritos,
    ));
  }
  public function obtenerEstratosInterna(){
    $datos=DB::select( DB::raw("SELECT es.descripcion,count(co.estratos_id) as conteo FROM contratos as co inner join estratos as es on co.estratos_id=es.id where co.estados_id >=5 and co.estados_id != 8 group by co.estratos_id order by conteo desc") );
    return response()->json(array(
        'estratos' => $datos,
    ));
  }
  public function obtenerTipoInstalacionInterna(){
    $datos=DB::select( DB::raw("SELECT pe.descripcion,count(co.precios_id) as conteo FROM contratos as co inner join precios as pe on co.precios_id=pe.id where co.estados_id >=5 and co.estados_id != 8 group by co.precios_id order by conteo desc") );
    return response()->json(array(
        'instalacion' => $datos,
    ));
  }
  //AREA HABILITACION
  public function obtenerTecnicosHabilitacion(){
    $datos=DB::select( DB::raw("SELECT cl.nombre,count(co.habilitadores) as conteo FROM contratos as co inner join colaboradores as cl on co.habilitadores=cl.id where co.estados_id >=16 group by co.habilitadores order by conteo desc") );
    return response()->json(array(
        'tecnicos' => $datos,
    ));
  }
  public function obtenerDistritosHabilitacion(){
    $distritos=DB::select( DB::raw("SELECT distrito,count(distrito) as conteo FROM contratos where estados_id >=16 group by distrito order by conteo desc") );
    return response()->json(array(
        'distri' => $distritos,
    ));
  }
  public function obtenerEstratosHabilitacion(){
    $datos=DB::select( DB::raw("SELECT es.descripcion,count(co.estratos_id) as conteo FROM contratos as co inner join estratos as es on co.estratos_id=es.id where co.estados_id >=16 group by co.estratos_id order by conteo desc") );
    return response()->json(array(
        'estratos' => $datos,
    ));
  }
  public function obtenerTipoInstalacionHabili(){
    $datos=DB::select( DB::raw("SELECT pe.descripcion,count(co.precios_id) as conteo FROM contratos as co inner join precios as pe on co.precios_id=pe.id where co.estados_id >=16 group by co.precios_id order by conteo desc") );
    return response()->json(array(
        'instalacion' => $datos,
    ));
  }

  //FUNCIONES DE MIGRACIONES
  public function cargarAsesores(){
    $contratos=Contratos::all();
    foreach($contratos as $contrato){
      $dataAsesor=Colaboradores::find($contrato->asesores);
      $precio=PreciosColaboradores::where('tipo','ASESOR')->where('sub_tipo','VENTAS')->where('origen_colaborador',$dataAsesor->origen_colaborador)->first();

      $carga=new AsesoresContratos();
      $carga->colaboradores_id=$contrato->asesores;
      $carga->contratos_id=$contrato->id;
      $carga->estado=NULL;
      $carga->fecha=$contrato->fecha;
      $carga->descripcion_pago=$precio->descripcion;
      $carga->monto=$precio->monto;
      $carga->fecha_pago_1=NULL;
      $carga->fecha_pago_2=NULL;
      $carga->fecha_pago_3=NULL;
      $carga->pago_1=$precio->pago1;
      $carga->pago_2=$precio->pago2;
      $carga->pago_3=$precio->pago3;
      $carga->pago_1_s=$precio->monto*($precio->pago1/100);
      $carga->pago_2_s=$precio->monto*($precio->pago2/100);
      $carga->pago_3_s=$precio->monto*($precio->pago3/100);
      $carga->estado_1='0';
      $carga->estado_2='1';
      $carga->estado_3='1';
      $carga->condicion_1=$precio->estado_contrato_1;
      $carga->condicion_2=$precio->estado_contrato_2;
      $carga->condicion_3=$precio->estado_contrato_3;
      $carga->created_for='2';
      $carga->updated_for='2';
      $carga->save();
    }
  }
  public function cargarTecnicos(){
    $contratos=Contratos::all();
    foreach($contratos as $contrato){
      $tecnico=$contrato->tecnicos;
      if($tecnico!=NULL){
        $dataTecnico=Colaboradores::find($tecnico);
        $precio=PreciosColaboradores::where('tipo','TECNICO')->where('sub_tipo','TECNICO DE INTERNAS')->where('origen_colaborador',$dataTecnico->origen_colaborador)->first();
        $carga=new ColaboradoresContratos();
        $carga->colaboradores_id=$contrato->tecnicos;
        $carga->contratos_id=$contrato->id;
        if($contrato->fecha_interna!=''){
          $carga->estado='construido';
        }else{
          $carga->estado=NULL;
        }
        $carga->tipo='INTERNA';
        $carga->fecha=$contrato->fecha_asignacion_tecnico_interna;
        $carga->campo='fecha_interna';
        $carga->descripcion_pago=$precio->descripcion;
        $carga->pago_2=$precio->pago2;
        $carga->monto=$precio->monto;
        $carga->fecha_pago_1=NULL;
        $carga->fecha_pago_2=NULL;
        $carga->fecha_pago_3=NULL;
        $carga->pago_1=$precio->pago1;
        $carga->pago_2=$precio->pago2;
        $carga->pago_3=$precio->pago3;
        $carga->pago_1_s=$precio->monto*($precio->pago1/100);
        $carga->pago_2_s=$precio->monto*($precio->pago2/100);
        $carga->pago_3_s=$precio->monto*($precio->pago3/100);
        $carga->estado_1='0';
        $carga->estado_2='1';
        $carga->estado_3='1';
        $carga->condicion_1=$precio->estado_contrato_1;
        $carga->condicion_2=$precio->estado_contrato_2;
        $carga->condicion_3=$precio->estado_contrato_3;
        $carga->observacion=NULL;
        $carga->foto1=NULL;
        $carga->foto2=NULL;
        $carga->created_for='2';
        $carga->updated_for='2';
        $carga->save();
      }
    }
  }
  public function cargarhabilitadores(){
    $contratos=Contratos::all();
    foreach($contratos as $contrato){
      $habilitador=$contrato->habilitadores;
      if($habilitador!=NULL){
        $dataTecnico=Colaboradores::find($habilitador);
        $precio=PreciosColaboradores::where('tipo','TECNICO')->where('sub_tipo','TECNICO DE HABILITACION')->where('origen_colaborador',$dataTecnico->origen_colaborador)->first();
        $carga=new ColaboradoresContratos();
        $carga->colaboradores_id=$contrato->habilitadores;
        $carga->contratos_id=$contrato->id;
        if($contrato->fecha_habilitacion!=''){
          $carga->estado='habilitado';
        }else{
          $carga->estado=NULL;
        }
        $carga->tipo='HABILITACION';
        $carga->fecha=$contrato->fecha_habilitacion;
        $carga->campo='fecha_habilitacion';
        $carga->descripcion_pago=$precio->descripcion;
        $carga->pago_2=$precio->pago2;
        $carga->monto=$precio->monto;
        $carga->fecha_pago_1=NULL;
        $carga->fecha_pago_2=NULL;
        $carga->fecha_pago_3=NULL;
        $carga->pago_1=$precio->pago1;
        $carga->pago_2=$precio->pago2;
        $carga->pago_3=$precio->pago3;
        $carga->pago_1_s=$precio->monto*($precio->pago1/100);
        $carga->pago_2_s=$precio->monto*($precio->pago2/100);
        $carga->pago_3_s=$precio->monto*($precio->pago3/100);
        $carga->estado_1='0';
        $carga->estado_2='1';
        $carga->estado_3='1';
        $carga->condicion_1=$precio->estado_contrato_1;
        $carga->condicion_2=$precio->estado_contrato_2;
        $carga->condicion_3=$precio->estado_contrato_3;
        $carga->observacion=NULL;
        $carga->foto1=NULL;
        $carga->foto2=NULL;
        $carga->created_for='2';
        $carga->updated_for='2';
        $carga->save();
      }
    }
  }
  public function cargarNumeros(){
    $contratos=Contratos::all();
    foreach($contratos as $contrato){
      $contratoEdit=$contrato->find($contrato->id);
      $hojaUnica=$hojaUnica=HojaUnica::where('contrato_id',$contrato->id)->first();
      $contratoEdit->numero=$hojaUnica->numero_d;
      $contratoEdit->save();
      echo $contrato->id.' '.$hojaUnica->numero_d.'<br>';
    }
  }
  public function cargarFilaHabilitadores(){
    $contratos=Contratos::all();
    foreach($contratos as $contrato){
      $contratoEdit=$contrato->find($contrato->id);
      $habilitador=Habilitacion::where('id',$contrato->id)->first();
      $contratoEdit->habilitadores=$habilitador->habilitadores;
      $contratoEdit->save();
      echo $contrato->id.' '.$habilitador->habilitadores.'<br>';
    }
  }
}
