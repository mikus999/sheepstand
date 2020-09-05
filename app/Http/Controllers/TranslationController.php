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
        //  'key' -> nested translation key
        //  'string' -> translated string
        //  'lang' -> target translation language where key should be updated (ie. 'en', 'es', 'ru', etc)


        // Read in variables from request
        $section = $request->section;
        $key = $request->key;
        $string = $request->string;
        $lang = $request->lang;
        $filepath = 'resources/js/lang/' . $lang . '.json';

        // Read File
        $jsonString = file_get_contents(base_path($filepath));
        $data = json_decode($jsonString, true);


        // Update key
        if ($section) {
            $data[$section][$key] = $string;
        } else {
            $data[$key] = $string;
        };
        

        // Write File
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents(base_path($filepath), stripslashes($newJsonString));


        $response = [
            'data' => $data
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
