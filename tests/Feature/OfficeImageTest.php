<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficeImageTest extends TestCase
{
    use RefreshDatabase;
    
    
    // /**
    //  * @test
    //  */
    // public function itStoresAnImage(): void
    // {

    //     // $this->withoutExceptionHandling();
    //     ob_start();
    //     Storage::fake('public');
        
    //     $user = User::factory()->create();
    //     $office = Office::factory()->for($user)->create();


    //     $this->actingAs($user);
        
        
        
    //     $response = $this->post('/api/offices/'.$office->id.'/images', [
    //         'image' => UploadedFile::fake()->image('image.jpg')
    //     ]);
    //     Storage::disk('public')->assertExists('photo1.jpg');

    //     dd($response->json());
    //     ob_get_contents();
    //     ob_end_clean();
        
        
        
    // }

    
    /**
     * @test
     */

    public function itDeletesAnImage()

    
    {
        // $this->withoutExceptionHandling();


        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path' => 'salma.png'
        ]);

        $office->images()->create([
            'path' => 'sama.png'
        ]);

        $response = $this->delete('/api/offices/'.$office->id.'/images/'.$image->id);

        $this->assertModelMissing($image);
        $response->assertOk();
    }

    /**
     * @test
     */

    public function itCannotDeleteOnlyImage()

    
    {
        // $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path' => 'salma.png'
        ]);


        $response = $this->delete('/api/offices/'.$office->id.'/images/'.$image->id);

        $response->assertInvalid();

        
    }

    /**
     * @test
     */

    public function itCannotDeleteFeaturedImage()

    
    {
        // $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path' => 'salma.png'
        ]);

        $office->update(['featured_image_id' => $image->id]);

        $office->images()->create([
            'path' => 'sa.png'
        ]);



        $response = $this->delete('/api/offices/'.$office->id.'/images/'.$image->id);

        $response->assertInvalid();


    }

    /**
     * @test
     */

    public function itCannotDeleteOthersImage():void
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $office2 = Office::factory()->for($user)->create();

        $image = $office2->images()->create([
            'path' => 'salma.png'
        ]);

        $office2->images()->create([
            'path' => 'salm.png'
        ]);

        $response = $this->delete('/api/offices/'.$office->id.'/images/'.$image->id);

        
        $response->assertInvalid();

    } 


}
