<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function topik(){
        return $this->belongsTo(Topik::class, 'topik_id');
    }

    public function userProgress(){
        return $this->belongsTo(UserProgress::class);
    }

    
}
