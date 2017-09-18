<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Facade;

class BridesController extends Facade {

   protected static function getFacadeAccessor() {
       //what you want
       return 'getPhotographer';
   }

   public static function getPhotographer() {
       return "photographer";
   }

}