<?php

namespace App\Http\Controllers;

use App\Models\Popup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PopupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $popup = Popup::first();

        return view('popup.index', compact('popup', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'title' => 'required|string',
            // 'is_active' => 'nullable|boolean',
        ]);

        Popup::updateOrCreate(
            ['id' => 1],
            [
                'title' => $request->title,
                'message' => $request->message,
                'is_active' => $request->has('is_active'),
            ]
        );

        return redirect()->back()->with('success', 'Popup updated successfully.');
    }
}
