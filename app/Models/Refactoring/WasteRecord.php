<?php

namespace App\Models\Refactoring;

use App\Models\Refactoring\WasteRecordItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'moderator_id',
        'car_num',
        'driv_name',
        'record_date',
    ];

    public function items()
    {
        return $this->hasMany(WasteRecordItem::class, 'waste_record_id');
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
