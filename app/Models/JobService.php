<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; class JobService extends Model { use BelongsToTenant;protected $guarded=[]; protected $casts=['removed'=>'boolean']; public function service(){return $this->belongsTo(Service::class);} public function approval(){return $this->hasOne(ServiceApproval::class);}}
