<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; class Inventory extends Model { use BelongsToTenant; protected $table='inventory'; protected $guarded=[]; public function product(){return $this->belongsTo(Product::class);} public function branch(){return $this->belongsTo(Branch::class)->withDefault();}}
