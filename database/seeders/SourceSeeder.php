<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Source;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Source::count() > 0) {
            return;
        }

        $sources = ['The Guardian', 'New York Times', 'News API'];

        foreach($sources as $type) {
            $source = new Source();
            $source->name = $type;
            $source->save();
        }
    }
}
