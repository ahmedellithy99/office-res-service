<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 1 ; 
    const STATUS_CANCELLED = 2 ;

    protected $cast = [
        'price' => 'integer' ,
        'status' => 'integer',
        'start_date' =>'immutabel_date',
        'end_date' =>'immutabel_date'
    ];
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office():BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function scopeBetweenDates($query , $from , $to)

    {
        return $query->where(function($query) use($from , $to )
        {
            return $query->whereBetween('start_date' , [$from , $to])
            ->orWhereBetween('end_date' , [$from, $to]);
        });
    }
}
