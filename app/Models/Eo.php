<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eo extends Model
{
    use HasFactory;

    protected $table = 'eos'; // specify table name if not pluralized correctly

    protected $fillable = [
        'id_user',
        'name',
        'nib',
        'pic',
        'picPhone',
        'email',
        'address',
        'document',
    ];

    // Define the relationship with events
    public function events()
    {
        return $this->hasMany(Event::class, 'id_eo');
    }
}
