<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escenarios extends Model
{
    protected $table = "escenarios";
    protected $fillable = [
        'nombre',
        'estado',
        'created_for',
        'updated_for'
    ];

    public function mesas(){
        return $this->hasMany('App\Models\Mesas');
    }
}
