<?php

namespace App\Models;

use App\Enums\CommandCenterShiftStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommandCenterShift extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'status', 'capacity'];

    protected $enums = [
        'status' => CommandCenterShiftStatusEnum::class,
    ];

    protected function commandCenterReservations()
    {
        return $this->hasMany(CommandCenterReservation::class);
    }
}
