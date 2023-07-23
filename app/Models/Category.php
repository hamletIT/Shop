<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubCategory; 
use App\Models\Products;
use App\Models\BigStores;
use App\Models\CategoryPhotos;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'title',
    ];

    public function bigStore()
    {
        return $this->belongsTo(BigStores::class);
    }
    public function subCategories() 
    {
        return $this->belongsToMany(SubCategory::class, 'category_sub_categories', 'category_id', 'sub_category_id');
    }

    public function categoryImages()
    {
        return $this->hasMany(CategoryPhotos::class,'category_id');
    }
    
}
