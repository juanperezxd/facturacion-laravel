<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesas extends Model
{
    protected $table = "mesas";
    protected $fillable = [
        'nombre',
        'escenarios_id',
        'estado',
        'created_for',
        'updated_for'
    ];

    public function escenarios(){
        return $this->belongsTo('App\Models\Escenarios');
    }

    public function ventas(){
        return $this->hasMany('App\Models\Ventas');
    }
}
