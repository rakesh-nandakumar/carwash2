<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Appointment extends Model { use BelongsToTenant; use HasFactory; protected $guarded=[]; protected $casts=['active'=>'boolean','scheduled_at'=>'datetime']; public function customer(){return $this->belongsTo(Customer::class);} public function vehicle(){return $this->belongsTo(Vehicle::class);} public function branch(): BelongsTo {return $this->belongsTo(Branch::class)->withDefault();} }
