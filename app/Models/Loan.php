<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loans'; // Optional if your table name follows conventions

    protected $fillable = [
        'id_loan_profile',
        'amount',
        'tenor',
        'monthlyBill',
        'applicationDate',
        'status',
    ];

    public function loanProfile()
    {
        return $this->belongsTo(LoanProfile::class, 'id_loan_profile');
    }

    public function repayment()
    {
        return $this->hasMany(Repayment::class, 'id_loan');
    }

    protected $casts = [
        "monthlyBill" => "int",
        "amount" => "int",
        "applicationDate" => "date"
    ];
}
