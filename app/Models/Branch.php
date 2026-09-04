<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; class Branch extends Model { use BelongsToTenant;protected $guarded=[]; public function business(){return $this->belongsTo(Business::class);} public function users(){return $this->hasMany(User::class);}}
