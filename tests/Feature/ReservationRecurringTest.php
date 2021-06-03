<?php

namespace Tests\Feature;

use App\Events\AfterReservationRecurringCreated;
use App\Models\Asset;
use App\Models\Reservation;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ReservationRecurringTest extends TestCase
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
            'title' => 'testing daily',
            'description' => 'testing daily',
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

    public function testStoreWeeklyRecurringReservation()
    {
        $this->expectsEvents(AfterReservationRecurringCreated::class);
        // 1. Mocking data
        $employee = $this->employee;
        $data = [
            'title' => 'testing weekly',
            'description' => 'testing weekly',
            'asset_ids' => [$this->asset->id],
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addMonth()->format('Y-m-d'),
            'from' => Carbon::now()->addMinutes(60)->format('H:i:s'),
            'to' => Carbon::now()->addMinutes(90)->format('H:i:s'),
            'repeat' => true,
            'repeat_type' => 'WEEKLY',
            'days' => [1, 2],
            'week' => 2
        ];

        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->post(route('reservation.recurring', 'weekly'), $data);

        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function testStoreMonthlyRecurringReservation()
    {
        $this->expectsEvents(AfterReservationRecurringCreated::class);
        // 1. Mocking data
        $employee = $this->employee;
        $data = [
            'title' => 'testing monthly',
            'description' => 'testing monthly',
            'asset_ids' => [$this->asset->id],
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
            'from' => Carbon::now()->addMinutes(120)->format('H:i:s'),
            'to' => Carbon::now()->addMinutes(150)->format('H:i:s'),
            'repeat' => true,
            'repeat_type' => 'MONTHLY',
            'days' => [2],
            'week' => 2,
            'month' => 2
        ];

        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->post(route('reservation.recurring', 'monthly'), $data);

        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function testDestroyRecurringReservation()
    {
        // 1. Mocking data
        $employee = $this->employee;
        $reservation = factory(Reservation::class)->create([
            'recurring_id' => 101,
            'user_id_reservation' => $employee->uuid,
            'user_fullname' => $employee->name,
            'username' => $employee->username,
            'asset_id' => $this->asset->id,
            'asset_name' => $this->asset->name,
        ]);

        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->delete(route('delete.recurring', $reservation->recurring_id));

        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }
}
