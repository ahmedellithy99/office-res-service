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
                ->when(request('host_id') , fn($builder) => $builder->where('user_id' , request('host_id')))
                ->when(request('user_id') , fn(Builder $builder) => $builder->whereRelation('reservations','user_id' , request('user_id')))
                ->orderBy('id' , 'DESC')
                ->get();
        

        
        $resource = OfficeResource::collection($offices) ;

        return $resource;
    }
}
