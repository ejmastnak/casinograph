<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompoundFigureFigure extends Model
{
    use HasFactory;

    protected $fillable = [
        "figure_id",
        "compound_figure_id",
        "idx",
        "user_id",
    ];
}
