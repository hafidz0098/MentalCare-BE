<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsultasi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function reply(){
        return $this->hasMany(KonsultasiMessage::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
