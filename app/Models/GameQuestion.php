<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameQuestion extends Model
{
    use HasFactory;

    protected $table = 'game_questions';

    protected $fillable = [
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
    ];

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'question_id');
    }
}


