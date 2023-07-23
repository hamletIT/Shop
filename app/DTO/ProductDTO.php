<?php

namespace App\DTO;

class ProductDTO
{
    public ?string $title;
    public int $sub_category_id;
    public ?string $description; 
    public ?string $photoFileName; 
    public ?string $status;
    public int $price; 
    public int $totalQty; 

}