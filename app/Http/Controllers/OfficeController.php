<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {

        
        $offices = Office::query()
                
                ->where('approval_status' , Office::APPROVAL_APPROVED)
                ->where('hidden' , false)
                ->when(request('user_id') , fn($builder) => $builder->where('user_id' , request('user_id')))
                ->when(request('visitor_id') , fn(Builder $builder) => $builder->whereRelation('reservations','user_id' , request('visitor_id')))
                ->orderBy('id' , 'DESC')
                ->with(['user' , 'tags' , 'images'])
                ->withCount(['reservations' => fn($builder) => $builder->where('status' , Reservation::STATUS_ACTIVE)])
                ->paginate(20);
        
            
        
        $resource = OfficeResource::collection($offices) ;

        return $resource;

        // foreach($resource as $re){
        //     echo('<pre>');
        //     var_dump($re);
        //     echo('</pre>');

        // }

    }

    public function show(Office $office)
    {

        Office::factory()->create();
        
        $office->loadCount(
            ['reservations' => fn($builder) => $builder->where('status' , Reservation::STATUS_ACTIVE)])
            ->load(['user' , 'tags' , 'images']); 

        return OfficeResource::make($office);
    }
}
