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
        'input_data',
        'colors',
        'notes',
        'image_url',
        'ai_result',
    ];

    protected $casts = [
        'input_data' => 'array',
        'colors' => 'array',
        'ai_result' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
