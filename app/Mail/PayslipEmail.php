<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Classes\Payslip;

class PayslipEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public $company_name = "";
    public $company_site = "";

    public Payslip $payslip;
    public function setPayslip(Payslip $payslip, $company_name = "ABC Company Inc", $company_site = "http://bytescrafter.net") {
        $this->payslip = $payslip;
        $this->company_name = $company_name;
        $this->company_site = $company_site;
        return $this;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $viewJob = $this->view('payslip');
        for($i=0; $i<count($this->payslip->attachments); $i++) {
            $viewJob->attach($this->payslip->attachments[$i], [
                'as' => count($this->payslip->attachments)>1?'file-'.$i.'.pdf':'payslip.pdf',
                'mime' => 'application/pdf',
            ]);
        }
        return $viewJob;
    }
}
