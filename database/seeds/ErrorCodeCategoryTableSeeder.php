<?php

use Illuminate\Database\Seeder;
use App\Models\ErrorCodeCategory;

class ErrorCodeCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (ErrorCodeCategory::getCategoryBases() as $category => $base) {
            ErrorCodeCategory::create([
                'id' => ($base / 1000),
                'name' => $category
            ]);
        }
    }
}
