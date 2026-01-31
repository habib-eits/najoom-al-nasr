@extends('tmp')

@section('title', 'Invoice')


@section('content')

    @php

        $company = DB::table('company')->first();
    @endphp


    <!-- Responsive datatable examples -->


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>

    <style type="text/css">
        .form-control,
        .form-select {
            border-radius: 0 !important;

        }
    </style>


    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-print-block d-sm-flex align-items-center justify-content-between">
                            <strong class="text-end"> </strong>


                        </div>
                    </div>
                </div>
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
                        <form action="{{ URL('/InvoiceUpdate') }}" method="post">


                            <input type="hidden" name="_token" id="csrf" value="{{ Session::token() }}">


                            <div class="">
                                <div class="">



                                    <div class="row">
                                        <div class="col-6"> 
                                          {{-- <img src="{{ asset('documents/' . $company->Logo) }}" alt=""> --}}

                                            <br>
                                            <br>
                                            <div class="col-6">
                                                <label for="">Invoice Type</label>
                                                <select class="form-select select2" name="InvoiceTypeID" id="InvoiceTypeID"
                                                    required="">
                                                    <?php foreach ($invoice_type as $key => $value): ?>
                                                    <option value="{{ $value->InvoiceTypeID }}"
                                                        {{ $value->InvoiceTypeID == $invoice_mst[0]->InvoiceTypeID ? 'selected=selected' : '' }}>
                                                        {{ $value->InvoiceTypeCode }}-{{ $value->InvoiceType }}</option>
                                                    <?php endforeach ?>
                                                </select>

                                                <div class="clearfix mt-1"></div>
                                                <label for="">Party</label>

                                                <select name="PartyID" id="PartyID" class="form-select select2 mt-5"
                                                    name="PartyID" required="">
                                                    <?php foreach ($party as $key => $value): ?>
                                                    <option value="{{ $value->PartyID }}"
                                                        {{ $value->PartyID == $invoice_mst[0]->PartyID ? 'selected=selected' : '' }}>
                                                        {{ $value->PartyID }}-{{ $value->PartyName }}-{{ $value->Phone }}
                                                    </option>
                                                    <?php endforeach ?>
                                                </select>

                                                 <label class="mt-2">Reference No</label>

                                                <input type="text" class="form-control" name="ReferenceNo" placeholder="Reference No" value="{{$invoice_mst[0]->ReferenceNo}}">
                                            </div>
                                            
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
                                                                name="VHNO"
                                                                value="{{ $invoice_mst[0]->InvoiceMasterID }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mb-1 row">
                                                        <div class="col-sm-3">
                                                            <label class="col-form-label" for="email-id">Date</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            {{-- <input type="text" name="Date"  autocomplete="off" class="form-control" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" data-date-container="#datepicker21" data-provide="datepicker" data-date-autoclose="true" value=""> --}}

                                                            <input type="date" name="Date" class="form-control"
                                                                value="{{ $invoice_mst[0]->Date }}">


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

                                                            {{-- <input type="text" name="DueDate"  autocomplete="off" class="form-control" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" data-date-container="#datepicker22" data-provide="datepicker" data-date-autoclose="true"  value="{{$invoice_mst[0]->DueDate}}"> --}}
                                                            <input type="date" name="DueDate" class="form-control"
                                                                value="{{ $invoice_mst[0]->DueDate }}">

                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="PaymentMode"
                                                    vlaue="{{ $invoice_mst[0]->PaymentMode }}">
                                                {{-- <div class="col-12">
                <div class="mb-1 row">
                  <div class="col-sm-3">
                    <label class="col-form-label" for="password">Payment Mode </label>
                  </div>
                  <div class="col-sm-9">
                    <select name="PaymentMode" id="PaymentMode" class="form-select">
                  <option value="Cash" {{($invoice_mst[0]->PaymentMode=='Cash') ? 'selected=selected':'' }}>Cash</option>
                  <option value="ENBD Bank" {{($invoice_mst[0]->PaymentMode=='ENBD Bank') ? 'selected=selected':'' }}>ENBD Bank</option>
                  <option value="ADCB Bank" {{($invoice_mst[0]->PaymentMode=='ADCB Bank') ? 'selected=selected':'' }}>ADCB Bank</option>
                  <option value="Credit Card" {{($invoice_mst[0]->PaymentMode=='Credit Card') ? 'selected=selected':'' }}>Credit Card</option>
           
                </select>
                  </div>
                </div>
              </div> --}}

                                                <div class="col-12">
                                                    <div class="mb-1 row">
                                                        <div class="col-sm-3">
                                                            <label class="col-form-label" for="password">Salesman </label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <select name="SalemanID" id="SalemanID" class="form-select">
                                                                <?php foreach ($saleman as $key => $value): ?>

                                                                <option value="{{ $value->UserID }}"
                                                                    {{ $value->UserID == $invoice_mst[0]->UserID ? 'selected=selected' : '' }}>
                                                                    {{ $value->FullName }}</option>
                                                                <?php endforeach ?>



                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="mb-1 row">
                                                        <div class="col-sm-3">
                                                            <label class="col-form-label" for="password">Source </label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="Source" id="Source"
                                                                class="form-control" value="{{ $invoice_mst[0]->Source }}">
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
                                            <table>
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

                                                    @foreach ($invoice_det as $key => $value1)
                                                        <?php $no = $key + 1; ?>
                                                        <tr class="p-3">
                                                            <td class="p-1 bg-light borde-1 border-light"><input
                                                                    class="case" type="checkbox" /></td>
                                                            <td>

                                                                <select name="ItemID0[]" id="ItemID0_{{ $no }}"
                                                                    class="form-select select2 form-control-sm item-select"
                                                                    style="width:100%">
                                                                    @foreach ($items as $key => $value)
                                                                        <option value="{{ $value->ItemID }}"
                                                                            data-tax="{{ $value->Percentage ?? 0 }}"
                                                                            {{ $value->ItemID == $value1->ItemID ? 'selected=selected' : '' }}>
                                                                            {{ $value->ItemCode }}-{{ $value->ItemName }}-{{ $value->Percentage }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <select name="SupplierID[]"
                                                                    id="SupplierID_{{ $no }}"
                                                                    class=" form-select select2"
                                                                    onchange="ajax_balance(this.value);"
                                                                    style="width:100%">
                                                                    @foreach ($supplier as $key => $value)
                                                                        <option value="{{ $value->SupplierID }}"
                                                                            {{ $value->SupplierID == $value1->SupplierID ? 'selected=selected' : '' }}>
                                                                            {{ $value->SupplierName }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="PaxName[]"
                                                                    id="PaxName_{{ $no }}"
                                                                    class=" form-control " autocomplete="off"
                                                                    value="{{ $value1->PaxName }}">
                                                                <input type="text" name="TicketNo[]"
                                                                    id="TicketNo_{{ $no }}"
                                                                    class="form-control" value="{{ $value1->TicketNo }}"
                                                                    placeholder="Ticket No">

                                                            </td>

                                                            <td>
                                                                <input type="text" name="Sector[]"
                                                                    id="Sector_{{ $no }}"
                                                                    class=" form-control " autocomplete="off"
                                                                    value="{{ $value1->Sector }}" placeholder="Sector">
                                                                <input type="text" name="PNR[]"
                                                                    id="PNR_{{ $no }}"
                                                                    class=" form-control " autocomplete="off"
                                                                    value="{{ $value1->PNR }}">
                                                            </td>

                                                            <td>
                                                                <input type="number" name="Fare[]"
                                                                    id="Fare_{{ $no }}"
                                                                    class=" form-control row-calculation" autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" value="{{ $value1->Fare }}">
                                                                <input type="text" name="Passport[]"
                                                                    id="Passport_{{ $no }}"
                                                                    class="form-control" value="{{ $value1->Passport }}"
                                                                    placeholder="Passport #">

                                                            </td>

                                                            <td class="d-none">
                                                                <input type="text" name="RefNo[]"
                                                                    id="RefNo_{{ $no }}"
                                                                    class="form-control     " autocomplete="off"
                                                                    value="{{ $value1->RefNo }}">
                                                            </td>

                                                            <td class="d-none">
                                                                <input type="text" name="VisaType[]"
                                                                    id="VisaType_{{ $no }}"
                                                                    class="   form-control " autocomplete="off"
                                                                    value="{{ $value1->VisaType }}">
                                                            </td>




                                                            <td>
                                                                 <input readonly type="number" name="TaxPer[]"
                                                                    id="TaxPer_{{ $no }}"
                                                                    class=" form-control " autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" value="{{ $value1->TaxPer }}">
                                                                <input readonly type="number" name="Taxable[]"
                                                                    id="Taxable_{{ $no }}"
                                                                    class=" form-control   " autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" value="{{ $value1->Taxable }}">
                                                               
                                                            </td>
                                                            <td>
                                                                <input type="number" name="Service[]"
                                                                    id="Service_{{ $no }}"
                                                                    class=" form-control row-calculation" autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" value="{{ $value1->Service }}">
                                                                <input type="number" name="Discount[]"
                                                                    id="discount_{{ $no }}"
                                                                    class=" form-control row-calculation" autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" value="{{ $value1->Discount }}"
                                                                    placeholder="Discount">
                                                            </td>
                                                            <td class="d-none">
                                                                <input type="number" name="OPVAT[]"
                                                                    id="OPVAT_{{ $no }}" class=" form-control "
                                                                    autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" value="{{ $value1->OPVAT }}">
                                                            </td>
                                                            <td class="d-none">
                                                                <input type="number" name="IPVAT[]"
                                                                    id="IPVAT_{{ $no }}" class=" form-control "
                                                                    autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" value="{{ $value1->IPVAT }}">
                                                            </td>






                                                            <td>
                                                                <input readonly type="number" name="ItemTotal[]"
                                                                    id="total_{{ $no }}"
                                                                    class=" form-control totalLinePrice "
                                                                    autocomplete="off"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    step="0.01" value="{{ $value1->Total }}">
                                                                <input type="date" name="DepartureDate[]"
                                                                    id="DepartureDate_{{ $no }}"
                                                                    class="form-control"
                                                                    value="{{ $value1->DepartureDate }}">
                                                            </td>
                                                        </tr>


                                                        <script>
                                                            $('#SupplierID_' + {{ $no }}).select2();
                                                        </script>
                                                    @endforeach

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


                                            <textarea class="form-control" rows='5' name="Note" id="notes" placeholder="Your Notes">{{ $invoice_mst[0]->Note }}</textarea>




                                            <div class="mt-2"><button type="submit"
                                                    class="btn btn-success w-lg float-right">Save</button>
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
                                                            onkeypress="return IsNumeric(event);" ondrop="return false;"
                                                            onpaste="return false;">
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
                                                        <span class="input-group-text bg-light">{{ env('APP_CURRENCY') }}</span>
                                                        <input type="number" name="Total" class="form-control"
                                                            step="0.01" id="totalAftertax" placeholder="Total"
                                                            onkeypress="return IsNumeric(event);" ondrop="return false;"
                                                            onpaste="return false;" value="{{ $invoice_mst[0]->Total }}">
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
                                                                <option value="">Select
                                                                    {{ $invoice_mst[0]->BankName }}</option>
                                                                <option value="Nomod"
                                                                    {{ $invoice_mst[0]->BankName == 'Nomod' ? 'selected' : '' }}>
                                                                    Nomod</option>
                                                                <option value="Tabbay"
                                                                    {{ $invoice_mst[0]->BankName == 'Tabbay' ? 'selected' : '' }}>
                                                                    Tabbay</option>
                                                                <option value="Tamara"
                                                                    {{ $invoice_mst[0]->BankName == 'Tamara' ? 'selected' : '' }}>
                                                                    Tamara</option>

                                                            </select>
                                                        </div>

                                                        <!-- Percentage Input -->
                                                        <div class="col-md-4">
                                                            <div class="input-group">
                                                                <input type="number" name="Percentage"
                                                                    class="form-control" placeholder="%" step="0.01"
                                                                    min="0" max="100"
                                                                    value="{{ number_format($invoice_mst[0]->Percentage, 2) }}"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                        </div>

                                                        <!-- Value Input -->
                                                        <div class="col-md-4">
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-light">{{ env('APP_CURRENCY') }}</span>
                                                                <input type="number" name="PercentageValue"
                                                                    id="PercentageValue" class="form-control"
                                                                    step="0.01" placeholder="Amount"
                                                                    onkeypress="return IsNumeric(event);"
                                                                    ondrop="return false;" onpaste="return false;"
                                                                    value="{{ number_format($invoice_mst[0]->BankCharges, 2) }}">
                                                            </div>
                                                        </div>



                                                    </div>
                                                </div>

                                                <div class="form-group mt-2">

                                                    <label>
                                                        <H5>Grand Total: &nbsp;</H5>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">{{ env('APP_CURRENCY') }}</span>
                                                        <input type="number" class="form-control" name="GrandTotal"
                                                            id="GrandTotal" placeholder="Amount Due"
                                                            onkeypress="return IsNumeric(event);" ondrop="return false;"
                                                            onpaste="return false;" step="0.01"
                                                            value="{{ $invoice_mst[0]->GrandTotal }}">
                                                    </div>
                                                </div>




                                                <div class="form-group mt-1">
                                                    <label>
                                                        <h5>Amount Paid: &nbsp;</h5>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">{{ env('APP_CURRENCY') }}</span>
                                                        <input readonly type="number" class="form-control"
                                                            id="amountPaid-" name="amountPaid" placeholder="Amount Paid"
                                                            onkeypress="return IsNumeric(event);" ondrop="return false;"
                                                            onpaste="return false;" step="0.01"
                                                            value="{{ $invoice_mst[0]->Paid }}">
                                                    </div>
                                                </div>

                                                <div class="form-group mt-1">

                                                    <label>
                                                        <H5>Amount Due: &nbsp;</H5>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">{{ env('APP_CURRENCY') }}</span>
                                                        <input type="number" class="form-control amountDue"
                                                            name="amountDue" id="amountDue" placeholder="Amount Due"
                                                            onkeypress="return IsNumeric(event);" ondrop="return false;"
                                                            onpaste="return false;" step="0.01"
                                                            value="{{ $invoice_mst[0]->Balance }}">
                                                    </div>
                                                </div>

                                                <div class="form-group mt-1">

                                                    <label>
                                                        <H5>Voucher: &nbsp;</H5>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">VHNO</span>
                                                        <input readonly type="text" class="form-control"
                                                            name="Voucher" placeholder="Voucher"
                                                            value="{{ $invoice_mst[0]->Voucher }}">
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
















    <script src="{{ asset('assets/invoice/js/jquery-1.11.2.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/bootstrap-datepicker.js') }}"></script>
    <!-- <script src="js/ajax.js"></script> -->

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>

    


    @include('invoice_js')

    <!-- ajax trigger -->



    <script src="{{ asset('assets/js/jquery-3.6.0.js') }}" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
        crossorigin="anonymous"></script>

    <script type="text/javascript">
        //<![CDATA[


        $(function() {
            $('#WalkinCustomer').hide();
            $('#PartyID').change(function() {

                if (this.options[this.selectedIndex].value == '1') {
                    // alert('dd');

                    $('#WalkinCustomer').show();
                    $('#1WalkinCustomerName').focus();

                } else {
                    $('#WalkinCustomer').hide();
                    $('#1WalkinCustomerName').val(0);
                    $('#1WalkinCustomerMobile').val(0);
                }
            });
        });


        //]]>
    </script>

    <script>
        // Get the elements
        const percentageInput = document.querySelector('input[name="Percentage"]');
        const percentageValueInput = document.getElementById('PercentageValue');
        const totalAfterTaxInput = document.getElementById('totalAftertax');
        const grandTotalInput = document.getElementById('GrandTotal');
        const amountPaidInput = document.querySelector('input[name="amountPaid"]');
        const amountDueInput = document.getElementById('amountDue');

        // Function to calculate and update values
        function updateBankCharges(source = "") {
            const total = parseFloat(totalAfterTaxInput.value) || 0;
            let percentage = parseFloat(percentageInput.value) || 0;
            let bankCharge = parseFloat(percentageValueInput.value) || 0;

            if (source === "percentage") {
                // User typed in percentage → calculate value
                bankCharge = (percentage / 100) * total;
                percentageValueInput.value = bankCharge.toFixed(2);
            } else if (source === "value") {
                // User typed in {{ env('APP_CURRENCY') }} value → calculate percentage
                if (total > 0) {
                    percentage = (bankCharge / total) * 100;
                    percentageInput.value = percentage.toFixed(2);
                }
            } else {
                // Default: recalc from percentage
                bankCharge = (percentage / 100) * total;
                percentageValueInput.value = bankCharge.toFixed(2);
            }

            // Update Grand Total
            const grandTotal = total + bankCharge;
            grandTotalInput.value = grandTotal.toFixed(2);

            // Get amount paid
            const amountPaid = parseFloat(amountPaidInput.value) || 0;

            // Update Amount Due
            const amountDue = grandTotal - amountPaid;
            amountDueInput.value = amountDue.toFixed(2);
        }

        // Trigger calculation on input changes
        percentageInput.addEventListener('input', () => updateBankCharges("percentage"));
        percentageValueInput.addEventListener('input', () => updateBankCharges("value"));
        totalAfterTaxInput.addEventListener('input', () => updateBankCharges("total"));
        amountPaidInput.addEventListener('input', () => updateBankCharges("paid"));

        // Run on load
        window.addEventListener('DOMContentLoaded', () => updateBankCharges("init"));
    </script>





    <!-- END: Content-->

@endsection
