<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Classes\Payslip;
use App\Libraries\PayHP;
use Ramsey\Uuid\Uuid;
use App\Jobs\PayslipJob;
use App\Libraries\AmountWords;
use App\Traits\UserTrait;
use Illuminate\Support\Facades\Auth;
USE App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\UserController;

class ExcelController extends Controller
{
    use UserTrait;

    protected $curUser = null;
    public function __construct() {
        //$this->middleware('user.permit');
        $this->curUser = auth()->user();
    }

    function index(Request $request) {
        if( !auth()->check() ) {
            return redirect('signin');
        }

        $this->curUser = auth()->user();
		if( $this->user_has_permission($this->curUser->id, 'can_use_payhp') !== TRUE ) {
            (new UserController())->logout();
            return redirect('signin');
        }

        return view('home')
            ->with('curUser',  Auth::user())
            ->with('test',  'hello world');
    }

    function downloadMaster() {
        $master = storage_path('system/master.xlsx');
        return response()->download($master);
    }

    protected function createDir($subdir) {
        $fulldir = storage_path($subdir.'/');
        if (!file_exists($fulldir)) {
            mkdir($fulldir, 0700, true);
        }
        return $fulldir;
    }

    protected function write_payslip($paydata) {
        $payslip = new PayHP();

            $data = array();

            //Add basic rates
            $payslip->add_basic_rate( $paydata['monthly'] ); //INPUT
            $data["hourly_rate"] = $payslip->hourly_rate();
            $data["basic_salary"] = $payslip->monthly_rate;

            //Add employee allowance
            $payslip->add_monthly_allowance($paydata['allowance']); //INPUT
            $payslip->add_incentives($paydata['incentive']); //INPUT
            $payslip->add_hourly_nightdiff($paydata['nightdiff']); //INPUT
            $payslip->add_hourly_regholiday($paydata['reghdpay']); //INPUT
            $payslip->add_hourly_speholiday($paydata['spchdpay']); //INPUT
            $payslip->add_regular_ot($paydata['regot']); //INPUT
            $payslip->add_restday_ot($paydata['rstot']); //INPUT
            $payslip->add_reg_holiday_ot($paydata['reghdot']); //INPUT
            $payslip->add_spe_holiday_ot($paydata['spchdot']); //INPUT

            $data["unworked_hours"] = "";
            if((int)$paydata['deduction'] > 0) {
                $data["unworked_hours"] = number_format(($paydata['deduction']*$payslip->hourly_rate()), 2)." (".number_format($paydata['deduction'], 2)."hrs)";
            }

            //Add absent per hours.
            $payslip->add_hourly_deductions($paydata['deduction']); //INPUT
            //$data["deduct_pay"] = $payslip->hourly_deductions();
            $data["basic_rate"] = $payslip->basic_rate();

            //Compute all additionals.
            $payslip->add_additional("allowance", $payslip->monthly_allowance)///2)
                ->add_additional("hourlynd", $payslip->hourly_nightdiff)//*($payslip->hourly_rate()*0.1))
                ->add_additional("regularhd", $payslip->hourly_rate()*$payslip->hourly_regholiday)
                ->add_additional("specialhd", $payslip->hourly_rate()*$payslip->hourly_speholiday*0.3)
                ->add_additional("incentive", $payslip->incentives)
                ->add_additional("regular_ot", $payslip->hourly_rate()*$payslip->regular_ot*1.25)
                ->add_additional("restday_ot", $payslip->hourly_rate()*$payslip->restday_ot*1.30)
                ->add_additional("reg_holiday_ot", $payslip->hourly_rate()*$payslip->reg_holiday_ot*2.60)
                ->add_additional("sce_holiday_ot", $payslip->hourly_rate()*$payslip->spe_holiday_ot*1.69);
            //$data["additional"] = round($payslip->total_additional(), 2);

            $data["allowance"] = $payslip->monthly_allowance;//2;
            $data["hourlynd"] = $payslip->hourly_nightdiff;//*($payslip->hourly_rate()*0.1);
            $data["regularhd"] = $payslip->hourly_rate()*$payslip->hourly_regholiday;
            $data["specialhd"] = $payslip->hourly_rate()*$payslip->hourly_speholiday*0.3;
            $data["overtime"] = ($payslip->hourly_rate()*$payslip->regular_ot*1.25) + ($payslip->hourly_rate()*$payslip->restday_ot*1.30) + ($payslip->hourly_rate()*$payslip->reg_holiday_ot*2.60) + ($payslip->hourly_rate()*$payslip->spe_holiday_ot*1.69);
            $data["others"] = $payslip->incentives;

            $data["gross_pay"]  = round($payslip->basic_rate()+$payslip->total_additional(), 2);

            //Compute all deductions.
            $payslip->add_deduction("tax", $paydata['tax']) //INPUT
                ->add_deduction("sss", $paydata['sss']) //INPUT
                ->add_deduction("phealth", $paydata['phealth']) //INPUT
                ->add_deduction("pagibig", $paydata['pagibig']) //INPUT
                ->add_deduction("hmo", $paydata['hmo']) //INPUT
                ->add_deduction("sss_amt", $paydata['sss_amt']) //INPUT
                ->add_deduction("hdmf_amt", $paydata['hdmf_amt']) //INPUT
                ->add_deduction("other", $paydata['other']); //INPUT
            $data["total_deductions"] = round($payslip->total_deductions(), 2);

            $data["sss_loan"] = (int)$paydata['sss_amt'] > 0?"(".$paydata['sss_term'].") ".number_format($paydata['sss_amt'], 2):"0.00";
            $data["hdmf_loan"] = (int)$paydata['hdmf_amt'] > 0?"(".$paydata['hdmf_term'].") ".number_format($paydata['hdmf_amt'], 2):"0.00";

            $data["net_pay"] = round($data["gross_pay"] - $data["total_deductions"], 2);

        return $data;
    }

    protected function save_payslip($data, $paydate, $payriod, $dirpath) {

        $compute = $this->write_payslip($data);

        $template_spreadsheet   = IOFactory::load(storage_path('system/template.xls'));
        $template_activesheet   = $template_spreadsheet->getActiveSheet(); //use for edit. other page.

        $template_activesheet->setCellValue('E5', "Pay Period ".$payriod);
        $template_activesheet->setCellValue('E7', $data['fullname']);
        $template_activesheet->setCellValue('E8', $compute['basic_salary']);
        $template_activesheet->setCellValue('E9', $data['leave_credit']);

        $template_activesheet->setCellValue('H7', $data['title']);
        $template_activesheet->setCellValue('H8', $data['department']);
        $template_activesheet->setCellValue('H9', $compute['unworked_hours']);

        $template_activesheet->setCellValue('F13', $compute['basic_rate']);
        $template_activesheet->setCellValue('F14', $compute['allowance']);
        $template_activesheet->setCellValue('F15', $compute['hourlynd']);
        $template_activesheet->setCellValue('F16', $compute['regularhd']);
        $template_activesheet->setCellValue('F17', $compute['specialhd']);
        $template_activesheet->setCellValue('F18', $compute['overtime']);
        $template_activesheet->setCellValue('F19', $compute['others']);

        $template_activesheet->setCellValue('H20', number_format($data['tax']), 2);
        $template_activesheet->setCellValue('H21', number_format($data['sss']), 2);
        $template_activesheet->setCellValue('H22', number_format($data['phealth']), 2);
        $template_activesheet->setCellValue('H23', number_format($data['pagibig']), 2);
        $template_activesheet->setCellValue('H24', number_format($data['hmo']), 2);

        $template_activesheet->setCellValue('H25', $compute['sss_loan']);
        $template_activesheet->setCellValue('H26', $compute['hdmf_loan']);
        $template_activesheet->setCellValue('H27', number_format($data['other']), 2);

        $template_activesheet->setCellValue('F29', $compute['gross_pay']);
        $template_activesheet->setCellValue('H29', $compute['total_deductions']);
        $template_activesheet->setCellValue('F31', $compute['net_pay']);
        $template_activesheet->setCellValue('F32', (new AmountWords())->convertNumber($compute["net_pay"]));

        $template_activesheet->setCellValue('E30', $paydate);
        $template_activesheet->setCellValue('E31', $data['bankname']);
        $template_activesheet->setCellValue('E32', $data['fullname']);
        $template_activesheet->setCellValue('E33', $data['banknum']);

        $template_activesheet->setCellValue('C3', $data['payinc']);
        $template_activesheet->setCellValue('F35', $data['payman']);

        // $template_spreadsheet->getDefaultStyle()->applyFromArray(
        //     [
        //         'borders' => [
        //             'allBorders' => [
        //                 'borderStyle' => Border::BORDER_THIN,
        //                 'color' => ['rgb' => '000000'],
        //             ],
        //         ]
        //     ]
        // );

        $template_activesheet->getPageMargins()->setTop(0.5);
        $template_activesheet->getPageMargins()->setRight(0.5);
        $template_activesheet->getPageMargins()->setLeft(0.5);
        $template_activesheet->getPageMargins()->setBottom(0.5);

        $xmlWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($template_spreadsheet,'Mpdf');
        $xmlWriter->writeAllSheets();
        //$xmlWriter->setFooter("AHM Outsourcing Inc");
        $username = substr($data['email'], 0, strpos($data['email'], "@"));
        $xmlWriter->save( $dirpath.$username.'.pdf' );
        return $username;
    }

    function bulkGenerate(Request $request){

        $this->validate($request, [
            'paydate' => 'required|max:50',
            'payriod' => 'required|max:50',
            'payinc' => 'required|max:100',
            'payman' => 'required|max:100',
            'master' => 'required|file|mimes:xls,xlsx'
        ]);

        $paydate = $request->input('paydate');
        $payriod = $request->input('payriod');
        $payinc = $request->input('payinc');
        $payman = $request->input('payman');
        $excel = $request->file('master');

        try {
            $spreadsheet    = IOFactory::load($excel->getRealPath());
            $activesheet    = $spreadsheet->getActiveSheet();
            $datasheet      = $activesheet->toArray();

            $counter       = 1;
            $startRow       = 5;

            $group_id = Uuid::uuid4()->toString();
            $dirpath = $this->createDir('app/payroll/'.$group_id);

            $zip_name = "Payroll - ".$payriod;
            $zip_filepath = $dirpath.$zip_name.'.zip';
            $zipArchieve = new \ZipArchive();
            $zipArchieve->open($zip_filepath, \ZipArchive::CREATE || \ZipArchive::OVERWRITE);

            foreach($datasheet as $sheet) {
                if($counter >= $startRow && $sheet[0] == 'x') {

                    $current = array(
                        'enable' => $sheet[0],
                        'fullname' => $sheet[1],
                        'email' => $sheet[2],

                        'title' => $sheet[3],
                        'department' => $sheet[4],
                        'directorate' => $sheet[5],

                        'bankname' => $sheet[6],
                        'banknum' => $sheet[7],

                        'monthly' => $sheet[8],
                        'allowance' => $sheet[9],
                        'deduction' => $sheet[10],

                        'incentive' => $sheet[11],
                        'nightdiff' => $sheet[12],
                        'reghdpay' => $sheet[13],
                        'spchdpay' => $sheet[14],
                        'regot' => $sheet[15],
                        'rstot' => $sheet[16],
                        'reghdot' => $sheet[17],
                        'spchdot' => $sheet[18],

                        'tax' => $sheet[19],
                        'sss' => $sheet[20],
                        'phealth' => $sheet[21],
                        'pagibig' => $sheet[22],
                        'hmo' => $sheet[23],
                        'other' => $sheet[24],

                        'sss_amt' => $sheet[25],
                        'sss_term' => $sheet[26],
                        'hdmf_amt' => $sheet[27],
                        'hdmf_term' => $sheet[28],

                        'leave_credit' => $sheet[29],

                        'payinc' => $payinc,
                        'payman' => $payman
                    );

                    //Save to model.
                    $payslip = new Payslip();
                    $insertid = $payslip->store([
                        'uuid' => Uuid::uuid4()->toString(),
                        'group_id' => $group_id,

                        'fullname' => $sheet[1],
                        'email' => $sheet[2],

                        'payriod' => $payriod,
                        'paydate' => $paydate,

                        'title' => $sheet[3],
                        'department' => $sheet[4],
                        'directorate' => $sheet[5],

                        'bankname' => $sheet[6],
                        'banknum' => $sheet[7],

                        'monthly' => $sheet[8],
                        'allowance' => $sheet[9],
                        'deduction' => $sheet[10],
                        'incentive' => $sheet[11],
                        'nightdiff' => $sheet[12],

                        'reghdpay' => $sheet[13],
                        'spchdpay' => $sheet[14],
                        'regot' => $sheet[15],
                        'rstot' => $sheet[16],
                        'reghdot' => $sheet[17],
                        'spchdot' => $sheet[18],

                        'tax' => $sheet[19],
                        'sss' => $sheet[20],
                        'phealth' => $sheet[21],
                        'pagibig' => $sheet[22],
                        'hmo' => $sheet[23],
                        'other' => $sheet[24],

                        'sss_amt' => $sheet[25],
                        'sss_term' => $sheet[26],
                        'hdmf_amt' => $sheet[27],
                        'hdmf_term' => $sheet[28],

                        'leave_credit' => $sheet[29],

                        'payinc' => $payinc,
                        'payman' => $payman,

                        'generated_by' => NULL, //TODO
                        'status' => $sheet[0] ? '1':'0'
                    ]);

                    $filename = $this->save_payslip($current, $paydate, $payriod, $dirpath, $group_id);
                    $filename = $filename.".pdf"; //add the pdf extention.
                    $zipArchieve->addFile( $dirpath.$filename, $filename );
                }
                $counter ++;
            }

            $zipArchieve->close();

            return response()->download($zip_filepath)->deleteFileAfterSend(true);

        } catch (Exception $e) {

            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }

        return back()->withSuccess('Great! Data has been successfully uploaded.');
    }

    function sendTestMail(Request $request) {
        //TODO: Temporary
        $data = array(
            "email" => "caezarjepoy@gmail.com",
            "subject" => "Oh hello Mark!",
            "body" => "Personalized Message from Script",
            "attachments" => [storage_path('system')."/master.xlsx", storage_path('system')."/template.xls"],
            "cc" => ["cc@bytescrafter.net"],
            "bcc" => ["bcc@bytescrafter.net"],
            "replyto" => ["replyto@bytescrafter.net"]
        );

        $this->sendMail( $data );
    }

    protected function sendMail( array $data, $company_name = "ABC Company Inc", $company_site = "http://bytescrafter.net") {
        $emailJob = (new PayslipJob())->setPayslipData(new Payslip($data), $company_name, $company_site );
        dispatch($emailJob);
    }

    protected function explodeToList( $string, $separator = ",", $checkMail = false ) {
        $exploded = explode($separator, $string);
        $list = [];
        foreach($exploded as $item) {
            if($checkMail && filter_var($item, FILTER_VALIDATE_EMAIL)) {
                $list[] = $item;
            } else {
                $list[] = $item;
            }
        }
        return $list;
    }

    function bulkSend(Request $request){

        $this->validate($request, [
            'ccmail' => 'max:200',
            'bccmail' => 'required|max:200',
            'replyto' => 'required|max:100',
            'payriod' => 'required|max:50',
            'zipfile' => 'required|file|mimes:zip'
        ]);

        $ccmail = $request->input('ccmail');
        $bccmail = $request->input('bccmail');
        $replyto = $request->input('replyto');
        $payriod = $request->input('payriod');
        $zipfile = $request->file('zipfile');

        //Received ccmail and zip file.
        $zip_filepath = $zipfile->getRealPath();
        $zipArchieve = new \ZipArchive();
        $zipArchieve->open($zip_filepath);

        if ( $zipArchieve->open($zip_filepath) !== TRUE) {
            return back()->withErrors('Zip file cannot be open.');
        }

        $group_id = Uuid::uuid4()->toString();
        $dirpath = $this->createDir('app/mailman/'.$group_id);
        $zipArchieve->extractTo($dirpath);

        $filesInFolder = \File::files( $dirpath );
        foreach($filesInFolder as $path) {
            $file = pathinfo($path);

            $ccs = $this->explodeToList($ccmail, ",", true);
            $bccs = $this->explodeToList($bccmail, ",", true);
            $replytos = $this->explodeToList($replyto, ",", true);

            //TODO:
            $company_domain = env("ERPAT_COMPANY_SITE", 'bytescrafter.net');
            $company_name = env("ERPAT_COMPANY_NAME", 'ABC Company Inc.');

            $data = array(
                "email" => $file['filename']."@".$company_domain,
                "subject" => "Payslip for ".$payriod,
                "payriod" => $payriod,
                "fullname" => $file['filename'],
                //"body" => "",
                "attachments" => [$dirpath.$file['filename'].".".$file['extension']],
                "cc" => $ccs,
                "bcc" => $bccs,
                "replyto" => $replytos
            );
            $this->sendMail( $data, $company_name, "http://".$company_domain );
        }

        $zipArchieve->close();

        return back()->withSuccess('Great! All payslip had been enqueque.');
   }

}
