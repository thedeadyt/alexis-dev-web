<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model {
    protected $fillable = ['quote','author','role','sort_order','active'];
    protected $casts = ['active'=>'boolean'];
}
