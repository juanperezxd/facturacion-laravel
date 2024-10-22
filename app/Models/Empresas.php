<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresas extends Model
{
    protected $table = "empresas";
    protected $fillable = [
        'id','ruc','razon_social','nombre','direccion','fecha_inicio', 'cta_detraccion', 'porcentaje_detraccion', 'created_for','updated_for'
    ];
}
