<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\OfficeApprovalPending;
use Database\Factories\OfficeFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
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
    public function itListsAllHiddenAndUnApprovedOnesIfTheUserIsAuthenticated(): void
    {

        // $this->withoutExceptionHandling();
        $user = User::factory()->create();

        Office::factory(3)->for($user)->create();
        Office::factory()->for($user)->create(['hidden' => true]);
        Office::factory()->for($user)->create(['approval_status' => Office::APPROVAL_PENDING]);
        
        $this->actingAs($user);
        
        $response = $this->get('/api/offices?user_id='.$user->id);
        
        $this->assertCount(5, $response->json('data'));
        
        

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

        $response->assertOk();

        $this->assertEquals(2, $response->json('data')['reservations_count'] );

    }
    
    /**
     * @test
     */
    public function itCreateAnOffice()

    {
        $this->withoutExceptionHandling();

        Notification::fake();
        
        $admin = User::factory()->create(['is_admin' => true]);



        $tag = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $user = User::factory()->createQuietly();

        $this->actingAs($user);

        $response = $this->postJson('/api/offices' , 
            [
                'title' => 'Office in AbuHommos',
                'description' => 'Description',
                'lat' => '58.199681920950',
                'lng' => '38.445535899732',
                'address_line1' => 'Abo Hommos',
                'price_per_day' =>10_000,
                'monthly_discount' => 5,
                'tags' =>[
                    $tag->id  , $tag2->id
                ]
            ]);

        $response->assertCreated()
                ->assertJsonPath('data.title' , 'Office in AbuHommos')
                ->assertJsonPath('data.approval_status' , Office::APPROVAL_PENDING)
                ->assertJsonPath('data.user.id' , $user->id);

        $this->assertDatabaseHas('offices' , ['title' =>'Office in AbuHommos' ]);

        Notification::assertSentTo(
            [$admin], OfficeApprovalPending::class
        );


    }

    /**
     * @test
     */

    public function itAllowsOnlyTokendUsers():void
    {
        // $this->withoutExceptionHandling();


        $user = User::factory()->createQuietly();

        $token = $user->createToken('test',['aa']);

        $response = $this->postJson('/api/offices' , 
            [],
            [
                'Authorization' => 'Bearer '.$token->plainTextToken 
            ]);

        $response->assertForbidden();

    }

    /**
     * @test
     */

    public function itUpdateTheOffice():void
    {
        

        $user = User::factory()->create();
        $tags = Tag::factory(2)->create();
        $anotherTag = Tag::factory()->create();

        $office = Office::factory()->for($user)->create();
        
        $office->tags()->attach($tags);
        
        
        
        $token = $user->createToken('test',['office.update']);



        $response = $this->putJson('/api/offices/'.$office->id , 
            ['title' => 'Office in Alex',
            'tags' => [$tags[0]->id , $anotherTag->id]],
            [
                'Authorization' => 'Bearer '.$token->plainTextToken 
            ]);

        
        $response->assertOk();
        
        $response->assertJsonCount(2 ,'data.tags' );


        $response->assertJsonPath('data.title' , 'Office in Alex');

    }

    /**
     * @test
     */

    public function itUpdatesFeaturedImage():void
    {
        

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        
        $image = $office->images()->create([
            'path' => 'salma.png'
        ]);
    
    
        $this->actingAs($user);
        
        $response = $this->putJson('/api/offices/'.$office->id , 
            [
                'featured_image_id' => $image->id
            ]);
        
        $response->assertOk();

        // dd($response->json());


        $response->assertJsonPath('data.featured_image_id' , $image->id);

        }

        /**
     * @test
     */

    public function itUpdatesAnotherOfficeFeaturedImage():void
    {
        

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $office2 = Office::factory()->for($user)->create();
        
        
        $image = $office2->images()->create([
            'path' => 'salma.png'
        ]);
    
    
        $this->actingAs($user);
        
        $response = $this->putJson('/api/offices/'.$office->id , 
            [
                'featured_image_id' => $image->id
            ]);
        
        $response->assertUnprocessable()->assertInvalid('featured_image_id');


        }


    /**
     * @test
     */

    public function itDoesnotUpdateUnAuthoziedUser():void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $office = Office::factory()->for($anotherUser)->create();


        $this->actingAs($user);

        $response = $this->putJson('api/offices/'.$office->id , 
        ['title' => 'a4a']
        );

        $response->assertForbidden();


    }

    /**
     * @test
     */

    public function itChangingApprovalStatusToPendingIfItIsDirty():void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $office = Office::factory()->for($user)->create();

        Notification::fake();
        
        $this->actingAs($user);
        
        $response = $this->putJson('api/offices/'.$office->id , 
        ['price_per_day' => '1234']
        );

        $response->assertJsonPath('data.approval_status' , Office::APPROVAL_PENDING);

        $this->assertDatabaseHas('offices' , 
        [
            'id' => $office->id,
            'approval_status' => Office::APPROVAL_PENDING
        ]);

        Notification::assertSentTo(
            [$user], OfficeApprovalPending::class
        );

        $response->assertOk();


    }

    /**
     * @test
     */

    public function itCanDeletTheOffice():void
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->delete('api/offices/'.$office->id);

        $response->assertOk();
    }


    /**
     * @test
     */

    public function itCannotDeletTheOffice():void
    {
        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        Reservation::factory()->for($office)->create();

        $this->actingAs($user);

        $response = $this->delete('api/offices/'.$office->id);

        $response->assertStatus(302);
    }
    


}
