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
        "position_family_id",
        "user_id",
    ];

    public static function getForUser(?int $userId) {
        return self::where('user_id', ($userId ?? config('constants.user_ids.casino')))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public static function getForUserWithPositionFamilies(?int $userId) {
        return self::with('position_family:id,name')
            ->where('user_id', ($userId ?? config('constants.user_ids.casino')))
            ->orderBy('name')
            ->get(['id', 'name', 'position_family_id']);
    }

    public function withName() {
        return $this->only(['id', 'name']);
    }

    public function withPositionFamily() {
        $this->load([
            'position_family:id,name',
        ]);
        return $this->only([
            'id',
            'name',
            'description',
            'position_family_id',
            'position_family',
        ]);
    }

    public function withPositionFamilyAndFigures() {
        $this->load([
            'position_family:id,name',
            'incoming_figures:id,name,to_position_id,from_position_id',
            'incoming_figures.from_position:id,name',
            'outgoing_figures:id,name,from_position_id,to_position_id',
            'outgoing_figures.to_position:id,name',
        ]);
        return $this->only([
            'id',
            'name',
            'description',
            'position_family_id',
            'position_family',
            'incoming_figures',
            'outgoing_figures',
        ]);
    }

    /**
     *  Returns true if a position has at least one incoming or outgoing figure
     */
    public function hasFigures() {
        return $this->incoming_figures()->count() > 0 || $this->outgoing_figures()->count() > 0;
    }

    public function position_family() {
        return $this->belongsTo(PositionFamily::class, 'position_family_id', 'id');
    }

    public function incoming_figures() {
        return $this->hasMany(Figure::class, 'to_position_id', 'id')->orderBy('name');
    }

    public function outgoing_figures() {
        return $this->hasMany(Figure::class, 'from_position_id', 'id')->orderBy('name');
    }

}
