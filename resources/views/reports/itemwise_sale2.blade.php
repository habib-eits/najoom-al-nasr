@extends('template.tmp')

@section('title', $pagetitle)
 

@section('content')

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


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
                                 
                            <div >
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

           
           
<div class="row ">
    <div class="col-md-3">
        <div class="page-title-box">
            <h4 class="mb-sm-0 font-size-18 pt-3">Itemwise Sale</h4>
        </div>
    </div>
    
    <div class="col-md-9">
        <form action="{{URL('/ItemWiseSale2')}}" method="post" name="form1" id="form1" class="form-inline w-100 d-flex align-items-center">
            @csrf

     <div class="row  p-2 rounded-3 w-100">
               <div class="row  ">

                <!-- Date Range Selector -->
                <div class="col-md-3">
                    <label for="dateRangeSelector" class="form-label">Select Date Range</label>
                    <select id="dateRangeSelector" name="dateRangeSelector" class="form-select">
                        <option value="">Select Date Range</option>
                        <option value="Today">Today</option>
                        <option value="Yesterday">Yesterday</option>
                        <option value="This Week">This Week</option>
                        <option value="This Month">This Month</option>
                        <option value="This Quarter">This Quarter</option>
                        <option value="This Year">This Year</option>
                        <option value="Year to Date">Year to Date</option>
                        <option value="Previous Week">Previous Week</option>
                        <option value="Previous Month">Previous Month</option>
                        <option value="Previous Quarter">Previous Quarter</option>
                        <option value="Previous Year">Previous Year</option>
                        <option value="Custom Range">Custom Range</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div class="col-md-3">
                    <label for="StartDate" class="form-label">Start Date</label>
                    <input type="date" id="StartDate" name="StartDate" class="form-control" value="{{ request()->StartDate }}">
                </div>

                <!-- End Date -->
                <div class="col-md-3">
                    <label for="EndDate" class="form-label">End Date</label>
                    <input type="date" id="EndDate" name="EndDate" class="form-control" value="{{ request()->EndDate }}">
                </div>

                <!-- Submit Button -->
                <div class="col-md-3 text-end mt-4">
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>

            </div>
     </div>
        </form>
    </div>
</div>
         
            
  <div class="card">
      <div class="card-body">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
    
  
    <tr>
      <td width="50%">From {{dateformatman2(request()->StartDate)}} - {{dateformatman2(request()->EndDate)}}</td>
    <td width="50%"><div align="right">DATED: {{date('d-m-Y')}}</div></td>
    
    </tr>
  </table>
  <div class="table-responsive">
  <table class="table table-bordered table-striped  table-sm" id="salesTable">
    <thead>
    <tr class="bg-light">
      <th width="5%">S.NO</th>
      <th width="30%">ITEM NAME</th>
 <th width="10%" class="text-center">NO OF SALES</th>
<th width="10%" class="text-center">TOTAL INVOICE</th>
<th width="10%" class="text-center">PROFIT</th>
     </tr>
  </thead>
 
<?php   

$total=0;
$profit=0;
$invoice=0;

 ?>
  <tbody>
@foreach ($today_sale as $key => $value)
     

     <?php  

      $total +=$value->Total;
      $profit +=$value->Profit;
      $invoice +=$value->Invoice;

      ?>

    <tr>
      <td><div align="center">{{$key+1}}.</div></td>
      <td>  <a href="{{URL('/InvoiceDetailList').'/'.$value->ItemID.'/'.request()->StartDate.'/'.request()->EndDate}}" target="_blank">{{$value->ItemName}}</a></td>
      <td align="center">{{$value->Total}}</td>
      <td align="center">{{number_format($value->Invoice,2)}}</td>
      <td><div align="center">{{ $value->Profit > 0 ? number_format($value->Profit, 2) : '' }}</div></td>
       
    </tr>
@endforeach
 </tbody>
   <tfoot style="font-weight: bolder;">

<tr style="font-weight: bolder;">
  <td colspan="2" class="text-center" ><strong>Grand Total</strong></td>
  <td class="text-center">{{number_format($total)}}</td>
  <td class="text-center">{{number_format($invoice,2)}}</td>
  <td class="text-center">{{number_format($profit,2)}}</td>
 
</tr>
</tfoot>
  </table>       
      </div>
      </div>
  </div>
  
  </div>
</div>

        </div>
      </div>
    </div>
    <!-- END: Content-->
 <script>
$(document).ready(function() {
    $('#salesTable').DataTable({
        "paging": false,       // disable pagination
        "info": false,         // disable info text
        "ordering": true,      // enable sorting
        "searching": false     // disable search bar
    });
});
</script>

<script>
        $(document).ready(function () {
            // Handle the date range selection
            $('#dateRangeSelector').on('change', function () {
                let range = $(this).val();
                let startDate = null;
                let endDate = null;

                switch (range) {
                    case "Today":
                        startDate = moment().format("YYYY-MM-DD");
                        endDate = moment().format("YYYY-MM-DD");
                        break;
                    case "Yesterday":
                        startDate = moment().subtract(1, "days").format("YYYY-MM-DD");
                        endDate = startDate;
                        break;
                    case "This Week":
                        startDate = moment().startOf("week").format("YYYY-MM-DD");
                        endDate = moment().endOf("week").format("YYYY-MM-DD");
                        break;
                    case "This Month":
                        startDate = moment().startOf("month").format("YYYY-MM-DD");
                        endDate = moment().endOf("month").format("YYYY-MM-DD");
                        break;
                    case "This Quarter":
                        startDate = moment().startOf("quarter").format("YYYY-MM-DD");
                        endDate = moment().endOf("quarter").format("YYYY-MM-DD");
                        break;
                    case "This Year":
                        startDate = moment().startOf("year").format("YYYY-MM-DD");
                        endDate = moment().endOf("year").format("YYYY-MM-DD");
                        break;
                    case "Year to Date":
                        startDate = moment().startOf("year").format("YYYY-MM-DD");
                        endDate = moment().format("YYYY-MM-DD");
                        break;
                    case "Previous Week":
                        startDate = moment().subtract(1, "week").startOf("week").format("YYYY-MM-DD");
                        endDate = moment().subtract(1, "week").endOf("week").format("YYYY-MM-DD");
                        break;
                    case "Previous Month":
                        startDate = moment().subtract(1, "month").startOf("month").format("YYYY-MM-DD");
                        endDate = moment().subtract(1, "month").endOf("month").format("YYYY-MM-DD");
                        break;
                    case "Previous Quarter":
                        startDate = moment().subtract(1, "quarter").startOf("quarter").format("YYYY-MM-DD");
                        endDate = moment().subtract(1, "quarter").endOf("quarter").format("YYYY-MM-DD");
                        break;
                    case "Previous Year":
                        startDate = moment().subtract(1, "year").startOf("year").format("YYYY-MM-DD");
                        endDate = moment().subtract(1, "year").endOf("year").format("YYYY-MM-DD");
                        break;
                    case "Custom Range":
                        startDate = ""; // Let user manually set dates
                        endDate = "";
                        break;
                }

                // Populate the date fields
                $('#StartDate').val(startDate);
                $('#EndDate').val(endDate);
            });
        });
    </script>
  @endsection