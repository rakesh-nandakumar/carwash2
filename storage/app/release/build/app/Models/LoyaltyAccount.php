<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory;
class LoyaltyAccount extends Model { use BelongsToTenant; use HasFactory; protected $guarded=[]; protected $casts=['active'=>'boolean']; }
