<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category; 
use App\Models\BigStorePhotos;


class BigStores extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'name',
        'info',
        'status',
        'created_at',
        'updated_at',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class,'big_store_id');
    }

    public function bigStoreImages()
    {
        return $this->hasMany(BigStorePhotos::class,'big_store_id');
    }
    
}
