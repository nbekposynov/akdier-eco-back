<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalProcessing extends Model
{
    use HasFactory;
    protected $table = 'final_processing';

    protected $fillable = [
    'kod_othoda',
    'name_othod',
    'company_id',
    'value',
    'type_operation',
    ];



    public function moderator()
    {
        return $this->belongsTo(Moderator::class, 'moderator_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
