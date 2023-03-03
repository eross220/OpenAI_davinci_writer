<?php

namespace App\Services;

class HelperService 
{
    public static function getTotalWords()
    {   
        $value = number_format(auth()->user()->available_words + auth()->user()->available_words_prepaid);
        return $value;
    }

    public static function getTotalImages()
    {   
        $value = number_format(auth()->user()->available_images + auth()->user()->available_images_prepaid);
        return $value;
    }
}