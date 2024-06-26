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

    public static function getWithOnlyPositionsForUser(?int $userId) {
        return self::where('user_id', ($userId ?? config('constants.user_ids.casino')))
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

    public function withFamilyAndPositionsAndVideos() {
        $this->load([
            'figure_family:id,name',
            'from_position:id,name',
            'to_position:id,name',
            'figure_videos:id,url,description,figure_id',
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
            'figure_videos',
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

    public function figure_videos() {
        return $this->hasMany(FigureVideo::class, 'figure_id', 'id');
    }

}
