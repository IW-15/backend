<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = "transactions";

    protected $fillable = [
        "id_user",
        "name",
        "type",
        "amount",
        "date"
    ];

    protected $casts = [
        'amount' => 'int', // Mengonversi ke decimal dengan 2 tempat desimal
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "id_user");
    }
}
