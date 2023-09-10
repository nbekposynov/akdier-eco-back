<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Processing extends Model
{
    use HasFactory;
    
    protected $table = 'processing';

    protected $fillable = [
        'company_id',
        'moderator_id',
        'car_num',
        'driv_name',
        'tbo_total',
        'tbo_food',
        'tbo_plastic',
        'tbo_bumaga',
        'tbo_derevo',
        'tbo_meshki',
        'tbo_metal',
        'tbo_neutil',
        'bsv',
        'tpo_total',
        'tpo_cement',
        'tpo_drevesn',
        'tpo_metall_m',
        'tpo_krishki',
        'tpo_meshki',
        'tpo_plastic',
        'tpo_shini',
        'tpo_vetosh_fi',
        'tpo_makul',
        'tpo_akkum',
        'tpo_tara_met',
        'tpo_tara_pol',
        'po_total',
        'po_neftesh',
        'po_zam_gr',
        'po_bur_shl',
        'po_obr',
        'po_him_reag',
        'created_at',
        'updated_at',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
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
