<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\GameQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AttemptController extends Controller
{
    /**
     * Display a listing of the authenticated user's attempts.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Attempt::where('user_id', $user->id)
            ->with('question');

        // Filter by question_id if provided
        if ($request->has('question_id')) {
            $query->where('question_id', $request->question_id);
        }

        // Filter by correctness if provided
        if ($request->has('is_correct')) {
            $query->where('is_correct', $request->boolean('is_correct'));
        }

        // Pagination
        $perPage = $request->integer('per_page', 15);
        $attempts = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $attempts->items(),
            'pagination' => [
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
            ],
        ]);
    }

    /**
     * Display the specified attempt.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        $attempt = Attempt::where('user_id', $user->id)
            ->with('question')
            ->find($id);

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Attempt not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attempt,
        ]);
    }

    /**
     * Store a newly created attempt (submit an answer).
     */
  public function store(Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'question_id' => ['required', 'integer', 'exists:game_questions,id'],
            'user_answer' => ['required', 'in:A,B,C,D'],
        ]);

        $user = Auth::user();

        // Check if already attempted
        $existingAttempt = Attempt::where('user_id', $user->id)
            ->where('question_id', $validated['question_id'])
            ->first();

        if ($existingAttempt) {
            return response()->json([
                'success' => false,
                'message' => 'You already attempted this question',
            ], 409);
        }

        $question = GameQuestion::find($validated['question_id']);
        $isCorrect = $validated['user_answer'] === $question->correct_answer;

        // ⭐ FIX — UPDATE USER POINTS ⭐
        if ($isCorrect) {
            $user->increment('points', 10);
        }

        $attempt = Attempt::create([
            'user_id' => $user->id,
            'question_id' => $validated['question_id'],
            'user_answer' => $validated['user_answer'],
            'is_correct' => $isCorrect,
        ]);

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'points_added' => $isCorrect ? 10 : 0,
            'total_points' => $user->fresh()->points,
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors(),
        ], 422);
    }
}

    /**
     * Get user's attempt statistics.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();

        $totalAttempts = Attempt::where('user_id', $user->id)->count();
        $correctAttempts = Attempt::where('user_id', $user->id)
            ->where('is_correct', true)
            ->count();
        $incorrectAttempts = $totalAttempts - $correctAttempts;

        $accuracy = $totalAttempts > 0
            ? round(($correctAttempts / $totalAttempts) * 100, 2)
            : 0;

        // Get attempts by question (to see which questions were answered)
        $attemptedQuestions = Attempt::where('user_id', $user->id)
            ->distinct('question_id')
            ->count('question_id');

        $totalQuestions = GameQuestion::count();
        $unattemptedQuestions = $totalQuestions - $attemptedQuestions;

        return response()->json([
            'success' => true,
            'data' => [
                'total_attempts' => $totalAttempts,
                'correct_attempts' => $correctAttempts,
                'incorrect_attempts' => $incorrectAttempts,
                'accuracy_percentage' => $accuracy,
                'total_points' => $user->points,
                'attempted_questions' => $attemptedQuestions,
                'total_questions' => $totalQuestions,
                'unattempted_questions' => $unattemptedQuestions,
            ],
        ]);
    }

    /**
     * Get leaderboard (top users by points).
     */
 public function leaderboard()
{
    $leaders = User::orderBy('points', 'DESC')
        ->take(20)
        ->get(['id', 'name', 'points']);

    return response()->json([
        'success' => true,
        'data' => $leaders
    ]);
}



    /**
     * Get attempts for a specific question.
     */
    public function getByQuestion(int $questionId): JsonResponse
    {
        $user = Auth::user();

        $attempt = Attempt::where('user_id', $user->id)
            ->where('question_id', $questionId)
            ->with('question')
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'No attempt found for this question',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attempt,
        ]);
    }
}

