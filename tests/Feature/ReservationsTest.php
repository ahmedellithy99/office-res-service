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

    public function itFetches()
    {

        // $this->withoutExceptionHandling();
        $user = User::factory()->create();

        Reservation::factory(5)->for($user)->create();
        Reservation::factory(2)->create();
        
        
        $this->actingAs($user);

        $response = $this->getJson('api/reservations/'.$user->id);

        $response->assertJsonCount(5,'data');


    }


}
