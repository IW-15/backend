<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events'; // specify table name if not pluralized correctly

    protected $fillable = [
        'id_eo',
        'name',
        'date',
        'time',
        'category',
        'location',
        'latitude',
        'longitude',
        'venue',
        'visitorNumber',
        'tenantNumber',
        'tenantPrice',
        'description',
        'status',
        'pic',
        'banner',
        'picNumber',
    ];

    protected $casts = [
        'date' => 'date',               // cast date to Carbon instance
        'time' => 'time',               // cast time to Carbon instance
        'latitude' => 'double',         // cast latitude to double
        'longitude' => 'double',        // cast longitude to double
        'visitorNumber' => 'int',   // cast visitorNumber to int
        'tenantNumber' => 'int',    // cast tenantNumber to integer
        'tenantPrice' => 'int',   // cast tenantPrice to decimal with 2 decimal points
        'status' => 'string',           // status casted as string (optional)
    ];

    // Define the relationship with eo
    public function eo()
    {
        return $this->belongsTo(Eo::class, 'id_eo');
    }
}
