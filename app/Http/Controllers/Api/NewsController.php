<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NewsController extends Controller
{
    /**
     * Display a listing of news articles.
     */
    public function index(Request $request)
    {
        try {
            $query = News::query();

            // Search by title
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('title', 'like', "%{$search}%");
            }

            // Filter by category
            if ($request->has('category')) {
                $query->where('category', $request->get('category'));
            }

            // Filter by published status (default: show only published for public)
            if ($request->get('published_only', false)) {
                $query->published();
            }

            $news = $query->orderBy('created_at', 'desc')->get();

            return apiSuccess('News articles retrieved successfully', $news);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve news articles: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified news article.
     */
    public function show(string $id)
    {
        try {
            $news = News::find($id);

            if (!$news) {
                return apiNotFound('News article not found');
            }

            return apiSuccess('News article retrieved successfully', $news);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve news article: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created news article.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'excerpt' => 'required|string|max:500',
                'content' => 'required|string',
                'image' => 'nullable|string|max:500',
                'category' => 'nullable|string|max:100',
                'published' => 'nullable|boolean',
            ]);

            $news = News::create($validated);

            return apiSuccess('News article created successfully', $news, 201);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to create news article: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified news article.
     */
    public function update(Request $request, string $id)
    {
        try {
            $news = News::find($id);

            if (!$news) {
                return apiNotFound('News article not found');
            }

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'excerpt' => 'sometimes|string|max:500',
                'content' => 'sometimes|string',
                'image' => 'nullable|string|max:500',
                'category' => 'sometimes|string|max:100',
                'published' => 'sometimes|boolean',
            ]);

            $news->update($validated);

            return apiSuccess('News article updated successfully', $news);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update news article: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified news article.
     */
    public function destroy(string $id)
    {
        try {
            $news = News::find($id);

            if (!$news) {
                return apiNotFound('News article not found');
            }

            $news->delete();

            return apiSuccess('News article deleted successfully');
        } catch (\Exception $e) {
            return apiError('Failed to delete news article: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get published news for public display.
     */
    public function published()
    {
        try {
            $news = News::published()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return apiSuccess('Published news retrieved successfully', $news);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve published news: ' . $e->getMessage(), null, 500);
        }
    }
}
