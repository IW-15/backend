<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditScore extends Model
{
    use HasFactory;

    protected $table = 'credit_scores'; // Optional, Laravel infers table name from model

    protected $fillable = [
        'id_loan_profile',
        'score',
        'indicator'
    ];

    // Define the relationship with LoanProfile
    public function loanProfile()
    {
        return $this->belongsTo(LoanProfile::class, 'id_loan_profile');
    }
}
