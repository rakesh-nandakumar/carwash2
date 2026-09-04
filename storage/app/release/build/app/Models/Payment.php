<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; class Payment extends Model { use BelongsToTenant;protected $guarded=[]; public function invoice(){return $this->belongsTo(Invoice::class);}}
