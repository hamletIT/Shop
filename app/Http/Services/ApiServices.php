<?php

namespace App\Http\Services;

use App\Models\Carts;
use App\Models\Products;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildSubCategory;
use App\Models\User;

class ApiServices
{
    /**
     * Comment => This function returns the standard project schema
     */
    public function StructureOfTheStandardSchema()
    {
        return [
            'bigStoreImages',
            'user',
            'categories',
            'categories.categoryImages',
            'categories.subCategories',
            'categories.subCategories.subCategoryImages',
            'categories.subCategories.products',
            'categories.subCategories.products.productPrice',
            'categories.subCategories.products.productImages',
        ];
    }

    /**
     * Comment => The function returns categories with photos
     */
    public function categoryAndImages()
    {
        return Category::with(['categoryImages','bigStore'])->get();
    }

    /**
     * Comment => The function returns categories with photos
     */
    public function subCategoryAndImages()
    {
        return SubCategory::with(['subCategoryImages'])->get();
    }

    /**
     * Comment => This function returns the standard product schema
     */
    public function productsSchema()
    {
        return [
            'productPrice','productImages'
        ];
    }

    /**
     * Comment => This function returns all products from the cart, the price of a specific product and the price of all products
     * @param ?int $userID
     */
    public function getCart($userID)
    {
        $cart = Carts::where('user_id', $userID)->with('product')->get()->groupBy('random_number');
        $randPrices = [];
        $product = []; // Initialize the $product array here

        foreach ($cart as $items) {
            $productTable = Products::with('productPrice')->where('id', $items[0]->product_id)->first();
            $prod_price = $productTable->productPrice[0]->productPrice * $items[0]->totalQty;
            $randPrice = 0;

           
            $randPrices['product_id: ' . $items[0]->product_id] = $randPrice + $prod_price;
            $product[$items[0]->product_id] = $items[0];
        }

        return ['products' => $product, 'product_prices' => $randPrices, 'Total_price:' => array_sum($randPrices)];
    }

    /**
     * Comment =>
     * @param ?int $userID
     */
    public function isAdministrator($userID)
    {
        $user = User::where('id',$userID)->first();

        if($user->two_factor_secret == 'admin'){
            return true;
        }else{
            return false;
        }
    }
}