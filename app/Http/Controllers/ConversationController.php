<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\SimpleAskService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    public function __construct(private SimpleAskService $askService) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ?Conversation $conversation = null)
    {
       $user = Auth::user();

       $conversationId = $request->route('conversation') ? $request->route('conversation')->id : $request->query('active_id');

       if ($conversationId) {
             $conversation = Conversation::where('id', $conversationId)->where('user_id', $user->id)->firstOrFail();
        }

        $messages = $conversation
            ? $conversation->messages()->orderBy('id')->get(['role', 'content', 'id'])->toArray()
            : [];

            // dd($messages);

        $userConversations = $user->conversations()->orderBy('updated_at', 'desc')->get(['id', 'title']);

        $models = $this->askService->getModels();


        return Inertia::render('Conversations/Index', [
            'activeConversationId' => $conversation ? $conversation->id : null,
            'messages' => $messages,
            'conversations' => $userConversations,
            'models' => $models,
            'selectedModel' => $conversation->model ?? null,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        set_time_limit(300);

        $request->validate(['prompt' => 'required|string|max:1000']);

        $userPrompt = $request->input('prompt');

        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'Nouvelle conversation…',
        ]);

        $apiMessages = [['role' => 'user', 'content' => $userPrompt]];

        try {
            $aiResponseContent = $this->askService->sendMessage($apiMessages);
        } catch (\Exception $e) {

             $aiResponseContent = "Erreur : Désolé, il y a eu un problème pour contacter l’IA." . $e->getMessage();
        }

        $conversation->messages()->createMany([
            ['role' => 'user', 'content' => $userPrompt],
            ['role' => 'assistant', 'content' => $aiResponseContent],
        ]);

        $conversation->update(['title' => Str::limit($userPrompt, 50)]);


        return redirect()->route('conversations.index', $conversation->id);
    }

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

        return response()->json(['success' => true]);
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

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate(['prompt' => 'required|string|max:1000']);


        $userPrompt = $request->input('prompt');
        $model = $request->input('model');

         if ($model) {
        $conversation->update(['model' => $model]);
         }

        $apiMessages = $conversation->messages()
            ->orderBy('id')
            ->get(['role', 'content'])
            ->toArray();

        $apiMessages[] = ['role' => 'user', 'content' => $userPrompt];

        try {
            $aiResponseContent = $this->askService->sendMessage($apiMessages);

        } catch (\Exception $e) {
             $aiResponseContent = $e->getMessage();
        }

        $conversation->messages()->createMany([
            [
                'role' => 'user',
                'content' => $userPrompt,
            ],
            [
                'role' => 'assistant',
                'content' => $aiResponseContent,
            ],
        ]);

        $conversation->update(['title'=>Str::limit($userPrompt,50)]);


        $messages = $conversation->messages()->orderBy('id')->get(['id','role','content'])->toArray();
        $userConversations = auth()->user()->conversations()->orderBy('updated_at','desc')->get(['id','title']);

        return Inertia::render('Conversations/Index', [
            'activeConversationId' => $conversation->id,
            'messages' => $messages,
            'conversations' => $userConversations,
            'models' => $this->askService->getModels(),
            'selectedModel' => $conversation->model,
        ]);
    }
}

