<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutletRevenue extends Model
{
    /** @use HasFactory<\Database\Factories\OutletRevenueFactory> */
    use HasFactory;

    protected $table = "outlet_revenues";

    protected $fillable = ["label"];

    protected $casts = [
        "id" => "int"
    ];
}
