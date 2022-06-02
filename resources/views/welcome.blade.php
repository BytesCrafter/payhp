<!DOCTYPE html>
<html>
    <head>
        <title>ERPat - PAS</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css" />
        {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" /> --}}
    </head>
    <body>
        <div class="container">

            <div class="card-header bg-primary dark bgsize-darken-4 white card-header">
                <h4 class="text-white">PAS - PAYSLIP AUTOMATION SYSTEM</h4>
            </div>

            <div class="row justify-content-centre" style="margin-top: 30px;">
                <div class="col-md-12">
                    <div class="card">

                        <div class="card-header bgsize-primary card-header">
                            <h4 class="card-title">STEP 1: PAYSLIP SUMMARY - Download the master file and populate with payroll data.</h4>
                        </div>

                        <div class="card-body" style="text-align: center">
                            <a type="button" class="btn btn-info" href="download" target="_blank">Download Master File</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-centre" style="margin-top: 30px;">
                <div class="col-md-12">
                    <div class="card">

                        <div class="card-header bgsize-primary card-header">
                            <h4 class="card-title">STEP 2: PAYSLIP GENERATOR - Upload the processed master file and click on bulk generate.</h4>
                        </div>

                        <div class="card-body">

                            @if ($message = Session::get('success'))

                                <div class="alert alert-success alert-block">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    <strong>{{ $message }}</strong>
                                </div>

                                <br>

                            @endif

                            <form action="{{url("generate")}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <fieldset>

                                    <div class="form-group">
                                        <label for="payriod">Pay Period</label>
                                        <input type="text" required class="form-control" name="payriod" id="payriod" placeholder="January 1 - 15">
                                    </div>

                                    <div class="form-group">
                                        <label for="paydate">Payment Date</label>
                                        <input type="text" required class="form-control" name="paydate" id="paydate" placeholder="January 25, 2022">
                                    </div>

                                    <label>Select File to Upload <small class="warning text-muted">{{__('Please upload only Zip (.zip) files')}}</small></label>

                                    <div class="form-group">
                                        <label for="master">Master File</label>
                                        <input type="file" required class="form-control" name="master" id="master" >
                                        @if ($errors->has('master'))
                                            <p class="text-right mb-0">
                                                <small class="danger text-muted" id="file-error">{{ $errors->first('master') }}</small>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="input-group-append" id="button-addon2" style="text-align: right; display: block;">
                                        <button class="btn btn-primary square" type="submit"><i class="ft-upload mr-1"></i> Bulk Generate</button>
                                    </div>

                                </fieldset>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-centre" style="margin-top: 30px;">
                <div class="col-md-12">
                    <div class="card">

                        <div class="card-header bgsize-primary card-header">
                            <h4 class="card-title">STEP 3: PAYSLIP MAILMAN - After cross-checking the generated payslip, use this to send payslip.</h4>
                        </div>

                        <div class="card-body">

                            @if ($message = Session::get('success'))

                                <div class="alert alert-success alert-block">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    <strong>{{ $message }}</strong>
                                </div>

                                <br>

                            @endif

                            <form action="{{url("send")}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <fieldset>

                                    <div class="form-group">
                                        <label for="ccmail">Email CC <small class="warning text-muted">{{__('Secparate multiple emails with a comma.')}}</small></label>
                                        <input type="text" required class="form-control" name="ccmail" id="ccmail" placeholder="juan@example.com,mark@example.com">
                                    </div>

                                    <label>Select File to Upload <small class="warning text-muted">{{__('Please upload only Excel (.xlsx or .xls) files')}}</small></label>

                                    <div class="form-group">
                                        <label for="zipfile">Zip File</label>
                                        <input type="file" required class="form-control" name="zipfile" id="zipfile" >
                                        @if ($errors->has('zipfile'))
                                            <p class="text-right mb-0">
                                                <small class="danger text-muted" id="file-error">{{ $errors->first('zipfile') }}</small>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="input-group-append" id="button-addon2" style="text-align: right; display: block;">
                                        <button class="btn btn-warning square" type="submit"><i class="ft-upload mr-1"></i> Bulk Send</button>
                                    </div>

                                </fieldset>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
            {{-- <script>
                $(document).ready(function () {
                    $('#example').DataTable();
                });
            </script> --}}

    </body>
</html>
