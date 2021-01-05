<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Language;
use Auth;
use DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class TranslationController extends Controller
{
    public function updateString(Request $request)
    {
      $user = Auth::user();
      
      if ($user->hasRole('translator', null) || $user->hasRole('super_admin', null)) {
        
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

        if (!file_exists(base_path($filepath))) {
          file_put_contents(base_path($filepath), '{}');
        }

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
        } else {
          return RB::error(400); // Bad request, missing strings
        }


        // Write File
        $newJsonString = $file;
        $newJsonString = json_encode($newJsonString, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $newJsonString = mb_convert_encoding($newJsonString, 'UTF-8');
        file_put_contents(base_path($filepath), $newJsonString);

        $newJsonString = json_decode($newJsonString);

        return RB::success(['strings' => $newJsonString]);

      } else {
        return RB::error(403); // access denied
      }
    }


    // GET
    public function getStrings($lang)
    {
        $filepath = 'resources/js/lang/' . $lang . '.json';

        // Read File
        if (file_exists(base_path($filepath))) {
          $jsonString = file_get_contents(base_path($filepath));
          $jsonString = json_decode($jsonString);
        } else {
          $jsonString = null;
        }

        return RB::success(['strings' => $jsonString]);
    }


    // GET
    public function getLanguages($subset)
    {
      if ($subset == 'site') {
        $languages = Language::where('site_language','=',true)->get();
      } else if ($subset == 'all') {
        $languages = Language::all();
      }

      return response()->json($languages);
    }


    // GET
    public function getUserLanguages(Request $request)
    {
      $user = Auth::user();
      if ($user->hasRole('translator', null)) {
        return RB::success(['languages' => $user->languages]);
      } else {
        return RB::error(403); // access denied
      }
    }


    public function setUserLanguages(Request $request)
    {
      $user = Auth::user();
      $targetUser = User::find($request->user_id);
      $languages = $request->languages;

      if (!$targetUser) return RB::error(404); // target user not found

      if ($user->hasRole('super_admin', null)) {
        $targetUser->languages()->detach();

        foreach ($languages as $lang) {
          $language = Language::find($lang);
          $targetUser->languages()->attach($language);
        }

        return RB::success(['languages' => $targetUser->languages]);

      } else {
        return RB::error(403); // access denied
      }
    }

    public function setSiteLanguage(Request $request)
    {
      $user = Auth::user();
      $language = $request->language;
      $changetype = $request->changetype;
      $site_lang = $changetype == 'add' ? 1 : 0;

      
      if ($user->hasRole('super_admin', null)) {
        $lang = Language::where('code','=',$language)->first();
        if (!$lang) return RB::error(404); // language not found

        $lang->site_language = $site_lang;
        $lang->save();

        return RB::success(['languages' => Language::all()]);

      } else {
        return RB::error(403); // access denied
      }
    }

}
