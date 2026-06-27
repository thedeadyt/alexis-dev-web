<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    protected $fillable = ['slug','name','client','category','year','summary','full_text','tech','rendered','sort_order','active'];
    protected $casts = ['full_text'=>'array','tech'=>'array','rendered'=>'array','active'=>'boolean'];
}
