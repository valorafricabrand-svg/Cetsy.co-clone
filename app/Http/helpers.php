<?php
use App\Models\Setting;

function favicon_url(){

    
    return '';
}

function logo_url(){

    
    // Return an empty string if domain or logo is not set
    return '';
}


function settings() {
    $domain = request()->getHost();

    // Remove 'www.' from the beginning if present
    if (strpos($domain, 'www.') === 0) {
        $domain = substr($domain, 4);
    }

    $setting = Setting::where('domain_name', $domain)->first();

    return $setting;
}


function shop(){
    return 'Cetsy';
}

    function avatar_img_url( $source, $img = null){
      $url_path = '';
      if ($img){
        if ($source == 'public'){
          $url_path = asset('storage/uploads/photos/'.$img);
        }elseif ($source == 's3'){
          $url_path = \Illuminate\Support\Facades\Storage::disk('s3')->url('uploads/avatar/'.$img);
        }
      }
      return $url_path;
    }

    function get_option($option_key = ''){
      $get = \App\Models\Setting::where('option_key', $option_key)->first();
      if($get) {
          return $get->option_value;
      }
      return $option_key;
  }



?>