<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandCenterCloseDate extends Model
{
    protected $fillable = [
        'date',
        'note',
    ];

    protected $dates = [
        'date',
    ];
}
