<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\TranslatorLanguages;
use Auth;
use DB;

class TranslationController extends Controller
{
    public function updateString(Request $request)
    {
        // REQUEST object should include:
        //  'section' -> top level of json array (optional if key is not nested)
        //  'key' -> nested translation key (optional. if ommited the entire section will be replaced)
        //  'string' -> translated string
        //  'lang' -> target translation language where key should be updated (ie. 'en', 'es', 'ru', etc)


        // Read in variables from request
        $section = $request->section;
        $key = $request->key;
        $string = $request->string;
        $strings = $request->strings;
        $lang = $request->lang;
        $filepath = 'resources/js/lang/' . $lang . '.json';

        // Read File
        $jsonString = file_get_contents(base_path($filepath));
        $file = json_decode($jsonString, true);


        if ($string) {
            if ($section) {
                $file[$section][$key] = $string;
            } else {
                $file[$key] = $string;
            };
        } else if ($strings) {
            $stringArr = array();
            $stringArr = json_decode($strings);

            foreach ($stringArr as $item) {
                $key = $item->key;
                $value = $item->value;

                if ($section) {
                    $file[$section][$key] = $value;
                } else {
                    $file[$key] = $value;
                };
            };
        };




        // Write File
        $newJsonString = mb_convert_encoding(json_encode($file, JSON_PRETTY_PRINT), 'UTF-8', 'UTF-8');
        file_put_contents(base_path($filepath), stripslashes($newJsonString));



        $response = [
            'data' => $file
        ];

        return response()->json($response);
    }


    // GET
    public function getLanguages(Request $request)
    {
        $user = Auth::user();

        return response()->json($user->translator_languages);
    }
}
