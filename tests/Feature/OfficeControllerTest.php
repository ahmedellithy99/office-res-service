<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use Database\Factories\OfficeFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{

    use RefreshDatabase;
    /**
     * @test
     */
    public function itListAllOffices(): void
    {

        // $this->withoutExceptionHandling();
        
        Office::factory(2)->create();
        
        $response = $this->get('/api/offices');
        
        $this->assertNotNull($response->json('data')[0]['id']);
        $this->assertCount(2, $response->json('data'));
        $response->assertOk();

    }

    /**
     * @test
     */
    public function itPaginates(): void
    {

        // $this->withoutExceptionHandling();
        
        Office::factory(2)->create();
        
        $response = $this->get('/api/offices');

        $response->dump();
        
        $this->assertNotNull($response->json('data')[0]['id']);
        $this->assertNotNull($response->json('meta'));
        $this->assertNotNull($response->json('links'));

        $this->assertCount(2, $response->json('data'));
        
        

    }

    /**
     * @test
     */
    public function itListsOnlyVisibleAndApprovedOnes(): void
    {

        // $this->withoutExceptionHandling();
        
        Office::factory(3)->create();
        Office::factory()->create(['hidden' => true]);
        Office::factory()->create(['approval_status' => Office::APPROVAL_PENDING]);

        
        $response = $this->get('/api/offices');
        
        $this->assertCount(3, $response->json('data'));
        
        

    }

     /**
     * @test
     */

    public function itListsOnlyUsers(): void
    {

        // $this->withoutExceptionHandling();
        
        Office::factory(2)->create();
        
        $user = User::factory()->create();

        Office::factory(4)->for($user)->create();
        Office::factory()->create(['user_id' => $user->id]);

        
        $response = $this->get('/api/offices?user_id='.$user->id);
        
        $this->assertCount(5, $response->json('data'));

    }

    
    /**
     * @test
     */

    public function itListsOnlyGuestUser(): void
    {
 
         // $this->withoutExceptionHandling();
        $user = User::factory()->create();
        
        $office = Office::factory()->create();

        Reservation::factory(2)->for($user)->create();
        Reservation::factory(3)->create();

        
        //  Office::factory()->create(['user_id' => $user->id]);
        
        $response = $this->get('/api/offices?visitor_id='.$user->id);
        

        $this->assertCount(2, $response->json('data'));
}

    /**
     * @test
     */

    public function itIncludesUsersImagesTags():void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $office = Office::factory()->for($user)->create();
        // $image = Image::factory()->create();
        
        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.pps']);

        
        
        $response = $this->get('/api/offices');


        
        $this->assertIsArray($response->json('data')[0]['tags']);
        $this->assertIsArray($response->json('data')[0]['images']);
        $this->assertEquals($user->id ,$response->json('data')[0]['user']['id']);
}
    /**
     * @test
     */

    public function itReturnCountOfActiveReservations()
    {
        
        $office = Office::factory()->create();
        
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);

        Reservation::factory()->create(['status' => Reservation::STATUS_ACTIVE]);

        $response = $this->get('/api/offices');

        $response->assertOk();

        $this->assertEquals(1, $response->json('data')[0]['reservations_count'] );

    }

    /**
     * @test
     */

    public function itShowsTheOffice():void
    {

        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $office = Office::factory()->for($user)->create();
        
        
        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.pps']);
        
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);

        Reservation::factory()->create(['status' => Reservation::STATUS_ACTIVE]);
        
        
        $response = $this->get('/api/offices/'.$office->id);

        $response->dump();

        $response->assertOk();

        $this->assertEquals(2, $response->json('data')['reservations_count'] );
 
    } 





}
