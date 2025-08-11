<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model {
    protected $fillable = ['tenant_id','parent_id','name'];
}
