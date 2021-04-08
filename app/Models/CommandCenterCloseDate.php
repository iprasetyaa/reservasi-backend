<?php

namespace App\Models;

use Carbon\Carbon;
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

    /**
     * Scope a query to only include dates within a month from now.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterAMonth($query)
    {
        $from = Carbon::now()->toDateString();
        $to = Carbon::now()->addMonth();

        return $query->whereBetween('date', [$from, $to]);
    }
}
