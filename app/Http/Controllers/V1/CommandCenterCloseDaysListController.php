<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CCCloseDateListResource;
use App\Models\CommandCenterCloseDate;
use Carbon\Carbon;

class CommandCenterCloseDaysListController extends Controller
{
    /**
     * List disabled days, dates only.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $records = CommandCenterCloseDate::query();
        $records = $records->orderBy('date');

        $records = $this->filterAMonth($records);

        return CCCloseDateListResource::collection($records->get());
    }

    /**
     * filter a month ahead
     *
     * @param  [Collection] $records
     * @return Collection
     */
    protected function filterAMonth($records)
    {
        $from = Carbon::now()->toDateString();
        $to = Carbon::now()->addMonth();

        $records->whereBetween('date', [$from, $to]);

        return $records;
    }

}
