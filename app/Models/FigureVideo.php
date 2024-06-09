<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FigureVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        "url",
        "description",
        "figure_id",
    ];

    public function figure() {
        return $this->belongsTo(Figure::class, 'figure_id', 'id');
    }

}
