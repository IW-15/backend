<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{

    use HasFactory;
    protected $table = "merchants";

    protected $fillable = [
        "id_user",
        "name",
        "address",
        "phone",
        "email",
        "applyForm",
        "ktp",
        "photo",
        "license",
        "npwp"
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
