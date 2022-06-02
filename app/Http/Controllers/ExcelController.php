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
use App\Models\Payslip;
use App\Libraries\PayHP;
use Ramsey\Uuid\Uuid;

class ExcelController extends Controller
{

    function index() {
        $data = array();
        return view('welcome', compact('data'));
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
            //$data["basic_pay"] = $payslip->basic_pay();

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

            //Add absent per hours.
            $payslip->add_hourly_deductions($paydata['deduction']); //INPUT
            //$data["deduct_pay"] = $payslip->hourly_deductions();
            $data["basic_rate"] = $payslip->basic_rate();

            //Compute all additionals.
            $payslip->add_additional("allowance", $payslip->monthly_allowance/2)
                ->add_additional("hourlynd", $payslip->hourly_nightdiff*($payslip->hourly_rate()*0.1))
                ->add_additional("regularhd", $payslip->hourly_rate()*$payslip->hourly_regholiday)
                ->add_additional("specialhd", $payslip->hourly_rate()*$payslip->hourly_speholiday*0.3)
                ->add_additional("incentive", $payslip->incentives)
                ->add_additional("regular_ot", $payslip->hourly_rate()*$payslip->regular_ot*1.25)
                ->add_additional("restday_ot", $payslip->hourly_rate()*$payslip->restday_ot*1.30)
                ->add_additional("reg_holiday_ot", $payslip->hourly_rate()*$payslip->reg_holiday_ot*2.60)
                ->add_additional("sce_holiday_ot", $payslip->hourly_rate()*$payslip->spe_holiday_ot*1.69);
            //$data["additional"] = round($payslip->total_additional(), 2);

            $data["allowance"] = $payslip->monthly_allowance/2;
            $data["hourlynd"] = $payslip->hourly_nightdiff*($payslip->hourly_rate()*0.1);
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
                ->add_deduction("other", $paydata['other']); //INPUT
            $data["total_deductions"] = round($payslip->total_deductions(), 2);
            $data["hmo_other"] = $paydata['hmo'] + $paydata['other'];

            $data["net_pay"] = round($data["gross_pay"] - $data["total_deductions"], 2);

        return $data;
    }

    protected function save_payslip($data, $paydate, $payriod, $dirpath) {

        $compute = $this->write_payslip($data);

        $template_spreadsheet   = IOFactory::load(storage_path('system/template.xls'));
        $template_activesheet   = $template_spreadsheet->getActiveSheet(); //use for edit. other page.

        $template_activesheet->setCellValue('E5', "Pay Period ".$payriod);
        $template_activesheet->setCellValue('E7', $data['fullname']);
        $template_activesheet->setCellValue('E8', $compute['hourly_rate']);

        $template_activesheet->setCellValue('H7', $data['title']);
        $template_activesheet->setCellValue('H8', $data['department']);
        $template_activesheet->setCellValue('H9', $data['directorate']);

        $template_activesheet->setCellValue('F13', $compute['basic_rate']);
        $template_activesheet->setCellValue('F14', $compute['allowance']);
        $template_activesheet->setCellValue('F15', $compute['hourlynd']);
        $template_activesheet->setCellValue('F16', $compute['regularhd']);
        $template_activesheet->setCellValue('F17', $compute['specialhd']);
        $template_activesheet->setCellValue('F18', $compute['overtime']);
        $template_activesheet->setCellValue('F19', $compute['others']);

        $template_activesheet->setCellValue('H20', $data['tax']);
        $template_activesheet->setCellValue('H21', $data['sss']);
        $template_activesheet->setCellValue('H22', $data['phealth']);
        $template_activesheet->setCellValue('H23', $data['pagibig']);
        $template_activesheet->setCellValue('H24', $compute['hmo_other']);

        $template_activesheet->setCellValue('F26', $compute['gross_pay']);
        $template_activesheet->setCellValue('H26', $compute['total_deductions']);
        $template_activesheet->setCellValue('F28', $compute['net_pay']);

        $template_activesheet->setCellValue('E27', $paydate);
        $template_activesheet->setCellValue('E28', $data['bankname']);
        $template_activesheet->setCellValue('E29', $data['fullname']);
        $template_activesheet->setCellValue('E30', $data['banknum']);

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
            'master' => 'required|file|mimes:xls,xlsx'
        ]);

        $paydate = $request->input('paydate');
        $payriod = $request->input('payriod');
        $excel = $request->file('master');

        try {
            $spreadsheet    = IOFactory::load($excel->getRealPath());
            $activesheet    = $spreadsheet->getActiveSheet();
            $datasheet      = $activesheet->toArray();

            $counter       = 1;
            $startRow       = 4;

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
                        'other' => $sheet[24]
                    );

                    //Save to model.
                    $payslip = new Payslip();
                    $insertid = $payslip->store([
                        'uuid' => Uuid::uuid4()->toString(),
                        'group_id' => $group_id,

                        'fullname' => $sheet[1],
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

    function bulkSend(Request $request){

        //Received ccmail and zip file.

        //Unzip to temporary location.

        //Queue all payslip for sending.

        //Delete all temporary files and zip.

        //Return all emails successfully send.
   }

}
