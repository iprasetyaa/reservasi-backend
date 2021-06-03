<?php

namespace Tests\Feature;

use App\Events\AfterReservationCreated;
use App\Mail\ReservationStoreMail;
use App\Models\Asset;
use App\Models\Reservation;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Tests\TestCase;

class ReservationTest extends TestCase
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

    /**
     * [testIndexPage render asset page]
     * @return void
     */
    public function testIndexReservation()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.index'));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testIndexReservationSearchTitle()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.index', ['search' => 'jabar']));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testIndexReservationPerPage()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.index', ['perPage' => 50]));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testIndexReservationFilterByAsset()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.index', ['asset_id' => 1]));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testIndexReservationFilterByApprovalStatus()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.index', ['approval_status' => 'already_approved']));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testIndexReservationFilterByStartDate()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.index', ['start_date' => '2021-01-27']));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testIndexReservationFilterByEndDate()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.index', ['end_date' => '2021-01-28']));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testIndexReservationSortedBy()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.index', [
            'sortBy' => 'reservation_time',
            'date' => '2021-01-25',
            'start_time' => '07:00',
            'end_time' => '09:00',
        ]));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testShowReservation()
    {
        Mail::fake();
        // 1. Mocking data
        $employee = $this->employee;
        $reservation = factory(Reservation::class)->create([
            'user_id_reservation' => $employee->uuid,
            'user_fullname' => $employee->name,
            'username' => $employee->username,
            'asset_id' => $this->asset->id,
            'asset_name' => $this->asset->name,
            'approval_status' => 'already_approved',
        ]);

        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.show', $reservation->id));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testStoreReservation()
    {
        $this->expectsEvents(AfterReservationCreated::class);

        Mail::fake();
        // 1. Mocking data
        $employee = $this->employee;
        $data = [
            'title' => 'test',
            'description' => 'testing phpunit',
            'asset_ids' => [$this->asset->id],
            'start_date' => Carbon::now()->format('Y-m-d'),
            'from' => Carbon::now()->format('H:i:s'),
            'to' => Carbon::now()->addMinutes(30)->format('H:i:s')
        ];
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->post(route('reservation.store'), $data);
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_CREATED);
        // 4. Database test (check data already inserted or not)
        $this->assertDatabaseHas('reservations', [
            'title' => 'test',
            'description' => 'testing phpunit',
            'asset_id' => $this->asset->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s')
        ]);
    }

    public function testStoreReservationNullData()
    {
        Mail::fake();
        // 1. Mocking data
        $employee = $this->employee;
        $data = [];
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->post(route('reservation.store'), $data);
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'title',
                'asset_ids',
                'date',
                'start_time',
                'end_time'
            ]
        ]);
    }

    public function testSendEmailReservation()
    {
        Mail::fake();
        $employee = $this->employee;

        $reservation = factory(Reservation::class)->create([
            'user_id_reservation' => $employee->uuid,
            'user_fullname' => $employee->name,
            'username' => $employee->username,
            'asset_id' => $this->asset->id,
            'asset_name' => $this->asset->name,
            'approval_status' => 'already_approved',
        ]);

        Mail::to($employee)->send(new ReservationStoreMail($reservation, 'message'));

        Mail::assertSent(ReservationStoreMail::class);
    }

    public function testDestroyReservation()
    {
        Mail::fake();
        // 1. Mocking data
        $employee = $this->employee;
        $asset = factory(Asset::class)->create();
        $reservation = factory(Reservation::class)->create([
            'user_id_reservation' => $employee->uuid,
            'user_fullname' => $employee->name,
            'username' => $employee->username,
            'asset_id' => $asset->id,
            'asset_name' => $asset->name,
        ]);

        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->delete(route('reservation.destroy', $reservation->id));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testListAllReservation()
    {
        // 1. Mocking data
        $employee = $this->employee;
        // 2. Hit Api Endpoint
        $response = $this->actingAs($employee)->get(route('reservation.list'));
        // 3. Verify and Assertion
        $response->assertStatus(Response::HTTP_OK);
    }
}
