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

    public function compound_figure() {
        return $this->belongsTo(CompoundFigure::class, 'compound_figure_id', 'id');
    }

    public function figure() {
        return $this->belongsTo(Figure::class, 'figure_id', 'id');
    }

}
