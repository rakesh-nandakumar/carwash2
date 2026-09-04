<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory;
class Customer extends Model { use BelongsToTenant;use HasFactory; protected $guarded=[]; public function vehicles(){return $this->hasMany(Vehicle::class);} public function jobs(){return $this->hasMany(Job::class);} public function invoices(){return $this->hasMany(Invoice::class);} public function loyalty(){return $this->hasOne(LoyaltyAccount::class);} }
