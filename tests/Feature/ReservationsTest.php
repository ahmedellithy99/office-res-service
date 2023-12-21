<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReservationsTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */

    public function itFetchesAllUsersReservations()
    {

        // $this->withoutExceptionHandling();
        $user = User::factory()->create();

        Reservation::factory(5)->for($user)->create();
        Reservation::factory(2)->create();
        
        
        $this->actingAs($user);

        $response = $this->getJson('api/reservations');

        // dd($response->json());

        $response->assertJsonCount(5 , 'data');


    }
    
    /**
     * @test
     */

    public function itFetchesAllUsersReservationsAtCertainTime()
    {

        // $this->withoutExceptionHandling();
        $user = User::factory()->create();

        $fromDate = '2023-03-03';
        $toDate = '2023-04-04';
        
        $reservations = Reservation::factory()->for($user)->createMany([
            [
            'start_date' => '2023-03-01',
            'end_date' => '2023-03-15'
            ],
            
            [
            'start_date' => '2023-03-25',
            'end_date' => '2023-04-25'
            ],
            
            [
            'start_date' => '2023-03-25',
            'end_date' => '2023-03-29'
            ]
            ,
            [
            'start_date' => '2023-03-01',
            'end_date' => '2023-04-29'
            ]

        ]);
        

        Reservation::factory()->for($user)->create([
            'start_date' => '2023-02-15',
            'end_date' => '2023-03-01'
        ]);

        Reservation::factory()->for($user)->create([
            'start_date' => '2023-06-15',
            'end_date' => '2023-07-01'
        ]);

        
        
        
        $this->actingAs($user);

        // DB::enableQueryLog();

        $response = $this->getJson('api/reservations?'.http_build_query([
            'from_date' => $fromDate,
            'to_date' => $toDate
                ]));



        // dd(
        //     DB::getQueryLog()
        // );


        $response->assertJsonCount(4 , 'data');

        $this->assertEquals($reservations->pluck('id')->toArray() , collect($response->json('data'))->pluck('id')->toArray()) ;

    }

    /**
     * @test
     */

    public function itFetchesAllUsersReservationsForACertainOffice()
    {

        // $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $office = Office::factory()->create();

        [$reservation] = Reservation::factory(2)->for($user)->for($office)->create();
        Reservation::factory(2)->create();

        $image = $reservation->office->images()->create(
            ['path' => 'image.jpg']
        );

        $reservation->office()->update(['featured_image_id' => $image->id]);
        
        
        $this->actingAs($user);

        $response = $this->getJson("api/reservations?office_id={$office->id}");

        // dd($response->json());

        $response->assertJsonCount(2 , 'data')
                ->assertJsonPath('data.1.office.featured_image_id' , $image->id);


    }

    /**
     * @test
     */

    public function itMakesReservations()
    {
        $user = User::factory()->create();
        $office = Office::factory()->create([
            'price_per_day' => 100 ,
            'monthly_discount' => 5
        ]);

        $this->actingAs($user);


        $response = $this->postJson("api/reservations" , [
            'office_id' => $office->id ,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(42)->toDateString(),
        ]);

        

        // dd($response->json());

        $response->assertCreated()
                ->assertJsonPath('data.price' , 3800)
                ->assertJsonPath('data.user_id' , $user->id)
                ->assertJsonPath('data.office_id' , $office->id);
                

    }

    /**
     * @test
     */
    public function itCannotMakeReservationOnNonExistingOffice()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('api/reservations', [
            'office_id' => 10000,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(41),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['office_id' => 'Invalid office_id']);
    }

    /**
     * @test
     */
    public function itCannotMakeReservationOnSameDay()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('api/reservations', [
            'office_id' => $office->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['start_date' => 'The start date field must be a date after today.']);
    }

        /**
     * @test
     */
    public function itCannotMakeReservationThatsConflicting()
    {
        $user = User::factory()->create();

        $fromDate = now()->addDays(2)->toDateString();
        $toDate = now()->addDay(15)->toDateString();

        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create([
            'start_date' => now()->addDay(2),
            'end_date' => $toDate,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('api/reservations', [
            'office_id' => $office->id,
            'start_date' => $fromDate,
            'end_date' => $toDate,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['office_id' => 'You cannot make a reservation during this time']);
    }




}
