<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory;
class QualityCheck extends Model { use HasFactory; protected $guarded=[]; protected $casts=['active'=>'boolean']; }
