<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ChatInstruction;

class ChatInstructionController extends Controller
{
    public function edit(Request $request)
    {
        $instructions = \App\Models\ChatInstruction::where('user_id',auth()->id())->first();

        return Inertia::render('Chat/Instructions', [
            'instruction' => $instructions,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'about_you' => 'nullable|array',
            'behaviour' => 'nullable|array',
            'commands' => 'nullable|array',
        ]);

        $user->chatInstruction()->updateOrCreate(['user_id' => $user->id], $data);

        return redirect('/conversations')->with('success', 'Instructions enregistrées');
    }
}
