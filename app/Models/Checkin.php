<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Checkin extends Model
{
    /** @use HasFactory<\Database\Factories\CheckinFactory> */
    use HasFactory;

    protected $fillable = ['uuid', 'in', 'out', 'latitude', 'longitude'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Interact with the checkin time
     */
    protected function in(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Carbon::parse($value)->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Interact with the checkout time
     */
    protected function out(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Carbon::parse($value)->format('Y-m-d H:i:s'),
        );
    }
}
