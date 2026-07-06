<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroTrattamentiItem extends Model
{
    use HasFactory;

    protected $table = 'registro_trattamenti_items';

    protected $fillable = [
        'company_id',
        'Attivita',
        'Finalita',
        'Interessati',
        'Dati',
        'Giuridica',
        'Destinatari',
        'extraEU',
        'Conservazione',
        'Sicurezza',
    ];
}
