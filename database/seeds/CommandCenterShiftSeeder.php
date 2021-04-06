<?php

use App\Enums\CommandCenterShiftStatusEnum;
use Illuminate\Database\Seeder;
use App\Models\CommandCenterShift;

class CommandCenterShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'SHIFT 1',
                'time' => '09:00 - 12:00',
                'capacity' => config('shift.default_max_visitor'),
                'status' => CommandCenterShiftStatusEnum::ACTIVE()
            ],
            [
                'name' => 'SHIFT 2',
                'time' => '13:00 - 16:00',
                'capacity' => config('shift.default_max_visitor'),
                'status' => CommandCenterShiftStatusEnum::ACTIVE()
            ],
        ];

        foreach ($data as $item) {
            $shift = new CommandCenterShift;
            $shift->fill($item);
            $shift->save();
        }
    }
}
