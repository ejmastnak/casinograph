<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "position_family_id",
        "user_id",
    ];

    protected static function booted(): void
    {
        // Delete PositionImage files from disk before position is deleted
        static::deleting(function (Position $position) {
            foreach ($position->position_images as $positionImage) {
                Storage::disk('local')->delete($positionImage->path);
            }
        });
    }

    public static function getForUser(?int $userId) {
        return self::where('user_id', ($userId ?? config('constants.user_ids.casino')))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public static function getForUserWithPositionFamilies(?int $userId) {
        return self::with('position_family:id,name')
            ->where('user_id', ($userId ?? config('constants.user_ids.casino')))
            ->orderBy('name')
            ->get(['id', 'name', 'position_family_id']);
    }

    public function withName() {
        return $this->only(['id', 'name']);
    }

    public function withPositionFamily() {
        $this->load([
            'position_family:id,name',
        ]);
        return $this->only([
            'id',
            'name',
            'description',
            'position_family_id',
            'position_family',
        ]);
    }

    public function withPositionFamilyAndImages() {
        $this->load([
            'position_family:id,name',
            'position_images:id,path,description,position_id'
        ]);
        return $this->only([
            'id',
            'name',
            'description',
            'position_family_id',
            'position_family',
            'position_images',
        ]);
    }

    public function withFamilyImagesAndFigures() {
        $this->load([
            'position_family:id,name',
            'incoming_figures:id,name,to_position_id,from_position_id',
            'incoming_figures.from_position:id,name',
            'outgoing_figures:id,name,from_position_id,to_position_id',
            'outgoing_figures.to_position:id,name',
            'position_images:id,path,description,position_id'
        ]);
        return $this->only([
            'id',
            'name',
            'description',
            'position_family_id',
            'position_family',
            'incoming_figures',
            'outgoing_figures',
            'position_images',
        ]);
    }

    /**
     *  Returns true if a position has at least one incoming or outgoing figure
     */
    public function hasFigures() {
        return $this->incoming_figures()->count() > 0 || $this->outgoing_figures()->count() > 0;
    }

    public function position_family() {
        return $this->belongsTo(PositionFamily::class, 'position_family_id', 'id');
    }

    public function incoming_figures() {
        return $this->hasMany(Figure::class, 'to_position_id', 'id')->orderBy('name');
    }

    public function outgoing_figures() {
        return $this->hasMany(Figure::class, 'from_position_id', 'id')->orderBy('name');
    }

    public function position_images() {
        return $this->hasMany(PositionImage::class, 'position_id', 'id');
    }

}
