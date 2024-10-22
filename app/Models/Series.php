<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $table = "series";
    protected $fillable = [
    'id','descripcion','tipo_documento','serie','correlativo','estado','created_at','updated_at'
    ];
}
