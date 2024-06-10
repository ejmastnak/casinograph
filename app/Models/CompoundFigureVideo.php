<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompoundFigureVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        "url",
        "description",
        "compound_figure_id",
    ];

    public function compound_figure() {
        return $this->belongsTo(CompoundFigure::class, 'compound_figure_id', 'id');
    }

}
