<?php
use App\Models\Setting;
use App\Models\Shop;
use App\Models\Country;

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


function shop_id(){

    $shop = Shop::whereUserId(Auth::id())->first();
    return $shop->id;
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


   function wallet(){
     $walletBalance = \App\Models\Wallet::where('user_id', Auth::id())
                            ->selectRaw('SUM(credit - debit) as balance')
                            ->value('balance') ?? 0;

        return $walletBalance;
  }


  function get_currency() {
    return 'USD';
 }


   if (! function_exists('setting')) {
    /**
     * Retrieve a setting value by key (column name), or return default.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        static $settings;

        // cache the row so we only hit the DB once
        if (! $settings) {
            $settings = Setting::first();
        }

        return $settings && isset($settings->$key)
            ? $settings->$key
            : $default;
    }
}

if (! function_exists('theme')) {
    /**
     * Shortcut for 'theme' setting.
     */
    function theme(): string
    {
        return setting('theme', 'cetsy');
    }
}


 function country_name($id){
    $country = Country::find($id);
    return $country->name;
 }




    function themed_view(string $view, array $data = [])
    {
       
 $theme    = theme();             ;

        // Compose full themed view name
        $themedView = "theme."."$theme.$view";

   return view($themedView, $data);

       
    }


?>