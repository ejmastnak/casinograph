<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionImage extends Model
{
    use HasFactory;

    protected $fillable = [
        "path",
        "description",
        "position_id",
    ];

    public function position() {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
}
