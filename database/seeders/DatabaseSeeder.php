<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\User;
use App\Models\Prices;
use App\Models\Addresses;
use App\Models\Products;
use App\Models\category_subcategory;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use AuthorizesRequests, ValidatesRequests;
   
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'status' => '1',
            'phone' => '+37477777777',
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('1234567890'),
            'two_factor_secret' => 'admin',
        ]);
        $user->createToken('Token Name')->accessToken;

        $bigStore = DB::table('big_stores')->insertGetId([
            'user_id' => $user->id,
            'name' => 'test big store',
            'info' => 'test big store info',
            'status' => '001',
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $category = DB::table('Categories')->insertGetId([
            'big_store_id' => $bigStore,
            'title' => 'test category',
            'status' => '001',
            'photoFileName' => 'null',
            'photoFilePath' => 'null',
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $subCategoery = DB::table('sub_categories')->insertGetId([
            'title' => 'test sub category',
            'status' => '001',
            'photoFileName' => 'null',
            'photoFilePath' => 'null',
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $product = Products::create([
            'sub_category_id' => $subCategoery,
            'title' => 'test product',
            'description' => 'tets product description',
            'photoFileName' => 'null',
            'photoFilePath' => 'null',
            'status' =>'yes',
            'totalQty' => '1',
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        Prices::insertGetId([
            'product_id' => $product->id,
            'title' => $product->title,
            'productPrice' => '10',
            'status' => '0',
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        category_subCategory::create([
            'category_id' => $category,
            'sub_category_id' => $subCategoery,
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        
    }
}
