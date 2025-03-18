<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    // Specify the table if it's not the plural form of the model name
    protected $table = 'outlets';

    // Define the fillable properties for mass assignment
    protected $fillable = [
        'id_revenue',
        'id_merchant',
        'id_user',
        'name',
        'type',
        'phone',
        'email',
        'rekening',
        'address',
    ];

    // Define the relationships
    public function revenue()
    {
        return $this->belongsTo(OutletRevenue::class, 'id_revenue');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'id_merchant');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    protected $casts = [
        "id_revenue" => "int"
    ];
}
