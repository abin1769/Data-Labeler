<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExampleImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'label',
    ];

    /**
     * Get the public URL for the example image asset.
     */
    public function getUrlAttribute()
    {
        return asset('dataset/examples/' . $this->label . '/' . $this->filename);
    }
}
