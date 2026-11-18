<?php

namespace App\Models;

use App\Enums\InstitutionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'logo_file_id',
        'timezone',
        'locale',
        'status',
    ];

    protected $casts = [
        'type' => InstitutionType::class,
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }
}
