<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Figure extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "weight",
        "from_position_id",
        "to_position_id",
        "user_id",
    ];

    public function from_position() {
        return $this->belongsTo(Position::class, 'from_position_id', 'id');
    }

    public function to_position() {
        return $this->belongsTo(Position::class, 'to_position_id', 'id');
    }

}
