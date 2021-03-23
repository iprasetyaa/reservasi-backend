<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Enums\CommandCenterReservationStatusEnum;
use Spatie\Enum\Laravel\HasEnums;

class CommandCenterReservation extends Model
{
    use SoftDeletes;
    use HasEnums;

    protected $fillable = [
        'user_id_reservation',
        'name',
        'reservation_code',
        'nik',
        'organization_name',
        'address',
        'phone_number',
        'email',
        'purpose',
        'visitors',
        'reservation_date',
        'command_center_shift_id',
        'approval_status',
        'note',
        'approval_date',
        'user_id_updated',
        'user_id_deleted'
    ];

    protected $dates = [
        'reservation_date',
        'approval_date'
    ];

    protected $enums = [
        'approval_status' => CommandCenterReservationStatusEnum::class
    ];

    protected function commandCenterShift()
    {
        return $this->belongsTo(CommandCenterShift::class);
    }
}
