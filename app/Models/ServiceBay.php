<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ServiceBay extends Model { use BelongsToTenant; use HasFactory; protected $guarded=[]; protected $casts=['active'=>'boolean']; public function branch(): BelongsTo {return $this->belongsTo(Branch::class)->withDefault();} }
