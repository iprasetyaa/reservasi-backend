<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandCenterCloseDate extends Model
{
    protected $fillable = [
        'date',
        'note',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d'
    ];
}
