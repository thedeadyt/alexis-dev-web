<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model {
    protected $fillable = ['slug','label','title','sub','body','tags','price','sort_order','active'];
    protected $casts = ['tags'=>'array','active'=>'boolean'];
}
