<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChildSubCategory;
use App\Models\SubCategoryPhotos;
use App\Models\Products;
use App\Models\Category;

class SubCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'title',
    ];

    public function subCategoryImages()
    {
        return $this->hasMany(SubCategoryPhotos::class,'sub_category_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Products::class);
    }
    
}
