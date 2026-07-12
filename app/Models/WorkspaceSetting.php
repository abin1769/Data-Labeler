<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'active_activity',
        'access_passkey',
    ];

    public const ACTIVE_LABELING = 'labeling';
    public const ACTIVE_AUDIT = 'audit';

    public const ACTIVE_ACTIVITIES = [
        self::ACTIVE_LABELING,
        self::ACTIVE_AUDIT,
    ];
}