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

    public static function getForUser(?int $userId) {
        return self::where('user_id', $userId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function withPositions() {
        $this->load(['positions:id,name,position_family_id']);
        return $this->only(['id', 'name', 'positions']);
    }

}
