<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpleAskStreamService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;



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

    public function index(Request $request)
    {
        $modelId = $request->input('model')
            ?? auth()->user()?->preferred_model
            ?? SimpleAskStreamService::DEFAULT_MODEL;

        return Inertia::render('AskStream/Index', [
            'models' => $this->streamService->getModelsLight(),
            'selectedModel' => $modelId,
        ]);
    }

    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:100000',
            'model' => 'required|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'reasoning_effort' => 'nullable|string|in:low,medium,high',
        ]);

        return response()->stream(
            function () use ($validated) {

                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                $this->streamService->streamToOutput(
                    [['role' => 'user', 'content' => $validated['message']]],
                    $validated['model'],
                    (float) ($validated['temperature'] ?? 1.0),
                    $validated['reasoning_effort'] ?? null
                );
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
