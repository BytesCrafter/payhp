<?php
    namespace App\Classes;

    class Payslip {

        public $email = "";
        public $subject = "";
        public $cc = [];
        public $bcc = [];
        public $replyto = [];
        public $body = "";
        public $attachments = [];

        public function __construct(array $payslip)
        {
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
    }
