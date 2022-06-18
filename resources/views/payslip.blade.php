<div style="background-color: #eeeeef; padding: 50px 0; ">
    <div style="max-width:640px; margin:0 auto; ">
        <div style="color: #fff; text-align: center; background-color: #607d8b;
            padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0;">
            <h1 style="text-align: center;">System Generated</h1>
        </div>
        <div style="padding: 20px; background-color: rgb(255, 255, 255);">
            <p style="color: rgb(85, 85, 85); font-size: 14px;"> Hello! {{$payslip->fullname}},<br><br></p>
            <p style="color: rgb(85, 85, 85); font-size: 14px;">
                Please find attached Salary slip for {{$payslip->payriod}}.
            </p>
            <hr>
            <p style="color: rgb(85, 85, 85); font-size: 14px; font-style: italic;">
                Note: If you have any question, do not hesitate to contact us or reply on this email.
            </p>

            <p style="color: rgb(85, 85, 85);"><br></p>
            <p style="color: rgb(85, 85, 85); font-size: 14px;">Regards,</p>
            <p style="color: rgb(85, 85, 85); font-size: 14px;">HR Payroll Team</p>
            <p style="color: rgb(85, 85, 85); font-size: 14px;">{{$company_name}}</p>
            <p style="color: rgb(85, 85, 85); font-size: 14px;">{{$company_site}}</p>
            <p style="color: rgb(85, 85, 85); font-size: 14px; text-align: center; margin-top: 40px; display: grid;">
                <label style="font-size: medium;">ERPat PayHP &copy; 2022</label>
                <label style="font-size: small;">Made possible by <a href="http://bytescrafter.net">BytesCrafter</a></label>
            </p>
        </div>
    </div>
</div>
