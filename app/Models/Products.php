<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;
use App\Models\Carts;
use App\Models\Stores;
use App\Models\Photos;
use App\Models\Prices;
use App\Models\Options;
use App\Models\Category;
use App\Models\SubCategory;

class Products extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'photoFileName',
        'photoFilePath',
        'status',
        'totalQty',
        'created_at',
        'updated_at',
    ];

    public function cart()
    {
        return $this->hasOne(Carts::class);
    }
    
    public function productImages()
    {
        return $this->hasMany(Photos::class,'product_id');
    }

    public function productPrice()
    {
        return $this->hasMany(Prices::class,'product_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function subCategories() 
    {
        return $this->belongsToMany(SubCategory::class, 'pivot_sub_categories_products', 'product_id', 'sub_category_id');
    }
}