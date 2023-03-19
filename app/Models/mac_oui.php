<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mac_oui extends Model
{
    use HasFactory;

    //  allow mass assignment
    protected $fillable = [
      'oui', 'organization_name', 'organization_address', 'created_at', 'updated_at'
    ];
}
