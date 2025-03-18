<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repayment extends Model
{
    use HasFactory;

    protected $table = 'repayments'; // Optional if your table name follows conventions

    protected $fillable = [
        'id_loan',
        'date',
        'order',
        'amount'
    ];

    // Define the relationship with Loan
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'id_loan');
    }

    protected $casts = [
        "date" => "date",
        "amount" => "int"
    ];
}
