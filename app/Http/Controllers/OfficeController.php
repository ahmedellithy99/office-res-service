<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Validator\OfficeValidator;
use App\Notifications\OfficeApprovalPending;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    public function index()
    {

        
        $offices = Office::query()
                ->when(request('user_id') && auth()->user() && request('user_id') == auth()->user()->id ,
                fn($builder) => $builder ,
                fn($builder) => $builder
                ->where('approval_status' , Office::APPROVAL_APPROVED)
                ->where('hidden' , false))
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

    public function create()

    {
        
        if(!auth()->user()->tokenCan('office.create')){
            abort(403);
        }

        $attributes = (new OfficeValidator())->validate(
            $office = new Office() ,
            request()->all()
        );

        $attributes['user_id'] = auth()->id();
        $attributes['approval_status'] = Office::APPROVAL_PENDING;
        

        

        $office = DB::transaction(function () use ($office ,$attributes) {
            $office->fill(
                Arr::except($attributes,['tags'])
            )->save();
    
            $office->tags()->attach($attributes['tags']);

            return $office ;
    
        });

        Notification::send(User::where('is_admin' , true)->get() ,new OfficeApprovalPending($office) );
        
        return OfficeResource::make(
            $office->load(['images' , 'user' , 'tags'])
        );
    }

    public function update(Office $office)

    {
        
        $this->authorize('update' , $office);
        
        if(!auth()->user()->tokenCan('office.update')){
            abort(403);
            
        }

        $attributes = (new OfficeValidator())->validate(
            $office ,
            request()->all()
        );
        
        $office->fill(
            Arr::except($attributes,['tags'])
        );

        if($reviewChanged = $office->isDirty(['lat' , 'lng' , 'price_per_day']))
        {
            $office['approval_status'] = Office::APPROVAL_PENDING;
        }

        
        
        $office = DB::transaction(function () use ($office ,$attributes) {
            
            $office->save();
    
            if(isset($attributes['tags'])){
                
                $office->tags()->sync($attributes['tags']);
            }

            return $office ;
    
        });

        if($reviewChanged)
        {
            Notification::send(User::where('is_admin' , true)->get() ,new OfficeApprovalPending($office) );
        }
        
        return OfficeResource::make(
            $office->load(['images' , 'user' , 'tags'])
        );
    }

    public function delete(Office $office)
    {
        $this->authorize('delete' , $office);
        
        if(!auth()->user()->tokenCan('office.delete')){
            abort(403);
            
        }

        if($office->reservations()->where('status' , Reservation::STATUS_ACTIVE)->exists()){
            throw ValidationException::withMessages(['office' => 'msa msa']);
        }

        $office->delete();
    }
}