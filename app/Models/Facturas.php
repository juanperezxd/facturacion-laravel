<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Facturas extends Model{
  protected $table='facturas';
  protected $fillable = [
    'cliente_setTipoDoc','cliente_setNumDoc',
    'cliente_setRznSocial','cliente_setDireccion',
    'setTipoOperacion','setTipoDoc',
    'setSerie','setCorrelativo',
    'setFechaEmision','setTipoMoneda',
    'setMtoOperGravadas','setMtoIGV',
    'setTotalImpuestos', 'setValorVenta',
    'setMtoImpVenta','legend_setCode',
    'legend_setValue','estado','pagos_id',
    'detraccion', 'dias_credito', 'forma_pago', 
    'cod_bien_detraccion', 'monto_detraccion',
    'dias'
  ];
  public function items(){
    return $this->hasMany('App\Models\Items');
  }
  public function contratos(){
    return $this->belongsTo('App\Models\Contratos');
  }

  public function movimientos(){
    return $this->hasMany('App\Models\Movimientos');
  }

  public function cajas(){
    return $this->hasMany('App\Models\Cajas');
  }

  //SCOPE
  public function scopeBuscarContrato($query,$contrato_id){
    $query->with('items')->where('contratos_id',$contrato_id)->where('estado','1')->where('setTipoDoc','03');
  }
  public function scopeBuscarPendientesPorDia($query,$fecha_resumen){
    $query->where('setFechaEmision',$fecha_resumen)->where('ticket',null);
  }

  public function scopeBuscarPendientesPorDiaResumen($query,$fecha_resumen){
    $query->where('setFechaEmision',$fecha_resumen)->where('setSerie','like','B%')->where('ticket',null);
  }
  public function scopeBoletas($query){
    $query->where('setTipoDoc','03');
  }
  public function scopeNotas($query){
    $query->where('setTipoDoc','07');
  }
}
