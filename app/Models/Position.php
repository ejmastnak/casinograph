<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "user_id",
    ];

    public function incoming_figures() {
        return $this->hasMany(Figure::class, 'to_position_id', 'id');
    }

    public function outgoing_figures() {
        return $this->hasMany(Figure::class, 'from_position_id', 'id');
    }

}
