<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ChatInstruction;

class ChatInstructionController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $instructions = $user->chatInstruction;

        return Inertia::render('Chat/Instructions', [
            'instruction' => $instructions,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'about_you' => 'nullable|string',
            'behaviour' => 'nullable|string',
            'commands' => 'nullable|array',
        ]);

        $instructions = $user->chatInstruction;

        if ($instructions) {
            $instructions->update($data);
        } else {
            $data['user_id'] = $user->id;
            $user->chatInstruction()->create($data);
        }

        return redirect()->route('chat.instructions.edit')->with('success', 'Instructions mises à jour avec succès!');
    }
}
