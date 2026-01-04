<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpleAskStreamService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Conversation;
use Illuminate\Support\Str;


/**
 * Controller pour la démonstration du streaming SSE.
 *
 * Exemple pédagogique : streaming temps réel avec Laravel + Vue.
 */
class AskStreamController extends Controller
{
    public function __construct(
        private SimpleAskStreamService $streamService
    ) {}

    /**
     * Affiche la page de streaming.
     */
    public function index(Request $request): Response
    {
        $modelId = $request->input('model')
            ?? auth()->user()?->preferred_model
            ?? SimpleAskStreamService::DEFAULT_MODEL;

        return Inertia::render('AskStream/Index', [
            'models' => $this->streamService->getModelsLight(),
            'selectedModel' => $modelId,
            'selectedModelDetails' => fn() => $this->streamService->getModelDetails($modelId),
        ]);
    }

    /**
     * Endpoint de streaming.
     */
    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:100000',
            'model' => 'required|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'reasoning_effort' => 'nullable|string|in:low,medium,high',
        ]);

        // Update user's preferred model
        $user = auth()->user();
        if ($user && $user->preferred_model !== $validated['model']) {
            $user->update(['preferred_model' => $validated['model']]);
        }

        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'title' => Str::limit(trim($validated['message']), 50),
        ]);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $messages = [
            ['role' => 'user', 'content' => $validated['message']],
        ];
        $model = $validated['model'];
        $temperature = (float) ($validated['temperature'] ?? 1.0);
        $reasoningEffort = $validated['reasoning_effort'] ?? null;

        return response()->stream(
            function () use ($conversation, $messages, $model, $temperature, $reasoningEffort): void {

                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                $this->streamService->streamToOutput($messages, $model, $temperature, $reasoningEffort);

                echo "\n{$conversation->id}";
                flush();

                $this->streamService->streamToOutput($messages, $model, $temperature, $reasoningEffort);

                $fullAIResponse = $this->streamService->getLastFullContent();

                if (!empty($fullAIResponse)) {
                    $conversation->messages()->create([
                        'role' => 'assistant',
                        'content' => $fullAIResponse,
                    ]);
                }
            },
            headers: [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }
}
