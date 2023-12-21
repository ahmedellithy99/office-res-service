<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function show(User $user)
    {
        if(!(auth()->user()->id == $user->id))
        {
            abort(403);
        }

        
        $reservations = Reservation::query()
                                    ->where('user_id' ,  $user->id )
                                    
                                    ->get();


        return ReservationResource::collection($reservations);


    }
}
