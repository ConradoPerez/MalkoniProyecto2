<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subrubro extends Model
{
    use HasFactory;
    
    protected $table = 'subrubros';
    protected $primaryKey = 'id_subrubro';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'id_rubro'
    ];
    
    public function rubro()
    {
        return $this->belongsTo(Rubro::class, 'id_rubro');
    }
    
    public function subdivisions()
    {
        return $this->hasMany(Subdivision::class, 'id_subrubro');
    }
}