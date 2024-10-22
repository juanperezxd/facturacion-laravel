<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impuestos extends Model
{
    protected $table = "impuestos";
    protected $fillable = [
        'nombre',
        'tasa',
        'estado',
        'created_for',
        'updated_for'
    ];

    public function productos(){
        return $this->hasMany('App\Models\Productos');
    }
}
