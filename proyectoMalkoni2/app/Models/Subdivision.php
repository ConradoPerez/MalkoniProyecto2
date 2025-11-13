<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subdivision extends Model
{
    use HasFactory;
    
    protected $table = 'subdivisions';
    protected $primaryKey = 'id_subdivision';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'id_subrubro'
    ];
    
    public function subrubro()
    {
        return $this->belongsTo(Subrubro::class, 'id_subrubro');
    }
    
    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'id_subdivision');
    }
}