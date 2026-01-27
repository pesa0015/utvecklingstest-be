<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckinUpdateRequest;
use App\Models\Checkin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

class CheckController extends Controller
{
    /**
     * List all checkins to work
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $checkins = $user->checkins()->select('uuid', 'in', 'out')->get();
        
        return response()->json($checkins, 200);
    }

    /**
     * Check in to work
     */
    public function in(Request $request)
    {
        $user = auth()->user();

        if ((boolean) $request->gps) {
            $ip = $request->ip();

            if (env('APP_ENV') !== 'production') {
                $ip = '88.131.7.250';
            }

            $gps = $request->gps ? Http::get('https://reallyfreegeoip.org/json/' . $ip) : null;

            $latitude = $gps['latitude'];
            $longitude = $gps['longitude'];
        } else {
            $latitude = null;
            $longitude = null;
        }

        $user->checkins()->save(Checkin::create([
            'uuid' => (string) Uuid::uuid4(),
            'in' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude
        ]));
        
        return response()->json([], 200);
    }

    /**
     * Check out from work
     */
    public function out(Request $request)
    {
        $user = auth()->user();

        $user->checkins()->whereNull('out')->update([
            'out' => now()
        ]);

        return response()->json([], 200);
    }

    /**
     * Update the user's profile information.
     */
    public function update(CheckinUpdateRequest $request, $uuid)
    {
        $user = auth()->user();

        $checkin = $user->checkins()->where('uuid', $uuid)->firstOrFail();

        if ($request->checkin) {
            $checkinPayload = $request->checkin;

            $yesterdaysChecking = $user->checkins()->where('id', '<', $checkin->id)->latest()->first();

            if ($yesterdaysChecking && $yesterdaysChecking->out < $checkinPayload) {
                throw new ValidationException;
            }

            if ($checkin->out && $checkinPayload >= $checkin->out) {
                throw new ValidationException;
            }

            $checkin->update(['in' => $checkinPayload]);

            return response()->json([], 200);
        }

        if ($request->checkout) {
            $checkoutPayload = $request->checkout;


            if ($checkoutPayload <= $checkin->in) {
                throw new ValidationException;
            }

            $checkin->update(['out' => $checkoutPayload]);

            return response()->json([], 200);
        }

        return response()->json([], 200);
    }
}
