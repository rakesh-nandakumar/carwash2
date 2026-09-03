<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory;
class LoyaltyAccount extends Model { use HasFactory; protected $guarded=[]; protected $casts=['active'=>'boolean']; }
