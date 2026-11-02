<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiContentController extends Controller
{
    protected $aiService;

    public function __construct(GeminiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Generate news article content using AI.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateNewsContent(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'details' => 'nullable|string|max:500',
            ]);

            $content = $this->aiService->generateNewsContent(
                $validated['title'],
                $validated['category'],
                $validated['details'] ?? null
            );

            return apiSuccess('News content generated successfully', [
                'content' => $content
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            Log::error('AI News Content Generation Error: ' . $e->getMessage());
            return apiError('Failed to generate content. Please try again later.', null, 500);
        }
    }
}
