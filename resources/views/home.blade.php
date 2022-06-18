@include('includes.header')

<nav class="navbar navbar-expand-lg navbar-dark sticky-top bg-dark">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03"
            aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="#">ERPat PayHP - Hi! {{ $curUser->first_name }}</a>
        <div class="collapse d-flex" id="navbarTogglerDemo03">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="{{url('/logout')}}">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
{{-- <div class="container">
                Test 1: {{$curUser}}
</div> --}}
<div class="container">

    <div class="row justify-content-centre" style="margin-top: 30px;">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header bg-secondary">
                    <h5 class="card-title text-center text-white">GETTING STARTED! </h5>
                    <label class="text-white">Download the master file and populate
                        with payroll data.</label>
                </div>

                <div class="card-body" style="text-align: center">
                    <div class="alert alert-info p-10" role="alert">
                        Please download this master file then populate with the data.
                    </div>
                    <a type="button" class="btn btn-success" href="download" target="_blank">Download Master File</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-centre" style="margin-top: 30px;">
        <div class="col-md-6">
            <div class="card">

                <div class="card-header bg-secondary">
                    <h5 class="card-title text-center text-white">STEP 1: PAYSLIP GENERATOR</h5>
                    <label class="text-white p-2">Upload the processed
                        master file and click on bulk generate.</label>
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
                                <input type="text" required class="form-control" name="payriod" id="payriod"
                                    placeholder="January 1 - 15">
                            </div>

                            <div class="form-group">
                                <label for="paydate">Payment Date</label>
                                <input type="text" required class="form-control" name="paydate" id="paydate"
                                    placeholder="January 25, 2022">
                            </div>

                            <label>Select File to Upload <small
                                    class="warning text-muted">{{__('Please upload only Zip (.zip) files')}}</small></label>

                            <div class="form-group">
                                <label for="master">Master File</label>
                                <input type="file" required class="form-control" name="master" id="master">
                                @if ($errors->has('master'))
                                <p class="text-right mb-0">
                                    <small class="danger text-muted"
                                        id="file-error">{{ $errors->first('master') }}</small>
                                </p>
                                @endif
                            </div>

                            <div class="input-group-append" id="button-addon2"
                                style="text-align: center; display: block; padding-top: 20px;">
                                <button class="btn btn-primary square" type="submit"><i class="ft-upload mr-1"></i> Bulk
                                    Generate</button>
                            </div>

                        </fieldset>
                    </form>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">

                <div class="card-header bg-secondary">
                    <h5 class="card-title text-center text-white">STEP 2: PAYSLIP MAILMAN</h5>
                    <label class="text-white p-2">After cross-checking the
                        generated payslip, use this to send payslip.</label>
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
                                <label for="ccmail">Email CC <small
                                        class="warning text-muted">{{__('Secparate multiple emails with a comma.')}}</small></label>
                                <input type="text" required class="form-control" name="ccmail" id="ccmail"
                                    placeholder="juan@example.com,mark@example.com">
                            </div>

                            <div class="form-group">
                                <label for="bccmail">Email BCC <small
                                        class="warning text-muted">{{__('Secparate multiple emails with a comma.')}}</small></label>
                                <input type="text" required class="form-control" name="bccmail" id="bccmail"
                                    placeholder="juan@example.com,mark@example.com">
                            </div>

                            <label>Select File to Upload <small
                                    class="warning text-muted">{{__('Please upload only Excel (.xlsx or .xls) files')}}</small></label>

                            <div class="form-group">
                                <label for="zipfile">Zip File</label>
                                <input type="file" required class="form-control" name="zipfile" id="zipfile">
                                @if ($errors->has('zipfile'))
                                <p class="text-right mb-0">
                                    <small class="danger text-muted"
                                        id="file-error">{{ $errors->first('zipfile') }}</small>
                                </p>
                                @endif
                            </div>

                            <div class="input-group-append" id="button-addon2"
                                style="text-align: center; display: block; padding-top: 20px;">
                                <button class="btn btn-danger square" type="submit"><i class="ft-upload mr-1"></i>
                                    Schedule Mail</button>
                            </div>

                        </fieldset>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>

<footer class="navbar fixed-bottom" style="display: block;">
    <div class="container" style="display: block;">
        <p class="mt-2 mb-2 text-muted" style="display: grid;">
            <label >ERPat PayHP &copy; <?= date("Y"); ?></label>
            <label style="font-size: small;">Made Possible by <a href="http://bytescrafter.net">BytesCrafter</a></label>
        </p>
    </div>
  </footer>

@include('includes.footer')
