<?php

namespace App\Models;

use App\Enums\ReservationRecurringTypeEnum;
use App\Enums\ReservationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Enum\Laravel\HasEnums;

class Reservation extends Model
{
    use SoftDeletes;
    use HasEnums;

    protected $fillable = [
        'recurring_id',
        'repeat_type',
        'user_id_reservation',
        'user_fullname',
        'username',
        'title',
        'email',
        'description',
        'asset_id',
        'asset_name',
        'asset_description',
        'asset_resource_type',
        'approval_status',
        'note',
        'date',
        'start_time',
        'end_time',
        'join_url',
        'user_id_updated',
        'approval_date'
    ];

    protected $dates = [
        'start_time',
        'end_time',
        'approval_date'
    ];

    protected $enums = [
        'approval_status' => ReservationStatusEnum::class . ':nullable',
        'repeat_type' => ReservationRecurringTypeEnum::class . ':nullable',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function scopeByUser($query, $user)
    {
        return $query->where('user_id_reservation', $user->uuid);
    }

    public function scopeNotYetApproved($query)
    {
        return $query->where('approval_status', ReservationStatusEnum::not_yet_approved());
    }

    public function scopeAlreadyApproved($query)
    {
        return $query->where('approval_status', ReservationStatusEnum::already_approved());
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', ReservationStatusEnum::rejected());
    }

    public function scopeValidateTime($query, $reservation)
    {
        $query->where('date', $reservation->date)
                ->where(function ($query) use ($reservation) {
                    $query->where(function ($query) use ($reservation) {
                        $query->whereTime('start_time', '<=', $reservation->start_time)
                            ->whereTime('end_time', '>', $reservation->start_time);
                    })
                    ->orWhere(function ($query) use ($reservation) {
                        $query->whereTime('start_time', '<', $reservation->end_time)
                            ->whereTime('end_time', '>', $reservation->end_time);
                    })
                    ->orWhere(function ($query) use ($reservation) {
                        $query->whereTime('start_time', '>=', $reservation->start_time)
                            ->whereTime('end_time', '<=', $reservation->end_time);
                    });
                });
        return $query;
    }

    public function getIsNotYetApprovedAttribute()
    {
        return $this->approval_status != ReservationStatusEnum::not_yet_approved();
    }

    public function getHasAlreadyApprovedAttribute()
    {
        return $this->approval_status == ReservationStatusEnum::already_approved();
    }

    public function getHasRejectedAttribute()
    {
        return $this->approval_status == ReservationStatusEnum::rejected();
    }

    public function getCheckTimeEditValidAttribute()
    {
        return Carbon::now() > $this->start_time->subMinutes(30);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
