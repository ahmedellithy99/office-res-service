<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeImageController extends Controller
{
    public function store(Office $office)
    {
        request()->validate([
            'image' =>['file' , 'max:5000' , 'mimes:png,jpg']
        ]);

        $path = request()->file('image')->storePublicly('/ss' );

        $image =$office->images()->create(
            ['path' => $path]
        );

        return ImageResource::make($image);

    }

    public function delete(Office $office , Image $image)

    
    {
        throw_if($image->resource_type != 'office' || $image->resource_id != $office->id  , ValidationException::withMessages(['images' =>'Cannot delete others image']) );

        throw_if($office->images()->count() == 1 , ValidationException::withMessages(['images' =>'Cannot delete only image']) );

        throw_if($office->featured_image_id == $image->id , ValidationException::withMessages(['images' =>'Cannot delete featured image']) );

        Storage::disk('public')->delete($image->path);

        $image->delete();
    }
}
