<?php

namespace Database\Seeders;

use App\Models\Attempt;
use App\Models\User;
use App\Models\GameQuestion;
use Illuminate\Database\Seeder;

class AttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have users and questions
        $users = User::all();
        $questions = GameQuestion::all();

        // If no users exist, create some
        if ($users->isEmpty()) {
            $users = User::factory(5)->create();
        }

        // If no questions exist, create some sample questions
        if ($questions->isEmpty()) {
            $sampleQuestions = [
                [
                    'question' => 'What is the capital of France?',
                    'option_a' => 'London',
                    'option_b' => 'Berlin',
                    'option_c' => 'Paris',
                    'option_d' => 'Madrid',
                    'correct_answer' => 'C',
                ],
                [
                    'question' => 'Which planet is known as the Red Planet?',
                    'option_a' => 'Venus',
                    'option_b' => 'Mars',
                    'option_c' => 'Jupiter',
                    'option_d' => 'Saturn',
                    'correct_answer' => 'B',
                ],
                [
                    'question' => 'What is 2 + 2?',
                    'option_a' => '3',
                    'option_b' => '4',
                    'option_c' => '5',
                    'option_d' => '6',
                    'correct_answer' => 'B',
                ],
                [
                    'question' => 'Who wrote "Romeo and Juliet"?',
                    'option_a' => 'Charles Dickens',
                    'option_b' => 'William Shakespeare',
                    'option_c' => 'Jane Austen',
                    'option_d' => 'Mark Twain',
                    'correct_answer' => 'B',
                ],
                [
                    'question' => 'What is the largest ocean on Earth?',
                    'option_a' => 'Atlantic Ocean',
                    'option_b' => 'Indian Ocean',
                    'option_c' => 'Arctic Ocean',
                    'option_d' => 'Pacific Ocean',
                    'correct_answer' => 'D',
                ],
                [
                    'question' => 'Which programming language is known as the language of the web?',
                    'option_a' => 'Python',
                    'option_b' => 'Java',
                    'option_c' => 'JavaScript',
                    'option_d' => 'C++',
                    'correct_answer' => 'C',
                ],
                [
                    'question' => 'What is the chemical symbol for water?',
                    'option_a' => 'H2O',
                    'option_b' => 'CO2',
                    'option_c' => 'O2',
                    'option_d' => 'NaCl',
                    'correct_answer' => 'A',
                ],
                [
                    'question' => 'How many continents are there?',
                    'option_a' => '5',
                    'option_b' => '6',
                    'option_c' => '7',
                    'option_d' => '8',
                    'correct_answer' => 'C',
                ],
                [
                    'question' => 'What is the speed of light in vacuum?',
                    'option_a' => '300,000 km/s',
                    'option_b' => '150,000 km/s',
                    'option_c' => '200,000 km/s',
                    'option_d' => '250,000 km/s',
                    'correct_answer' => 'A',
                ],
                [
                    'question' => 'Which year did World War II end?',
                    'option_a' => '1943',
                    'option_b' => '1944',
                    'option_c' => '1945',
                    'option_d' => '1946',
                    'correct_answer' => 'C',
                ],
            ];

            foreach ($sampleQuestions as $questionData) {
                GameQuestion::create($questionData);
            }
            
            $questions = GameQuestion::all();
        }

        // Create attempts for different users and questions
        $attempts = [];

        foreach ($users as $user) {
            // Each user attempts 3-7 random questions (or all questions if there are fewer than 3)
            $numberOfQuestions = min(rand(3, 7), $questions->count());
            
            if ($numberOfQuestions > 0) {
                $randomQuestions = $questions->random($numberOfQuestions);
                
                foreach ($randomQuestions as $question) {
                    // Check if this user already has an attempt for this question
                    $existingAttempt = Attempt::where('user_id', $user->id)
                        ->where('question_id', $question->id)
                        ->exists();
                    
                    // Skip if attempt already exists
                    if ($existingAttempt) {
                        continue;
                    }
                    
                    // Randomly choose an answer (A, B, C, or D)
                    $possibleAnswers = ['A', 'B', 'C', 'D'];
                    $userAnswer = $possibleAnswers[array_rand($possibleAnswers)];
                    
                    // Check if the answer is correct
                    $isCorrect = $userAnswer === $question->correct_answer;

                    $attempts[] = [
                        'user_id' => $user->id,
                        'question_id' => $question->id,
                        'user_answer' => $userAnswer,
                        'is_correct' => $isCorrect,
                        'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert all attempts in batches to avoid memory issues
        if (!empty($attempts)) {
            Attempt::insert($attempts);
        }
    }
}

