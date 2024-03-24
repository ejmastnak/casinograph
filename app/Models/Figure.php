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
        "figure_family_id",
        "from_position_id",
        "to_position_id",
        "user_id",
    ];

    public function figure_family() {
        return $this->belongsTo(FigureFamily::class, 'figure_family_id', 'id');
    }

    public function from_position() {
        return $this->belongsTo(Position::class, 'from_position_id', 'id');
    }

    public function to_position() {
        return $this->belongsTo(Position::class, 'to_position_id', 'id');
    }

    public function compound_figure_figures() {
        return $this->hasMany(CompoundFigureFigure::class, 'figure_id', 'id')->orderBy('compound_figure_id')->orderBy('seq_num');
    }

}
