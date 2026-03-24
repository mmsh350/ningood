<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserServicePrice;
use Illuminate\Http\Request;

class UserServicePriceController extends Controller
{
    public function store(Request $request, User $user)
    {

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'custom_price' => 'required|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $exists = UserServicePrice::where('user_id', $user->id)
            ->where('service_id', $validated['service_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This service already has a custom price for this user.')->with('active_tab', 'custom-fees');
        }

        UserServicePrice::create([
            'user_id' => $user->id,
            'service_id' => $validated['service_id'],
            'custom_price' => $validated['custom_price'],
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'],
        ]);

        return back()->with('success', 'Custom service price added successfully.')->with('active_tab', 'custom-fees');
    }

    public function destroy(UserServicePrice $price)
    {

        $price->delete();

        return back()->with('success', 'Custom service price removed successfully.')
            ->with('active_tab', 'custom-fees');
    }
}
