<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; class JobPart extends Model { use BelongsToTenant;protected $guarded=[]; protected $casts=['applied'=>'boolean']; public function product(){return $this->belongsTo(Product::class);} public function job(){return $this->belongsTo(Job::class);}}
