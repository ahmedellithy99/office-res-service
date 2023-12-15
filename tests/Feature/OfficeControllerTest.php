<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Database\Factories\OfficeFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{

    // use RefreshDatabase;
    /**
     * @test
     */
    public function itListAllOffices(): void
    {

        // $this->withoutExceptionHandling();
        
        Office::factory(2)->create();
        
        $response = $this->get('/api/offices');

        $response->dump();
        
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

    public function itListsOnlyHostedUsers(): void
    {

        // $this->withoutExceptionHandling();
        
        Office::factory(2)->create();
        
        $user = User::factory()->create();

        Office::factory(4)->for($user)->create();
        Office::factory()->create(['user_id' => $user->id]);

        
        $response = $this->get('/api/offices?host_id='.$user->id);
        

        
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
        
        $response = $this->get('/api/offices?user_id='.$user->id);
        

        $this->assertCount(2, $response->json('data'));
}



}
