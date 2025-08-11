<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Task extends Model {
    protected $fillable = [
        'tenant_id','document_id','assignee_id','title','notes','status','due_date'
    ];
}
