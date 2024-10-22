<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidades extends Model
{
    protected $table = "unidades";
    protected $fillable = [
        'simbolo',
        'nombre',
        'estado',
        'created_for',
        'updated_for'
    ];

    public function productos(){
        return $this->hasMany('App\Models\Productos');
    }
}
