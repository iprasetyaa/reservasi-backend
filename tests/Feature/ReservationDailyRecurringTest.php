<?php

namespace Tests\Feature;

use App\Events\AfterReservationRecurringCreated;
use App\Models\Asset;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ReservationDailyRecurringTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        // Provide mocking data for testing
        $this->asset = factory(Asset::class)->create();
        $this->employee = factory(User::class)->create([
            'role' => 'employee_reservasi',
        ]);
    }

    public function testStoreDailyRecurringReservation()
    {
        $this->expectsEvents(AfterReservationRecurringCreated::class);
        // 1. Mocking data
        $employee = $this->employee;
        $data = [
            'title' => 'test',
            'description' => 'testing phpunit',
            'asset_ids' => [$this->asset->id],
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'from' => Carbon::now()->format('H:i:s'),
            'to' => Carbon::now()->addMinutes(30)->format('H:i:s'),
            'repeat' => true,
            'repeat_type' => 'DAILY',
            'days' => [1, 2]
        ];

        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->post(route('reservation.recurring', 'daily'), $data);

        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_CREATED);
    }
}
