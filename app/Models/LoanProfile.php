<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProfile extends Model
{
    use HasFactory;

    protected $table = 'loan_profiles';

    protected $fillable = [
        'id_merchant',
        'id_user',
        'maxAmount',
        'minAmount',
        'limit',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'id_merchant');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'id_loan_profile');
    }

    public function creditScore()
    {
        return $this->has(CreditScore::class, 'id_loan_profile');
    }

    protected $casts = [
        "maxAmount" => "int",
        "minAmount" => "int",
        "limit" => "int",
    ];
}
