<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','route','icon','sort','category_id','is_active'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
