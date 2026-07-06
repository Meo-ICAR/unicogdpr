<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $connection = 'mysql_unicooam';

    protected $table = 'unicooam.companies';

    protected $fillable = [
        'name',
        'uuid',
        'address',
        'city',
        'zip_code',
        'province',
        'country',
        'email',
        'phone',
    ];
}
