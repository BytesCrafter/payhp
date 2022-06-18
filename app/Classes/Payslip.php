<?php
    namespace App\Classes;

    class Payslip {

        public $fullname = "";
        public $payriod = "";

        public $email = "";
        public $subject = "";
        public $cc = [];
        public $bcc = [];
        public $replyto = [];
        public $body = "";
        public $attachments = [];

        public function __construct(array $payslip = null)
        {
            if($payslip == null)
                return;

            $this->fullname = isset($payslip["fullname"])?$payslip["fullname"]:"";
            $this->payriod = isset($payslip["payriod"])?$payslip["payriod"]:"";

            $this->email = isset($payslip["email"])?$payslip["email"]:"";
            $this->subject = isset($payslip["subject"])?$payslip["subject"]:"";
            $this->cc = isset($payslip["cc"])?$payslip["cc"]:"";
            $this->bcc = isset($payslip["bcc"])?$payslip["bcc"]:"";
            $this->replyto = isset($payslip["replyto"])?$payslip["replyto"]:"";
            $this->body = isset($payslip["body"])?$payslip["body"]:"";
            $this->attachments = isset($payslip["attachments"])?$payslip["attachments"]:"";
        }

        public function toArray() {
            return $this->current->toArray();
        }

        public function store(array $payslip) {
            return false; //Save to database
        }
    }
