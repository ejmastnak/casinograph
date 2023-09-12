<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FigureFamily extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    public function figures() {
        return $this->hasMany(Figure::class, 'figure_family_id', 'id');
    }

    public function compound_figures() {
        return $this->hasMany(CompoundFigure::class, 'figure_family_id', 'id');
    }

}
