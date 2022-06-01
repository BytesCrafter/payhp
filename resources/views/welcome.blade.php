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

            <div class="card-header bg-secondary dark bgsize-darken-4 white card-header">
                <h4 class="text-white">PAS - PAYSLIP AUTOMATION SYSTEM</h4>
            </div>

            <div class="row justify-content-centre" style="margin-top: 4%">
                <div class="col-md-12">
                    <div class="card">

                        <div class="card-header bgsize-primary-4 white card-header">
                            <h4 class="card-title">Import Excel Data</h4>
                        </div>

                        <div class="card-body">

                            @if ($message = Session::get('success'))

                                <div class="alert alert-success alert-block">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    <strong>{{ $message }}</strong>
                                </div>

                                <br>

                            @endif

                            <form action="{{url("import")}}" method="post" enctype="multipart/form-data">
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

                                    <label>Select File to Upload <small class="warning text-muted">{{__('Please upload only Excel (.xlsx or .xls) files')}}</small></label>

                                    <div class="form-group">
                                        <label for="master">Master</label>
                                        <input type="file" required class="form-control" name="master" id="master" >
                                        @if ($errors->has('master'))
                                            <p class="text-right mb-0">
                                                <small class="danger text-muted" id="file-error">{{ $errors->first('master') }}</small>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="template">Template</label>
                                        <input type="file" required class="form-control" name="template" id="template">
                                        @if ($errors->has('template'))
                                            <p class="text-right mb-0">
                                                <small class="danger text-muted" id="file-error">{{ $errors->first('template') }}</small>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="input-group-append" id="button-addon2">
                                        <button class="btn btn-primary square" type="submit"><i class="ft-upload mr-1"></i> Upload</button>
                                    </div>

                                </fieldset>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="row justify-content-left" style="margin-top: 50px;">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bgsize-primary-4 white card-header">
                            <h4 class="card-title">Employee Payroll</h4>
                        </div>

                        <div class="card-body">
                            <div class="pull-right">
                                <a href="{{url("export")}}" class="btn btn-primary" style="margin-left:85%">Download All Payslips</a>
                            </div>

                            <div class=" card-content table-responsive">
                                <table id="example" class="table table-striped table-bordered" style="width:100%">

                                    <thead>
                                        <th>Customer Name</th>
                                        <th>Gender</th>
                                        <th>Address</th>
                                        <th>City</th>
                                        <th>Postal Code</th>
                                        <th>Country</th>
                                    </thead>

                                    <tbody>

                                        @if(!empty($data) && $data->count())

                                        @foreach($data as $row)

                                        <tr>

                                            <td>{{ $row->CustomerName }}</td>

                                            <td>{{ $row->Gender }}</td>

                                            <td>{{ $row->Address }}</td>

                                            <td>{{ $row->City }}</td>

                                            <td>{{ $row->PostalCode }}</td>

                                            <td>{{ $row->Country }}</td>

                                        </tr>

                                        @endforeach

                                        @else

                                        <tr>

                                            <td colspan="10">There are no data.</td>

                                        </tr>

                                        @endif




                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
            {{-- <script>
                $(document).ready(function () {
                    $('#example').DataTable();
                });
            </script> --}}

    </body>
</html>
