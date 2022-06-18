<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\PayslipEmail;
use App\Mail\HelloEmail;
use App\Classes\Payslip;
use Mail;

class PayslipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $company_name = "";
    protected $company_site = "";
    protected Payslip $payslip;

    public function setPayslipData(Payslip $payslip, $company_name = "ABC Company Inc", $company_site = "http://bytescrafter.net") {
        $this->payslip = $payslip;
        $this->company_name = $company_name;
        $this->company_site = $company_site;
        return $this;
    }

    /**
    * Create a new job instance.
    *
    * @return void
    */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new PayslipEmail();
        $email->setPayslip($this->payslip, $this->company_name, $this->company_site);

        for($i=0; $i<count($this->payslip->cc); $i++) {
            $email->cc( $this->payslip->cc[$i] );
        }

        for($i=0; $i<count($this->payslip->bcc); $i++) {
            $email->bcc( $this->payslip->bcc[$i] );
        }

        for($i=0; $i<count($this->payslip->replyto); $i++) {
            $email->replyTo( $this->payslip->replyto[$i] );
        }

        Mail::to($this->payslip->email)->queue($email); //send
    }
}
