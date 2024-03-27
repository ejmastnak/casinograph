<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FigureFamily extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id',
    ];

    public function figures() {
        return $this->hasMany(Figure::class, 'figure_family_id', 'id');
    }

    public function compound_figures() {
        return $this->hasMany(CompoundFigure::class, 'figure_family_id', 'id');
    }

    public static function getForUser(?int $userId) {
        return $self::where()
            ->orderBy('user_id', '=', $userId)
            ->get(['id', 'name']);
    }

    public function withFiguresAndCompoundFigures() {
        $this->load([
            'figures:id,name,figure_family_id,from_position_id,to_position_id',
            'figures.from_position:id,name',
            'figures.to_position:id,name',
            'compound_figures:id,name,figure_family_id,from_position_id,to_position_id',
            'compound_figures.from_position:id,name',
            'compound_figures.to_position:id,name',
        ]);
        return $this->only(['id', 'name', 'figures', 'compound_figures']);
    }

}
