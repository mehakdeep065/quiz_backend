<?php

namespace App\Http\Controllers;

use App\Models\GameQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    /**
     * Display a listing of questions.
     * Optionally hide correct answers for quiz mode.
     */
    public function index(Request $request): JsonResponse
    {
        $hideAnswers = $request->boolean('hide_answers', false);
        
        $questions = GameQuestion::all();
        
        if ($hideAnswers) {
            // Return questions without correct_answer for quiz mode
            $questions = $questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'option_a' => $question->option_a,
                    'option_b' => $question->option_b,
                    'option_c' => $question->option_c,
                    'option_d' => $question->option_d,
                ];
            });
        }
        
        return response()->json([
            'success' => true,
            'data' => $questions,
            'count' => $questions->count(),
        ]);
    }

    /**
     * Display the specified question.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $hideAnswers = $request->boolean('hide_answers', false);
        
        $question = GameQuestion::find($id);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }
        
        $questionData = [
            'id' => $question->id,
            'question' => $question->question,
            'option_a' => $question->option_a,
            'option_b' => $question->option_b,
            'option_c' => $question->option_c,
            'option_d' => $question->option_d,
        ];
        
        if (!$hideAnswers) {
            $questionData['correct_answer'] = $question->correct_answer;
        }
        
        return response()->json([
            'success' => true,
            'data' => $questionData,
        ]);
    }

    /**
     * Store a newly created question.
     * Typically requires admin authentication.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'question' => ['required', 'string'],
                'option_a' => ['required', 'string'],
                'option_b' => ['required', 'string'],
                'option_c' => ['required', 'string'],
                'option_d' => ['required', 'string'],
                'correct_answer' => ['required', 'in:A,B,C,D'],
            ]);

            $question = GameQuestion::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully',
                'data' => $question,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update the specified question.
     * Typically requires admin authentication.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $question = GameQuestion::find($id);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'question' => ['sometimes', 'required', 'string'],
                'option_a' => ['sometimes', 'required', 'string'],
                'option_b' => ['sometimes', 'required', 'string'],
                'option_c' => ['sometimes', 'required', 'string'],
                'option_d' => ['sometimes', 'required', 'string'],
                'correct_answer' => ['sometimes', 'required', 'in:A,B,C,D'],
            ]);

            $question->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully',
                'data' => $question,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified question.
     * Typically requires admin authentication.
     */
    public function destroy(int $id): JsonResponse
    {
        $question = GameQuestion::find($id);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully',
        ]);
    }

    /**
     * Get a random question for quiz.
     */
    public function random(Request $request): JsonResponse
    {
        $hideAnswers = $request->boolean('hide_answers', true);
        
        $question = GameQuestion::inRandomOrder()->first();
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'No questions available',
            ], 404);
        }
        
        $questionData = [
            'id' => $question->id,
            'question' => $question->question,
            'option_a' => $question->option_a,
            'option_b' => $question->option_b,
            'option_c' => $question->option_c,
            'option_d' => $question->option_d,
        ];
        
        if (!$hideAnswers) {
            $questionData['correct_answer'] = $question->correct_answer;
        }
        
        return response()->json([
            'success' => true,
            'data' => $questionData,
        ]);
    }
    public function checkAnswers(Request $request)
{
    // $user = Auth::user();
    $answers = $request->answers;
    $questions = GameQuestion::all();

    $score = 0;
    $correctAnswers = [];

    foreach ($questions as $q) {
        $correct = $q->correct_answer; // A/B/C/D

        if (isset($answers[$q->id]) && $answers[$q->id] === $correct) {
            $score++;
        }

        $correctAnswers[] = [
            "question_id" => $q->id,
            "correct_option" => $correct
        ];
    }
    //   $user->points = $user->points + $score; 
    // $user->save();
    return response()->json([
        "score" => $score,
        "correct_answers" => $correctAnswers
    ]);
}

}

