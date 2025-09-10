<?php
use App\Models\Setting;
use App\Models\Shop;
use App\Models\Country;
use App\Models\Product;

function favicon_url(){

    
    return '';
}

function logo_url(){

    
    // Return an empty string if domain or logo is not set
    return '';
}


function price($price = 0){
  
   $currency_sign = get_currency();
    return $currency_sign.' '.$price;
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


   function wallet($status = 'completed'){
     $walletBalance = \App\Models\Wallet::where('user_id', Auth::id())
                            ->where('status', $status)
                            ->selectRaw('SUM(credit - debit) as balance')
                            ->value('balance') ?? 0;

        return $walletBalance;
  }

  function wallet_on_hold(){
     return wallet('on_hold');
   }


  function admin_wallet($status = 'completed') {

     $walletBalance = \App\Models\Wallet::where('status', $status)
                            ->selectRaw('SUM(credit - debit) as balance')
                            ->value('balance') ?? 0;

        return $walletBalance;
  }


  function get_currency() {
    return setting('default_currency');
 }

 /**
  * Determine the currency symbol for a given shop/context, falling back to default.
  * Accepts: \App\Models\Shop instance, an object with ->shop, a shop id, or null (uses current seller).
  */
 function shop_currency($context = null) {
    try {
        // 1) Explicit Shop instance
        if ($context instanceof \App\Models\Shop) {
            return $context->currency ?: get_currency();
        }

        // 2) Any object with a loaded 'shop' relation (e.g., Order, Product)
        if (is_object($context) && isset($context->shop) && $context->shop instanceof \App\Models\Shop) {
            return $context->shop->currency ?: get_currency();
        }

        // 3) Shop id
        if (is_numeric($context)) {
            $shop = \App\Models\Shop::find((int)$context);
            if ($shop) {
                return $shop->currency ?: get_currency();
            }
        }

        // 4) Current seller's shop
        if (function_exists('auth') && auth()->check()) {
            $shop = \App\Models\Shop::where('user_id', auth()->id())->first();
            if ($shop && !empty($shop->currency)) {
                return $shop->currency;
            }
        }
    } catch (\Throwable $e) {
        // Fallback to default on any error
    }
    return get_currency();
 }

if (! function_exists('apply_discount')) {
    /**
     * Calculate the discounted price for a given product.
     *
     * @param  float  $price      Base or variant price.
     * @param  int    $productId  ID of the product.
     * @return float              Price after applying any product or deal discount.
     */
    function apply_discount(float $price, int $productId): float
    {
        $product = Product::find($productId);

        return $product ? $product->applyDiscount($price) : $price;
    }
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

if (! function_exists('product_thumb_url')) {
    /**
     * Build a product thumbnail URL with fallbacks:
     * 1) featured_image (absolute or storage path)
     * 2) first media item
     * 3) product->shop->logo
     * 4) setting('favicon_url')
     * 5) storage/placeholder.jpg
     */
    function product_thumb_url($product): string
    {
        if (! $product) {
            return setting('favicon_url') ?: asset('storage/placeholder.jpg');
        }

        $fi = $product->featured_image ?? null;
        if (!empty($fi)) {
            return str_starts_with($fi, 'http') ? $fi : asset('storage/' . ltrim($fi, '/'));
        }

        $firstMedia = method_exists($product, 'media') ? $product->media->first() : null;
        if ($firstMedia && !empty($firstMedia->url)) {
            return asset('storage/' . ltrim($firstMedia->url, '/'));
        }

        $shopLogo = ($product->shop && $product->shop->logo)
            ? asset('storage/' . ltrim($product->shop->logo, '/'))
            : null;
        if ($shopLogo) {
            return $shopLogo;
        }

        return setting('favicon_url') ?: asset('storage/placeholder.jpg');
    }
}


if (! function_exists('couriers_list')) {
    /**
     * Return a list of default couriers from settings (JSON),
     * or a sensible fallback list if not configured.
     */
    function couriers_list(): array
    {
        $default = [
            'DHL','FedEx','UPS','USPS','Royal Mail','DPD','Evri','GLS',
            'Canada Post','Australia Post','PostNL','La Poste','SEUR','Correos','Aramex','TNT',
        ];

        $raw = setting('couriers_json');
        if (!empty($raw)) {
            try {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && !empty($decoded)) {
                    // Normalize: trim strings and remove empties
                    $list = array_values(array_filter(array_map('trim', $decoded), fn($v) => $v !== ''));
                    if (!empty($list)) {
                        return $list;
                    }
                }
            } catch (\Throwable $e) {}
        }

        return $default;
    }
}


 function country_name($id){
    if (!$id) {
        return 'N/A';
    }
    $country = Country::find($id);
    return $country ? $country->name : 'N/A';
 }




    function themed_view(string $view, array $data = [])
    {
       
 $theme    = theme();             ;

        // Compose full themed view name
        $themedView = "theme."."$theme.$view";

   return view($themedView, $data);

       
    }






    function currencies(){
    return array(
        'AED' => 'United Arab Emirates dirham',
        'AFN' => 'Afghan afghani',
        'ALL' => 'Albanian lek',
        'AMD' => 'Armenian dram',
        'ANG' => 'Netherlands Antillean guilder',
        'AOA' => 'Angolan kwanza',
        'ARS' => 'Argentine peso',
        'AUD' => 'Australian dollar',
        'AWG' => 'Aruban florin',
        'AZN' => 'Azerbaijani manat',
        'BAM' => 'Bosnia and Herzegovina convertible mark',
        'BBD' => 'Barbadian dollar',
        'BDT' => 'Bangladeshi taka',
        'BGN' => 'Bulgarian lev',
        'BHD' => 'Bahraini dinar',
        'BIF' => 'Burundian franc',
        'BMD' => 'Bermudian dollar',
        'BND' => 'Brunei dollar',
        'BOB' => 'Bolivian boliviano',
        'BRL' => 'Brazilian real',
        'BSD' => 'Bahamian dollar',
        'BTC' => 'Bitcoin',
        'BTN' => 'Bhutanese ngultrum',
        'BWP' => 'Botswana pula',
        'BYR' => 'Belarusian ruble',
        'BZD' => 'Belize dollar',
        'CAD' => 'Canadian dollar',
        'CDF' => 'Congolese franc',
        'CHF' => 'Swiss franc',
        'CLP' => 'Chilean peso',
        'CNY' => 'Chinese yuan',
        'COP' => 'Colombian peso',
        'CRC' => 'Costa Rican col&oacute;n',
        'CUC' => 'Cuban convertible peso',
        'CUP' => 'Cuban peso',
        'CVE' => 'Cape Verdean escudo',
        'CZK' => 'Czech koruna',
        'DJF' => 'Djiboutian franc',
        'DKK' => 'Danish krone',
        'DOP' => 'Dominican peso',
        'DZD' => 'Algerian dinar',
        'EGP' => 'Egyptian pound',
        'ERN' => 'Eritrean nakfa',
        'ETB' => 'Ethiopian birr',
        'EUR' => 'Euro',
        'FJD' => 'Fijian dollar',
        'FKP' => 'Falkland Islands pound',
        'GBP' => 'Pound sterling',
        'GEL' => 'Georgian lari',
        'GGP' => 'Guernsey pound',
        'GHS' => 'Ghana cedi',
        'GIP' => 'Gibraltar pound',
        'GMD' => 'Gambian dalasi',
        'GNF' => 'Guinean franc',
        'GTQ' => 'Guatemalan quetzal',
        'GYD' => 'Guyanese dollar',
        'HKD' => 'Hong Kong dollar',
        'HNL' => 'Honduran lempira',
        'HRK' => 'Croatian kuna',
        'HTG' => 'Haitian gourde',
        'HUF' => 'Hungarian forint',
        'IDR' => 'Indonesian rupiah',
        'ILS' => 'Israeli new shekel',
        'IMP' => 'Manx pound',
        'INR' => 'Indian rupee',
        'IQD' => 'Iraqi dinar',
        'IRR' => 'Iranian rial',
        'ISK' => 'Icelandic kr&oacute;na',
        'JEP' => 'Jersey pound',
        'JMD' => 'Jamaican dollar',
        'JOD' => 'Jordanian dinar',
        'JPY' => 'Japanese yen',
        'KES' => 'Kenyan shilling',
        'KGS' => 'Kyrgyzstani som',
        'KHR' => 'Cambodian riel',
        'KMF' => 'Comorian franc',
        'KPW' => 'North Korean won',
        'KRW' => 'South Korean won',
        'KWD' => 'Kuwaiti dinar',
        'KYD' => 'Cayman Islands dollar',
        'KZT' => 'Kazakhstani tenge',
        'LAK' => 'Lao kip',
        'LBP' => 'Lebanese pound',
        'LKR' => 'Sri Lankan rupee',
        'LRD' => 'Liberian dollar',
        'LSL' => 'Lesotho loti',
        'LYD' => 'Libyan dinar',
        'MAD' => 'Moroccan dirham',
        'MDL' => 'Moldovan leu',
        'MGA' => 'Malagasy ariary',
        'MKD' => 'Macedonian denar',
        'MMK' => 'Burmese kyat',
        'MNT' => 'Mongolian t&ouml;gr&ouml;g',
        'MOP' => 'Macanese pataca',
        'MRO' => 'Mauritanian ouguiya',
        'MUR' => 'Mauritian rupee',
        'MVR' => 'Maldivian rufiyaa',
        'MWK' => 'Malawian kwacha',
        'MXN' => 'Mexican peso',
        'MYR' => 'Malaysian ringgit',
        'MZN' => 'Mozambican metical',
        'NAD' => 'Namibian dollar',
        'NGN' => 'Nigerian naira',
        'NIO' => 'Nicaraguan c&oacute;rdoba',
        'NOK' => 'Norwegian krone',
        'NPR' => 'Nepalese rupee',
        'NZD' => 'New Zealand dollar',
        'OMR' => 'Omani rial',
        'PAB' => 'Panamanian balboa',
        'PEN' => 'Peruvian nuevo sol',
        'PGK' => 'Papua New Guinean kina',
        'PHP' => 'Philippine peso',
        'PKR' => 'Pakistani rupee',
        'PLN' => 'Polish z&#x142;oty',
        'PRB' => 'Transnistrian ruble',
        'PYG' => 'Paraguayan guaran&iacute;',
        'QAR' => 'Qatari riyal',
        'RON' => 'Romanian leu',
        'RSD' => 'Serbian dinar',
        'RUB' => 'Russian ruble',
        'RWF' => 'Rwandan franc',
        'SAR' => 'Saudi riyal',
        'SBD' => 'Solomon Islands dollar',
        'SCR' => 'Seychellois rupee',
        'SDG' => 'Sudanese pound',
        'SEK' => 'Swedish krona',
        'SGD' => 'Singapore dollar',
        'SHP' => 'Saint Helena pound',
        'SLL' => 'Sierra Leonean leone',
        'SOS' => 'Somali shilling',
        'SRD' => 'Surinamese dollar',
        'SSP' => 'South Sudanese pound',
        'STD' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra',
        'SYP' => 'Syrian pound',
        'SZL' => 'Swazi lilangeni',
        'THB' => 'Thai baht',
        'TJS' => 'Tajikistani somoni',
        'TMT' => 'Turkmenistan manat',
        'TND' => 'Tunisian dinar',
        'TOP' => 'Tongan pa&#x2bb;anga',
        'TRY' => 'Turkish lira',
        'TTD' => 'Trinidad and Tobago dollar',
        'TWD' => 'New Taiwan dollar',
        'TZS' => 'Tanzanian shilling',
        'UAH' => 'Ukrainian hryvnia',
        'UGX' => 'Ugandan shilling',
        'USD' => 'United States dollar',
        'UYU' => 'Uruguayan peso',
        'UZS' => 'Uzbekistani som',
        'VEF' => 'Venezuelan bol&iacute;var',
        'VND' => 'Vietnamese &#x111;&#x1ed3;ng',
        'VUV' => 'Vanuatu vatu',
        'WST' => 'Samoan t&#x101;l&#x101;',
        'XAF' => 'Central African CFA franc',
        'XCD' => 'East Caribbean dollar',
        'XOF' => 'West African CFA franc',
        'XPF' => 'CFP franc',
        'YER' => 'Yemeni rial',
        'ZAR' => 'South African rand',
        'ZMW' => 'Zambian kwacha',
    );

}


?>
