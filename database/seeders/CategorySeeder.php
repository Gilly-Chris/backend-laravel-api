<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Category::count() > 0) {
            return;
        }

        $categories = ['Business', 'Sports', 'Science', 'Health', 'Entertainment', 'Technology', 'War',
             'Government','Politics', 'Environment', 'Economy', 'Fashion'];

        foreach($categories as $type) {
            $category = new Category();
            $category->name = $type;
            $category->save();
        }
    }
}
