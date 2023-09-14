<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionFamily extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id',
    ];

    public function positions() {
        return $this->hasMany(Position::class, 'position_family_id', 'id');
    }

}
