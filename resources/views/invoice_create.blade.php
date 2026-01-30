@extends('tmp')

@section('title', 'Invoice')


@section('content')


    <!-- Modal -->
    <div class="modal fade exampleModal" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add new customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <form method="post">
                    <input type="hidden" name="_token" value="SF5krfPegrIS3icD2CjPx78GxfBtaQjeKBoyQ3U2">
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-12">
                                <label for=""><strong>Customer : *</strong></label>
                                <input type="text" class="form-control" id="PartyName" name="PartyName" required>
                                <span class="error-message" id="name-error">Name is required.</span>
                            </div>

                            <div class="col-12 mt-2">
                                <label for=""><strong>Mobile No: *</strong></label>
                                <input type="text" class="form-control" id="Phone" name="Phone" required="">
                                <span class="error-message" id="email-error">Phone Number is required.</span>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="submitButton" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Responsive datatable examples -->



    <style type="text/css">
        .form-control {
            border-radius: 0 !important;


        }

        .select2 {
            border-radius: 0 !important;
            width: 100% !important;

        }


        .swal2-popup {
            font-size: 0.8rem;
            font-weight: inherit;
            color: #5E5873;
        }

        .select2-container--default .select2-search--dropdown {
            padding: 1px !important;
            background-color: #556ee6 !important;
        }
    </style>


    <?php
    
    $company = DB::table('company')->first();
    
    ?>

    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->

                @if (session('error'))
                    <div class="alert alert-{{ Session::get('class') }} p-1" id="success-alert">

                        {{ Session::get('error') }}
                    </div>
                @endif

                @if (count($errors) > 0)

                    <div>
                        <div class="alert alert-danger p-1   border-3">
                            <p class="font-weight-bold"> There were some problems with your input.</p>
                            <ul>

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                @endif


                <?php
                $DrTotal = 0;
                $CrTotal = 0;
                ?>
                <div class="card shadow-sm">
                    <div class="card-body">


                        <!-- enctype="multipart/form-data" -->
                        <form action="{{ URL('/InvoiceSave') }}" method="post">


                            <input type="hidden" name="_token" id="csrf" value="{{ Session::token() }}">


                            <div class=" ">
                                <div class=" ">



                                    <div class="row">
                                        <div class="col-6">


                                            <br>
                                            <br>
                                            <div class="col-6">
                                                <label for="">Invoice Type</label>
                                                <select class="form-select select2 " name="InvoiceTypeID" required=""
                                                    id="InvoiceTypeID">
                                                    <?php foreach ($invoice_type as $key => $value): ?>
                                                    <option value="{{ $value->InvoiceTypeID }}">
                                                        {{ $value->InvoiceTypeCode }}-{{ $value->InvoiceType }}
                                                    </option>
                                                    <?php endforeach ?>
                                                </select>

                                                <div class="clearfix mt-1"></div>
                                                <label for="">Party</label>

                                                <select name="PartyID" id="PartyID" class="form-select select2 mt-5"
                                                    required></select>




                                                <span id="PartyError" style="color: red; display: none;">Please select a
                                                    party</span>


                                                      <label class="mt-2">Reference No</label>

                                            <input type="text" class="form-control" name="ReferenceNo" placeholder="Reference No">
                                            </div>
                                            <div class="clearfix mt-1"></div>
                                          
                                        </div>


                                        <div class="col-2"> </div>
                                        <div class="col-4">


                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="mb-1 row">
                                                        <div class="col-sm-3">
                                                            <label class="col-form-label" for="first-name">Invoice #</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="first-name" class="form-control"
                                                                name="VHNO" value="{{ $vhno[0]->VHNO }}" readonly="">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="mb-1 row">
                                                        <div class="col-sm-3">
                                                            <label class="col-form-label" for="first-name">Lead #</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="first-name" class="form-control"
                                                                name="LeadID" value="{{ session::get('LeadID') }}"
                                                                readonly="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mb-1 row">
                                                        <div class="col-sm-3">
                                                            <label class="col-form-label" for="email-id">Date</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <div class="input-group" id="datepicker21">
                                                                <input type="date" name="Date" class="form-control"
                                                                    value="{{ date('Y-m-d') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="mb-1 row">
                                                            <div class="col-sm-3">
                                                                <label class="col-form-label" for="contact-info">Due
                                                                    Date</label>
                                                            </div>
                                                            <div class="col-sm-9">
                                                                <div class="input-group" id="datepicker22">

                                                                    <input type="date" name="DueDate"
                                                                        class="form-control" value="{{ date('Y-m-d') }}">


                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="PaymentMode" value="">
                                                    {{-- <div class="col-12">
                          <div class="mb-1 row">
                            <div class="col-sm-3">
                              <label class="col-form-label" for="password">Payment Mode </label>
                            </div>
                            <div class="col-sm-9">
                              <select name="PaymentMode" id="PaymentMode" class="form-select">
                                <option value="">Select</option>
                                <option value="Cash">Cash</option>
                                <option value="ENBD Bank ">ENBD BANK</option>
                                <option value="ADCB Bank">ADCB BANK</option>
                                <option value="Credit Card">Credit Card</option>
                              </select>
                              <span id="PaymentModeError" style="color: red; display: none;">Please select a Payment
                                Mode</span>
                            </div>
                           
                          </div>
                        </div> --}}

                                                    <div class="col-12">
                                                        <div class="mb-1 row">
                                                            <div class="col-sm-3">
                                                                <label class="col-form-label" for="password">Salesman
                                                                </label>
                                                            </div>
                                                            <div class="col-sm-9">
                                                                <select name="SalemanID" id="SalemanID"
                                                                    class="form-select">
                                                                    <option value="">Select</option>
                                                                    <?php foreach ($saleman as $key => $value): ?>
                                                                    <option value="{{ $value->UserID }}">
                                                                        {{ $value->FullName }}</option>
                                                                    <?php endforeach ?>
                                                                </select>
                                                                <span id="SalemanError"
                                                                    style="color: red; display: none;">Please select a
                                                                    Saleman</span>
                                                            </div>

                                                        </div>
                                                    </div>


                                                    <div class="col-12">
                                                        <div class="mb-1 row">
                                                            <div class="col-sm-3">
                                                                <label class="col-form-label" for="password">Source
                                                                </label>
                                                            </div>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="Source" id="Source"
                                                                    class="form-control">
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <hr class="invoice-spacing">

                                        <div class='text-center'>

                                        </div>
                                        <div class='row'>
                                            <div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>
                                                <table width="100%">
                                                    <thead>
                                                        <tr class="bg-light borde-1 border-light " style="height: 40px;">
                                                            <th width="2%" class="p-1"><input id="check_all"
                                                                    type="checkbox" /></th>
                                                            <th width="12%">Item</th>
                                                            <th width="5%" class="d-none">Ref No</th>
                                                            <!-- <th width="5%">Visa </th> -->
                                                            <th width="10%">PAX Name</th>
                                                            <!-- <th width="8%">PNR</th> -->
                                                            <th width="5%">Sector</th>
                                                            <th width="5%">Fare</th>
                                                            <th width="5%">VAT%</th>
                                                            <th width="5%">Service</th>
                                                            <th width="5%" class="d-none">O/P Vat</th>
                                                            <th width="5%" class="d-none">I/P VAT</th>
                                                            <!--   <th width="6%">VAT</th>
                                <th width="4%">Dis</th> -->
                                                            <th width="7%">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="p-3" style="vertical-align: top;">
                                                            <td class="p-1 bg-light borde-1 border-light"><input
                                                                    class="case" type="checkbox" /></td>
                                                            <td>

                                                                <select name="ItemID0[]" id="ItemID0_1"
                                                                    class="form-select form-control-sm  select2 item-select"
                                                                    required>
                                                                    <option value="">Select Item</option>
                                                                    @foreach ($items as $key => $value)
                                                                        <option value="{{ $value->ItemID }}"
                                                                            data-tax="{{ $value->Percentage }}">
                                                                            {{ $value->ItemCode }}-{{ $value->ItemName }}-{{ $value->Percentage }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <select name="SupplierID[]" id="SupplierID_1"
                                                                    class="form-select select2" required
                                                                    onchange="ajax_balance(this.value);">
                                                                    <option value="">Select Supplier</option>
                                                                    @foreach ($supplier as $key => $value)
                                                                        <option value="{{ $value->SupplierID }}">
                                                                            {{ $value->SupplierName }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <input type="hidden" name="ItemID[]" id="ItemID_1">
                                                            </td>


                                                            <td class="d-none">
                                                                <input type="text" name="RefNo[]" id="RefNo_1"
                                                                    class="form-control      " autocomplete="off"
                                                                    placeholder="RefNo"><input type="text"
                                                                    name="VisaType[]" id="VisaType_1"
                                                                    class="   form-control  " autocomplete="off"
                                                                    placeholder="Visa">

                                                            </td>

                                                            <td>
                                                                <input type="text" name="PaxName[]" id="PaxName_1"
                                                                    class=" form-control  " autocomplete="off"
                                                                    placeholder="PaxName"><input type="text"
                                                                    name="TicketNo[]" id="TicketNo_1"
                                                                    class="form-control  " autocomplete="off"
                                                                    placeholder="Ticket No">
                                                            </td>

                                                            <td>
                                                                <input type="text" name="Sector[]" id="Sector_1"
                                                                    class=" form-control  " autocomplete="off"
                                                                    placeholder="Sector"><input type="text"
                                                                    name="PNR[]" id="PNR_1" class=" form-control  "
                                                                    autocomplete="off" placeholder="PNR">
                                                            </td>
                                                            <td>
                                                                <input type="number" name="Fare[]" id="Fare_1"
                                                                    class=" form-control row-calculation" required
                                                                    autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" placeholder="Fare">
                                                                <input type="text" name="Passport[]" id="Passport_1"
                                                                    class="form-control  " autocomplete="off"
                                                                    placeholder="Passport #">
                                                            </td>
                                                            <td>
                                                                 <input type="number" name="TaxPer[]" id="TaxPer_1"
                                                                    class="form-control" autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" placeholder="VAT Amount" readonly>
                                                                <input type="number" name="Taxable[]" id="Taxable_1"
                                                                    class=" form-control   " autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" placeholder="VAT %" readonly>
                                                               
                                                            </td>
                                                            <td>
                                                                <input type="number" name="Service[]" id="Service_1"
                                                                    class=" form-control row-calculation" autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" placeholder="Service">
                                                                <input type="number" name="Discount[]" id="discount_1"
                                                                    class=" form-control" autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" placeholder="Discount">
                                                            </td>
                                                            <td class="d-none">
                                                                <input type="number" name="OPVAT[]" id="OPVAT_1"
                                                                    class=" form-control " autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01">
                                                            </td>
                                                            <td class="d-none">
                                                                <input type="number" name="IPVAT[]" id="IPVAT_1"
                                                                    class=" form-control " autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01">
                                                            </td>






                                                            <td>
                                                                <input type="number" name="ItemTotal[]" id="total_1"
                                                                    required class=" form-control totalLinePrice"
                                                                    autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" placeholder="Total" readonly>
                                                                <input type="date" name="DepartureDate[]"
                                                                    id="DepartureDate_1" class="form-control">
                                                            </td>
                                                        </tr>


                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="row mt-1 mb-2" style="margin-left: 29px;">
                                            <div class='col-xs-5 col-sm-3 col-md-3 col-lg-3  '>
                                                <button class="btn btn-danger delete" type="button"><i
                                                        class="bx bx-trash align-middle font-medium-3 me-25"></i>Delete</button>
                                                <button class="btn btn-success addmore" type="button"><i
                                                        class="bx bx-list-plus align-middle font-medium-3 me-25"></i> Add
                                                    More</button>

                                            </div>

                                            <div class='col-xs-5 col-sm-3 col-md-3 col-lg-3  '>
                                                <div id="result"></div>

                                            </div>
                                            <br>

                                        </div>


                                        <div class="row">

                                            <div class="col-lg-8 col-12  ">
                                                <h5>Notes: </h5>


                                                <textarea class="form-control" rows='5' name="remarks" id="notes" placeholder="Your Notes"></textarea>




                                                <div class="mt-2"><button type="submit"
                                                        class="btn-disable btn btn-success w-lg float-right">Save</button>
                                                    <a href="{{ URL('/Invoice') }}"
                                                        class="btn btn-secondary w-lg float-right">Cancel</a>

                                                </div>


                                            </div>


                                            <div class="col-lg-4 col-12 ">
                                                <form class="form-inline">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input type="hidden" class="form-control" id="subTotal"
                                                                name="subTotal" placeholder="Subtotal"
                                                                onkeypress="return IsNumeric(event);"
                                                                ondrop="return false;" onpaste="return false;">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input type="hidden" class="form-control" id="tax"
                                                                placeholder="Tax" onkeypress="return IsNumeric(event);"
                                                                ondrop="return false;" onpaste="return false;">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input type="hidden" class="form-control" id="taxAmount"
                                                                placeholder="Tax" onkeypress="return IsNumeric(event);"
                                                                ondrop="return false;" onpaste="return false;">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">

                                                        <label>
                                                            <h5>Total: &nbsp;</h5>
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light">AED</span>
                                                            <input type="number" name="Total" class="form-control"
                                                                step="0.01" id="totalAftertax" placeholder="Total"
                                                                onkeypress="return IsNumeric(event);"
                                                                ondrop="return false;" onpaste="return false;">
                                                        </div>
                                                    </div>



                                                    <div class="form-group mt-3 d-none">
                                                        <label>
                                                            <h5>Bank Charges:</h5>
                                                        </label>

                                                        <div class="row">
                                                            <!-- Dropdown -->
                                                            <div class="col-md-4">
                                                                <select name="BankName" class="form-select">
                                                                    <option value="">Select</option>
                                                                    <option value="Nomod">Nomod</option>
                                                                    <option value="Tabbay">Tabbay</option>
                                                                    <option value="Tamara">Tamara</option>
                                                                </select>
                                                            </div>

                                                            <!-- Percentage Input -->
                                                            <div class="col-md-4">
                                                                <div class="input-group">
                                                                    <input type="number" name="Percentage"
                                                                        class="form-control" placeholder="%"
                                                                        step="0.01" min="0" max="100"
                                                                        value="0">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </div>

                                                            <!-- Value Input -->
                                                            <div class="col-md-4">
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light">AED</span>
                                                                    <input type="number" name="PercentageValue"
                                                                        id="PercentageValue" class="form-control"
                                                                        step="0.01" placeholder="Amount"
                                                                        onkeypress="return IsNumeric(event);"
                                                                        ondrop="return false;" onpaste="return false;"
                                                                        value="0">
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>

                                                    <div class="form-group mt-2">

                                                        <label>
                                                            <H5>Grand Total: &nbsp;</H5>
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light">AED</span>
                                                            <input type="number" class="form-control" name="GrandTotal"
                                                                id="GrandTotal" placeholder="Amount Due"
                                                                onkeypress="return IsNumeric(event);"
                                                                ondrop="return false;" onpaste="return false;"
                                                                step="0.01">
                                                        </div>
                                                    </div>

                                                    <input type="hidden" class="form-control" id="amountPaid-"
                                                        name="amountPaid" value="0" />
                                                    {{-- <div class="form-group mt-1">
                          <label>
                            <h5>Amount Paid: &nbsp;</h5>
                          </label>
                          <div class="input-group">
                            <span class="input-group-text bg-light">AED</span>
                            <input type="number" class="form-control" id="amountPaid" name="amountPaid"
                              placeholder="Amount Paid" onkeypress="return IsNumeric(event);" ondrop="return false;"
                              onpaste="return false;" step="0.01" value="0">
                          </div>
                        </div> --}}

                                                    <div class="form-group mt-1">

                                                        <label>
                                                            <H5>Amount Due: &nbsp;</H5>
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light">AED</span>
                                                            <input type="number" class="form-control amountDue"
                                                                name="amountDue" id="amountDue" placeholder="Amount Due"
                                                                onkeypress="return IsNumeric(event);"
                                                                ondrop="return false;" onpaste="return false;"
                                                                step="0.01">
                                                        </div>
                                                    </div>

                                            </div>
                                        </div>
                                        <div>



                                        </div>






                                        <!--  <div class='row'>
              <div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>
                <div class="well text-center">
              <h2>Back TO Tutorial: <a href="#"> Invoice System </a> </h2>
            </div>
              </div>
            </div>   -->



                                    </div>
                                </div>
                            </div>





                        </form>



                    </div>
                </div>

            </div>
        </div>

    </div>
    </div>
    </div>














    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>


    @include('invoice_js');

   





    <!-- ajax trigger -->
    <script>
        







        $(document).on('keyup', '#Phone', function() {
            ajax_party_validate();
        });





        function ajax_party_validate() {

            // alert($("#csrf").val());


            var Phone = $('#Phone').val();

            // alert(SupplierID);
            if (Phone != "") {
                /*  $("#butsave").attr("disabled", "disabled"); */
                // alert(SupplierID);
                $.ajax({
                    url: "{{ URL('/ajax_party_validate') }}",
                    type: "POST",
                    data: {
                        _token: $("#csrf").val(),
                        Phone: Phone,

                    },
                    cache: false,
                    success: function(data) {



                        if (data.total == 0) {

                            $('#Phone').removeClass('border-red').addClass('border-green');
                            $('#submitButton').removeAttr('disabled');


                            $('#email-error').text('validated successfully');
                            $("#email-error").css("color", "green");
                            $('#email-error').show();

                        } else {
                            $('#submitButton').attr('disabled', 'disabled');
                            $('#Phone').removeClass('border-green').addClass('border-red');
                            $("#email-error").css("color", "red");
                            $('#email-error').text('Phone no already exists');
                            $('#email-error').show();
                        }
                        $('#result').html(data);
                    }
                });
            } else {
                $('#email-error').text('Phone number is required');
                $("#email-error").css("color", "red");
                $('#email-error').show();
            }

        }
    </script>


    <script>
        $(document).ready(function() {



            $('#PartyID').select2({
                allowClear: true,
                placeholder: 'This is my placeholder',
                language: {
                    noResults: function() {
                        // console.log('no record ounf');
                        return `<button style="width: 100%" type="button"
              class="btn btn-primary" 
              onClick='task()'>+ Add New Customer</button>
              </li>`;
                    }
                },

                escapeMarkup: function(markup) {
                    return markup;
                }
            });


        });




        function task() {
            // alert("Hello world! ");


            $('#PartyID').select2('close');

            $('input[name="PartyName"]').focus();
            $('#exampleModal').modal('show');

        }
    </script>


    <script>
        $(document).ready(function() {
            $('#submitButton').click(function() {
                var isValid = true;

                // Validate the name field
                var PartyName = $('#PartyName').val().trim();
                if (PartyName === '') {
                    $('#name').addClass('error');
                    $('#name-error').show();
                    isValid = false;
                } else {
                    $('#name').removeClass('error');
                    $('#name-error').hide();
                }

                // Validate the email field
                var Phone = $('#Phone').val().trim();
                if (Phone === '') {
                    $('#email').addClass('error');
                    $('#email-error').show();
                    isValid = false;
                } else {
                    $('#email').removeClass('error');
                    $('#email-error').hide();
                }




                // If the form is valid, make the AJAX request
                if (isValid) {

                    // alert('vvv');
                    $.ajax({
                        url: '{{ URL('/ajax_party_save') }}', // Replace with your server endpoint
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}', // Laravel's CSRF token
                            PartyName: PartyName,
                            Phone: Phone,
                        },
                        success: function(response) {
                            // Handle the response from the server


                            // alert('Form submitted successfully!');




                            console.log(response.PartyID);
                            console.log(response.PartyName);
                            console.log(response.Phone);


                            $("#PartyID").append('<option value=' + response.PartyID +
                                ' selected >' + response.PartyID + '-' + response
                                .PartyName + '-' + response.Phone + '</option>');

                            $('#exampleModal').modal('hide');

                            checkSelection();

                        },






                        error: function(xhr, status, error) {
                            // Handle any errors
                            alert('An error occurred: ' + error);
                        }
                    });
                }
            });



            $(document).on('keyup', '#PartyName', function() {

                var isValid = true;

                // Validate the name field
                var nameValue = $('#PartyName').val().trim();
                if (nameValue === '') {
                    $('#name').addClass('error');
                    $('#name-error').show();
                    isValid = false;
                } else {
                    $('#name').removeClass('error');
                    $('#name-error').hide();
                }



            });



            $(document).on('keyup', '#Phone', function() {

                var isValid = true;

                // Validate the email field
                var emailValue = $('#Phone').val().trim();

                if (emailValue === '') {
                    $('#email').addClass('error');
                    $('#email-error').show();
                    isValid = false;
                } else {
                    $('#email').removeClass('error');
                    $('#email-error').hide();
                }


            });



        });
    </script>


    <script>
        $(document).ready(function() {
            // Initialize select2
            $('#searchField').select2();

            // Event listener for input on select2 search field
            $(document).on('input', '.select2-search__field', function() {
                // Get the value from the select2 search field
                var searchValue = $(this).val();

                // Assign the value to the new text field
                $('#Phone').val(searchValue);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const $paymentModeSelect = $('#PaymentMode');
            const $salemanIDSelect = $('#SalemanID');
            const $partyIDSelect = $('#PartyID');
            const $buttons = $('.btn-disable');
            const $paymentModeError = $('#PaymentModeError');
            const $salemanError = $('#SalemanError');
            const $partyError = $('#PartyError');


            // Initialize state
            checkSelection();

            // Event listeners for select changes
            $paymentModeSelect.on('change', checkSelection);
            $salemanIDSelect.on('change', checkSelection);
            $partyIDSelect.on('change', checkSelection);
        });
    </script>
    <script>
        function checkSelection() {
            const $paymentModeSelect = $('#PaymentMode');
            const $salemanIDSelect = $('#SalemanID');
            const $partyIDSelect = $('#PartyID');
            const $buttons = $('.btn-disable');
            const $paymentModeError = $('#PaymentModeError');
            const $salemanError = $('#SalemanError');
            const $partyError = $('#PartyError');

            // Function to check if all required fields are selected

            const paymentModeValue = $paymentModeSelect.val();
            const salemanIDValue = $salemanIDSelect.val();
            const partyIDValue = $partyIDSelect.val();

            // Check Payment Mode selection
            if (paymentModeValue !== "") {
                $paymentModeError.hide();
            } else {
                $paymentModeError.show();
            }

            // Check Saleman ID selection
            if (salemanIDValue !== "") {
                $salemanError.hide();
            } else {
                $salemanError.show();
            }

            // Check Party ID selection
            if (partyIDValue !== "") {
                $partyError.hide();
            } else {
                $partyError.show();
            }

            // Enable/disable buttons based on all conditions being met
            if (paymentModeValue !== "" && salemanIDValue !== "" && partyIDValue !== "") {
                $buttons.prop('disabled', false);
            } else {
                $buttons.prop('disabled', true);
            }
        }
    </script>




    <script>
        $(document).ready(function() {
            $('#PartyID').select2({
                placeholder: 'This is my placeholder',
                allowClear: true,
                ajax: {
                    url: '{{ URL('/get-parties') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.PartyID,
                                    text: item.PartyID + ' - ' + item.PartyName + ' - ' + item
                                        .Phone
                                };
                            })
                        };
                    },
                    cache: true
                },
                language: {
                    noResults: function() {
                        return `<button type="button" class="btn btn-primary w-100 mt-2" onclick="task()">+ Add New Customer</button>`;
                    }
                },
                escapeMarkup: function(markup) {
                    return markup;
                }
            });
        });
    </script>

@endsection
