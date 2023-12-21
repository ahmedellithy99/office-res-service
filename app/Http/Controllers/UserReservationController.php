<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReservationResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserReservationController extends Controller


{

    public function index()
    {

        auth()->user()->tokenCan('reservations.show');
        
        
        validator(request()->all(),
            [
                'status' => [Rule::in([Reservation::STATUS_ACTIVE , Reservation::STATUS_CANCELLED])],
                'from_date' =>['date' , 'required_with:to_date' ],
                'to_date' =>['date' , 'required_with:from_date' ],
                'office_id' => ['integer']
            ]

        )->validate();

        $reservations = Reservation::query()
                    ->where('user_id' , auth()->id())
                    ->when(request('office_id') , 
                    fn($query) => $query->where('office_id' , request('office_id')))
                    ->when(request('status') , 
                    fn($query) => $query->where('status' , request('status')))
                    ->when(
                        request('from_date') && request('to_date'), 
                        fn($query) => $query->betweenDates(request('from_date') , request('to_date'))
                    )
                    ->with('office.featuredImage')
                    ->paginate(20);


        return ReservationResource::collection($reservations);

    }

    public function create()
    {
        if(!auth()->user()->tokenCan('reservations.show')){
            Response::HTTP_FORBIDDEN;
            abort(403);
        }

        validator(request()->all() , 
        [
            'office_id' => ['required' , 'integer'],
            'start_date' => ['required' , 'date:Y-m-d'],
            'end_date' => ['required' , 'date:Y-m-d'],

        ]
        )->validate();

        try{
            $office = Office::findOrFail(request('office_id'));
        } 
        catch (ModelNotFoundException $e){
            throw ValidationException::withMessages([
                'office_id' => 'Invalid office_id'
            ]);
        }

        if($office->user_id == auth()->id){
            
            throw ValidationException::withMessages([
                'office_id' => 'You cannot make a reservation on your own office'
            ]);
        }

        




        
        
    }

    
}