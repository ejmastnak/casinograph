<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompoundFigureFigure extends Model
{
    use HasFactory;

    protected $fillable = [
        "seq_num",
        "compound_figure_id",
        "figure_id",
        "is_final",
        "user_id",
    ];

    public function withFamilyAndFigures() {
        $this->load([
            'figure_family:id,name',
            'compound_figure_figures:id,seq_num,compound_figure_id,figure_id',
            'compound_figure_figures.figure:id,name,from_position_id,to_position_id',
            'compound_figure_figures.figure.from_position:id,name',
            'compound_figure_figures.figure.to_position:id,name',
        ]);
        return $this->only([
            'id',
            'name',
            'description',
            'weight',
            'figure_family_id',
            'figure_family',
            'compound_figure_figures',
        ]);
    }

    public function compound_figure() {
        return $this->belongsTo(CompoundFigure::class, 'compound_figure_id', 'id');
    }

    public function figure() {
        return $this->belongsTo(Figure::class, 'figure_id', 'id');
    }

}
