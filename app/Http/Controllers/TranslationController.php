<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Language;
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
        $jsonString = mb_convert_encoding($jsonString, 'UTF-8');
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
                
                if ($value !== '') {
                    if ($section) {
                        $file[$section][$key] = $value;
                    } else {
                        $file[$key] = $value;
                    };
                } else {
                    if ($section) {
                        unset($file[$section][$key]);
                    } else {
                        unset($file[$key]);
                    };
                }
            };
        };


        // Write File
        $newJsonString = $file;
        $newJsonString = json_encode($newJsonString, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $newJsonString = mb_convert_encoding($newJsonString, 'UTF-8');
        file_put_contents(base_path($filepath), $newJsonString);

        $newJsonString = json_decode($newJsonString);

        return response()->json($newJsonString);
    }


    // GET
    public function getStrings($lang)
    {
        $filepath = 'resources/js/lang/' . $lang . '.json';

        // Read File
        $jsonString = file_get_contents(base_path($filepath));
        $jsonString = json_decode($jsonString);


        return response()->json($jsonString);
    }


    // GET
    public function getLanguages($subset)
    {
      if ($subset == 'site') {
        $languages = Language::where('site_language','=',true)->get();
      } else {
        $languages = Language::all();
      }

      return response()->json($languages);
    }


    // GET
    public function getUserLanguages(Request $request)
    {
        $user = Auth::user();

        return response()->json($user->languages);
    }


    public function setUserLanguages(Request $request)
    {
      $targetUser = User::find($request->user_id);
      $languages = $request->languages;

      $targetUser->languages()->detach();

      foreach ($languages as $lang) {
        $language = Language::find($lang);
        $targetUser->languages()->attach($language);
      }
    }

}
