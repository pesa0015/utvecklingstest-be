<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request)
    {
        $user = auth()->user();

        if (auth()->attempt(['pnr' => $user->pnr, 'password' => $request->current_password])) {
            $user->update([
                'password' => Hash::make($request['password']),
            ]);

            return response()->json([], 200);
        }

        throw ValidationException::withMessages(['invalid_credentials']);
    }
}
