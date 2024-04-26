<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompoundFigure extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "weight",
        "from_position_id",
        "to_position_id",
        "figure_family_id",
        "user_id",
    ];

    public static function getForUser(?int $userId) {
        return self::where('user_id', ($userId ?? config('constants.user_ids.casino')))
            ->orderBy('name')
            ->with([
                'figure_family:id,name',
                'from_position:id,name',
                'to_position:id,name',
            ])
            ->get([
                'id',
                'name',
                'weight',
                'figure_family_id',
                'from_position_id',
                'to_position_id',
            ]);
    }

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

    public function compound_figure_figures() {
        return $this->hasMany(CompoundFigureFigure::class, 'compound_figure_id', 'id')->orderBy('seq_num');
    }

    public function figure_family() {
        return $this->belongsTo(FigureFamily::class, 'figure_family_id', 'id');
    }

    public function from_position() {
        return $this->belongsTo(Position::class, 'from_position_id', 'id');
    }

    public function to_position() {
        return $this->belongsTo(Position::class, 'to_position_id', 'id');
    }

}
