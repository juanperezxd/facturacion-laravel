<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'ape_paterno',
        'ape_materno',
        'email',
        'tipo',
        'dni',
        'departamento',
        'provincia',
        'distrito',
        'direccion',
        'tipo_user_id',
        'tipo_usuario',
        //permisos
        'permiso1',
        'permiso2',
        'permiso3',
        'permiso4',
        'permiso5',
        'permiso6',
        'permiso7',
        'permiso8',
        'permiso9',
        'permiso10',
        'permiso11',
        'permiso12',
        'permiso13',
        'permiso14',
        'permiso15',
        'permiso16',
        'permiso17',
        'permiso18',
        'permiso19',
        'permiso20',
        'permiso21',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function gastos(){
        return $this->hasMany('App\Models\Gastos');
    }

    public function tipo_user(){
        return $this->belongsTo('App\Models\TipoUsers', 'tipo_user_id');
    }

    public function ventas(){
        return $this->hasMany('App\Models\Ventas');
    }
}
