<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisHistory extends Model
{
    use HasFactory;

    protected $table = 'analysis_histories';

    protected $fillable = [
        'user_id',
        'result_palette',
        'ai_result',
        'input_data',
        'colors',
        'notes',
        'image_url',
    ];

    protected $casts = [
        'ai_result'  => 'array',
        'input_data' => 'array',
        'colors'     => 'array',
    ];
}
