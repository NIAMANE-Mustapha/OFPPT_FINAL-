<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Langue extends Model
{
    protected $fillable=['LangueName','Certificat','Niveau','CIN'];
}