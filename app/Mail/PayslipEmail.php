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

    public Payslip $payslip;

    public function setPayslip(Payslip $payslip) {
        $this->payslip = $payslip;
        return $this;
    }

    /**
    * Create a new message instance.
    *
    * @return void
    */
    public function __construct()
    {
        //
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
                'as' => 'payslip.pdf',
                'mime' => 'application/pdf',
            ]);
        }
        return $viewJob;
    }
}
