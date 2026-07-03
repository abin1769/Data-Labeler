<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'label',
        'labeled_by',
        'prodi',
        'label_status',
    ];

    /**
     * Get the fast direct public URL for the image asset.
     */
    public function getUrlAttribute()
    {
        $filename = basename($this->filename);
        if (file_exists(public_path('dataset/test/' . $filename))) {
            return asset('dataset/test/' . $filename);
        }
        if (file_exists(public_path('dataset/' . $filename))) {
            return asset('dataset/' . $filename);
        }
        return route('images.show', ['filename' => $filename]);
    }
}
