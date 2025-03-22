<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventInvitation extends Model
{
    use HasFactory;

    // Define the table name if it doesn't follow Laravel's naming convention
    protected $table = 'event_invitation';

    // Fillable properties to allow mass assignment
    protected $fillable = [
        'id_eo',
        'id_event',
        'id_sme',
        'id_outlet',
        'status',
        'date',
    ];

    protected $casts = [
        'id_eo' => 'int',
        'id_event' => 'int',
        'id_sme' => 'int',
        'id_outlet' => 'int',
        'date' => 'date',
    ];

    // Define relationships if needed
    public function eo()
    {
        return $this->belongsTo(Eo::class, 'id_eo');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    public function sme()
    {
        return $this->belongsTo(Merchant::class, 'id_sme');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'id_outlet');
    }
}
