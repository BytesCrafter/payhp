<?php

    namespace App\Libraries;

    class PayHP {

        public $monthly_allowance = 0.00;
        public $hourly_nightdiff = 0.00;
        public $incentives = 0.00;
        public $hourly_regholiday = 0.00;
        public $hourly_speholiday = 0.00;

        public $monthly_rate = 0.00;
        public $deducted_hours = 0.00;

        public $regular_ot = 0.00;
        public $restday_ot = 0.00;
        public $reg_holiday_ot = 0.00;
        public $spe_holiday_ot = 0.00;

        public $additionals = [];
        public $deductions = [];

        public function __contructor()
        {
            //initialized middleware, models, libraries, etc.
        }

        public function add_regular_ot($amount) {
            $this->regular_ot += round($amount, 2);
        }

        public function add_restday_ot($amount) {
            $this->restday_ot += round($amount, 2);
        }

        public function add_reg_holiday_ot($amount) {
            $this->reg_holiday_ot += round($amount, 2);
        }

        public function add_spe_holiday_ot($amount) {
            $this->spe_holiday_ot += round($amount, 2);
        }

        public function add_hourly_speholiday($amount) {
            $this->hourly_speholiday += round($amount, 2);
        }

        public function add_hourly_regholiday($amount) {
            $this->hourly_regholiday += round($amount, 2);
        }

        public function add_incentives($amount) {
            $this->incentives += round($amount, 2);
        }

        public function add_hourly_nightdiff($amount) {
            $this->hourly_nightdiff += round($amount, 2);
        }

        public function add_additional($name, $amount) {
            $this->additionals[] = array(
                "name" => $name,
                "amount" => $amount
            );
            return $this;
        }

        public function total_additional() {
            $total = 0;
            foreach($this->additionals as $add) {
                $total += $add["amount"];
            }
            return round($total, 2);
        }

        public function basic_rate() {
            $basic_rate = $this->basic_pay() - $this->hourly_deductions();
            return round($basic_rate, 2);
        }

        public function add_monthly_allowance($amount) {
            $this->monthly_allowance = round($amount, 2);
        }

        public function hourly_deductions() {
            $hourly_deducted_amt = $this->deducted_hours*$this->hourly_rate();
            return round($hourly_deducted_amt, 2);
        }

        public function add_hourly_deductions($amount) {
            $this->deducted_hours = round($amount, 2);
        }

        public function basic_pay() {
            $biweekly_rate = $this->monthly_rate/2;
            return round($biweekly_rate, 2);
        }

        public function hourly_rate() {
            $hourly_rate = (($this->monthly_rate*12)/261)/8;
            return round($hourly_rate, 2);
        }

        public function add_basic_rate($monthly) {
            $this->monthly_rate = round($monthly, 2);
        }

        public function add_deduction($name, $amount) {
            $this->deductions[] = array(
                "name" => $name,
                "amount" => $amount
            );
            return $this;
        }

        public function total_deductions() {
            $total = 0;
            foreach($this->deductions as $deduct) {
                $total += $deduct["amount"];
            }
            return round($total, 2);
        }
    }
