<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Document extends Model {
    protected $fillable = [
        'tenant_id','folder_id','title','path','mime','size','checksum','version','status','created_by'
    ];
}
