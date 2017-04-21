<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Hideyo\Ecommerce\Backend\Models\Shop as Shop;

class ShopTableSeeder extends Seeder
{
    public function run()
    {
        $shop = new Shop;

        DB::table($shop->getTable())->delete();

        $shop->title = 'hideyo';
        $shop->url = 'http://hideyo.dev';
        $shop->secret_key = 'RQlqvrjpGWPVqY1gPerKstoO4X6xhGGE';
        $shop->active = 1;
        $shop->description = "description";
        $shop->currency_code = "EUR";
        $shop->thumbnail_widescreen_sizes = "100x100";
        $shop->thumbnail_square_sizes = "100x100";        
        
        if (! $shop->save()) {
            Log::info('Unable to create shop '.$shop->title, (array)$shop->errors());
        } else {
            Log::info('Created shop "'.$shop->title);
        }
    }
}
