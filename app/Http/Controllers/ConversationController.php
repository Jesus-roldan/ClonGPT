<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\SimpleAskService;
use App\Services\SimpleAskStreamService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversationController extends Controller
{
    public function __construct(
    private SimpleAskService $askService,
    private SimpleAskStreamService $streamService) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ?Conversation $conversation = null)
    {
       $user = Auth::user();

       $conversationId = $request->route('conversation') ? $request->route('conversation')->id : $request->query('active_id');

       if ($conversationId) {
             $conversation = Conversation::where('id', $conversationId)->where('user_id', $user->id)->firstOrFail();
        }else {
            $conversation = null;
        }

        $messages = $conversation
            ? $conversation->messages()->orderBy('id')->get(['role', 'content', 'id'])->toArray()
            : [];

        $userConversations = $user->conversations()->orderBy('updated_at', 'desc')->get(['id', 'title']);

        $models = $this->askService->getModels();


        return Inertia::render('Conversations/Index', [
            'activeConversationId' =>$conversation?->id,
            'messages' => $messages,
            'conversations' => $userConversations,
            'models' => $models,
            'selectedModel' => $conversation?->model,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'Nouvelle conversation…',
        ]);

        return redirect()->route('conversations.index', $conversation->id);
    }
    /**
     * Update the specified resource in storage.
     */

    public function updateModel(Request $request, Conversation $conversation)
    {

        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'model' => 'required|string',
        ]);

        $conversation->update([
            'model' => $request->model,
        ]);

        return back();
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $conversation->delete();
        return redirect()->route('conversations.index');
    }

    /**
     * Envoyer un message dans une conversation avec streaming.
     */


    public function sendMessageStream(Request $request, Conversation $conversation): StreamedResponse
    {
        abort_if($conversation->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'message' => 'required|string|max:100000',
            'model' => 'required|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'reasoning_effort' => 'nullable|string|in:low,medium,high',
        ]);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $messages = [
            ['role' => 'user', 'content' => $validated['message']],
        ];

        return response()->stream(
            function () use ($conversation, $messages, $validated) {

                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                $this->streamService->streamToOutput(
                    $messages,
                    $validated['model'],
                    (float) ($validated['temperature'] ?? 1.0),
                    $validated['reasoning_effort'] ?? null
                );

                $full = $this->streamService->getLastFullContent();

                if ($full !== '') {
                    $conversation->messages()->create([
                        'role' => 'assistant',
                        'content' => $full,
                    ]);
                }
            },
            200,
            [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }
}

