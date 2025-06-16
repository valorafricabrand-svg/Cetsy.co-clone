<?php

namespace App\Services\Shared;


class GetShippingService
{


    public function handle()
    {


        $shippingData = [
            [
                'id' => 0,
                'name' => 'Other',
            ],
            [
                'id' => 1,
                'name' => "DHL",

            ],
            [
                'id' => 2,
                'name' => "Aramex",

            ],

            [
                'id' => 3,
                'name' => "Deliver Personally",

            ],
          
            ];
       

            return $shippingData;
    }
}
