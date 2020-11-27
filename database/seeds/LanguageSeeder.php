<?php

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $data = base_path('resources/languages.json');
      $data = json_decode(file_get_contents($data), true);

      foreach ($data as $language) {
          Language::create([
              'code'        => $language['code'],
              'name'        => $language['name'],
              'native_name' => $language['nativeName'],
          ]);
      }

    }
}
