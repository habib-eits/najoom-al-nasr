@extends('template.tmp')

@section('title', $pagetitle)
 

@section('content')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>


<div class="main-content">

 <div class="page-content">
 <div class="container-fluid">
  <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-print-block d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Party Balances</h4>
                                        <strong class="text-end"><div align="center">{{(request()->ReportType=='C') ? 'Creditor Customers' : 'Debitor Customers' }}
                                            </div></strong> 
                     

                                </div>
                            </div>
                        </div>
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

            
            <?php 
            $DrTotal=0;
            $CrTotal=0;
 






             ?>


<div class="card">
    <div class="card-body">

                  <form action="{{URL('/PartyBalance1')}}" method="post" name="form1" id="form1"> 
                    @csrf



        <div class="row align-items-end">
    <!-- Report Type -->
    <div class="col-md-3">
        <label for="ReportType">Report Type</label>
        <select name="ReportType" id="ReportType" class="form-select">
            <option value="D">Debitor Customer</option>
            <option value="C">Creditor Customer</option>
        </select>
    </div>

    <!-- Date Range Selector -->
    <div class="col-md-3">
        <label for="dateRangeSelector">Select Date Range</label>
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
    <div class="col-md-2">
        <label for="StartDate">Start Date</label>
        <input type="date" id="StartDate" name="StartDate" class="form-control" value="2025-08-01">
    </div>

    <!-- End Date -->
    <div class="col-md-2">
        <label for="EndDate">End Date</label>
        <input type="date" id="EndDate" name="EndDate" class="form-control" value="2025-08-22">
    </div>

    <!-- Submit Button -->
    <div class="col-md-2 text-end">
        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
    </div>
</div>


                  </form>
    </div>
</div>



  <div class="card">
      <div class="card-body">
          
          <div class="table-responsive">
          

        @if(count($party)>0)
     <table class="table table-sm mt-4" id="salesTable">
    <thead class="bg-light">
        <tr>
            <th width="3%">S.NO</th>
            <th width="5%" style="text-align: center;">CODE</th>
            <th width="10%">NAME</th>
            <th width="10%" style="text-align: center;" >DEBIT</th>
            <th width="10%" style="text-align: center;" >CREDIT</th>
            <th width="10%" style="text-align: center;" >BALANCE</th>
         </tr>
    </thead>
    <tbody>
        @foreach ($party as $key => $value)
            <?php 
                $DrTotal = $DrTotal + $value->Dr;
                $CrTotal = $CrTotal + $value->Cr;
            ?>
            <tr>
                <td><div align="center">{{ $key+1 }}.</div></td>
                <td><div align="CENTER">{{ $value->PartyID }}</div></td>
                <td><a href="{{ URL('/PartySalesLedger3/'.$value->PartyID) }}" target="_blank">{{ $value->PartyName }}</a></td>
                <td><div align="right">{{ number_format($value->Dr, 2) }}</div></td>
                <td><div align="right">{{ number_format($value->Cr, 2) }}</div></td>
                <td><div align="right">{{ number_format(($value->Dr) - $value->Cr, 2) }}</div></td>
               
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
             <td></td>
             <td></td>
            <td><strong>TOTAL</strong></td>
            <td align="right"><strong>{{ number_format($DrTotal, 2) }}</strong></td>
            <td align="right"><strong>{{ number_format($CrTotal, 2) }}</strong></td>
            <td align="right"><strong>{{ number_format(($DrTotal) - ($CrTotal), 2) }}</strong></td>
        </tr>
    </tfoot>
</table>

</div>
@else
<p>No record found</p>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#salesTable').DataTable({
            paging: false,  // Disable pagination (optional)
            ordering: true, // Enable column sorting
            info: true,     // Show table info (optional)
            searching: true,
            dom: 'lfrtip',
            order: [[2, 'asc']] // Set initial sorting on the 4th column (CREDIT) in descending order
        });
    });
</script>

      </div>
  </div>
  
  </div>
</div>

        </div>
      </div>
    </div>
    <!-- END: Content-->

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