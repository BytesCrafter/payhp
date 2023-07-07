<?php
    namespace App\Traits;
    use App\Models\User;
    use Storage;

    trait CalcTrait {

        public function decimal_format($value) {
            $number = str_replace(' ', '', $value);
            $amount = str_replace(',', '', $number);

            if( is_numeric($amount) ) {
                return round($amount, 2);
            }

            return 0;
        }
    }
