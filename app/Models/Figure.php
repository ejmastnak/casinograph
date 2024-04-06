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

    public static function getWithPositionsForUser(?int $userId) {
        return self::where('user_id', $userId)
        ->orderBy('name')
        ->with([
            'from_position:id,name',
            'to_position:id,name',
        ])
        ->get([
            'id',
            'name',
            'from_position_id',
            'to_position_id',
        ]);
    }

    public static function getCombinedFiguresAndCompoundFiguresForUser(?int $userId) {
        $figures = Figure::with([
            'figure_family:id,name',
            'from_position:id,name',
            'to_position:id,name'
        ])
        ->where('user_id', $userId)
        ->get()
        ->mapWithKeys(function ($figure, $key) {
            return [
                $key => [
                    'id' => $figure['id'],
                    'name' => $figure['name'],
                    'weight' => $figure['weight'],
                    'figure_family_id' => $figure['figure_family_id'],
                    'figure_family' => $figure['figure_family'],
                    'from_position_id' => $figure['from_position_id'],
                    'from_position' => $figure['from_position'],
                    'to_position_id' => $figure['to_position_id'],
                    'to_position' => $figure['to_position'],
                    'compound' => false,
                ]
            ];
        });

        $compoundFigures = CompoundFigure::with([
            'figure_family:id,name',
            'from_position:id,name',
            'to_position:id,name'
        ])
        ->where('user_id', $userId)
        ->get()
        ->mapWithKeys(function ($figure, $key) {
            return [
                $key => [
                    'id' => $figure['id'],
                    'name' => $figure['name'],
                    'weight' => $figure['weight'],
                    'figure_family_id' => $figure['figure_family_id'],
                    'figure_family' => $figure['figure_family'],
                    'from_position_id' => $figure['from_position_id'],
                    'from_position' => $figure['from_position'],
                    'to_position_id' => $figure['to_position_id'],
                    'to_position' => $figure['to_position'],
                    'compound' => true,
                ]
            ];
        });

        return $figures->concat($compoundFigures)->sortBy('name')->values()->all();
    }

    public function withFamilyAndPositions() {
        $this->load([
            'figure_family:id,name',
            'from_position:id,name',
            'to_position:id,name'
        ]);
        return $this->only([
            'id',
            'name',
            'description',
            'weight',
            'figure_family_id',
            'figure_family',
            'from_position_id',
            'from_position',
            'to_position_id',
            'to_position',
        ]);
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

    public function compound_figure_figures() {
        return $this->hasMany(CompoundFigureFigure::class, 'figure_id', 'id')->orderBy('compound_figure_id')->orderBy('seq_num');
    }

}
