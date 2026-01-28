@extends('template.tmp')

@section('title', $pagetitle)
 

@section('content')

 


<div class="main-content">

 <div class="page-content">
 <div class="container-fluid">

<script>
       function delete_voucher(id) {        


        url = '{{URL::TO('/')}}/VoucherDelete/'+ id;
        
    
       
            jQuery('#staticBackdrop').modal('show', {backdrop: 'static'});
            document.getElementById('delete_link').setAttribute('href' , url);
         
    }
</script>


    <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Voucher</h4>
                                         <div class="page-title-right ">
                                       
                                       <div class="btn-group  shadow-sm dropstart">
                                                <button type="button" class="btn btn-primary waves-effect waves-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="mdi mdi-chevron-left"></i> Add New
                                                </button>
                                                <div class="dropdown-menu" style="margin: 0px;">
                                                    <a class="dropdown-item" href="{{URL('/VoucherCreate/BP')}}">BP-Bank Payment</a>
                                                    <a class="dropdown-item" href="{{URL('/VoucherCreate/BR')}}">BR-Bank Receipt</a>
                                                     <div class="dropdown-divider"></div>
                                                     <a class="dropdown-item" href="{{URL('/VoucherCreate/CP')}}">CP-Cash Payment</a>
                                                      <a class="dropdown-item" href="{{URL('/VoucherCreate/CR')}}">CR-Cash Receipt</a>
                                                        <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="{{URL('/JV')}}">Journal Voucher</a>
                                                </div>
                                            </div>
                                    </div>  
                                           
                                      

                                    

                                </div>
                            </div>
                        </div>


          <div class="row">
  <div class="col-12">
  
  @if (session('error'))

 <div class="alert alert-{{ Session::get('class') }} p-1" id="success-alert">
                    
                   {{ Session::get('error') }}  
                </div>

@endif

 @if (count($errors) > 0)
                                 
                            <div >
                <div class="alert alert-danger pt-3 pl-0   border-3">
                   <p class="font-weight-bold"> There were some problems with your input.</p>
                    <ul>
                        
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>

                        @endforeach
                    </ul>
                </div>
                </div>
 
            @endif

 
  <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-md-2">
                                    <label for="Voucher">Voucher:</label>
                                    <input type="text" id="Voucher" name="Voucher" class="form-control">
                                </div> 

                                <div class="col-md-2">
                                    <label for="party_name">Vouchre Type</label>
                                    <input type="text" id="VoucherType" name="VoucherType" class="form-control">
                                </div>
                                 

                                <div class="col-md-2">
                                    <label for="date">From:</label>
                                    <input type="date" id="startdate" name="start" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label for="date">To:</label>
                                    <input type="date" id="enddate" name="end" class="form-control">
                                </div>
                               
                               <div class="col-md-4 d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-danger w-md mt-4" id="filter-button">
                                        <i class="mdi mdi-filter"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-primary w-md mt-4" id="reset-dates-button">
                                        <i class="fas fa-sync-alt"></i> Reset
                                    </button>
                                </div>  
                            </div>
                        </div>
                    </div>           
  <div class="card">
     
      <div class="card-body">
<div class="table-responsive">
    <table id="student_table" class="table table-striped table-sm " style="width:100%">
        <thead>
            <tr>
                <th>Voucher#</th>
                <th>Voucher</th>
                <th>Type</th>
                <th>Date</th>
                <th class="col-md-5">Narration</th>
                
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
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


     <script src="https://code.jquery.com/jquery-3.6.0.js"
        integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>



<script type="text/javascript">
$(document).ready(function() {
     // Initialize DataTable with filter parameters
        var table = $('#student_table').DataTable({
        "processing": true,
        "serverSide": true,
         "ajax": {
            "url": "{{ url('ajax_voucher') }}",
            "data": function (d) {
                d.Voucher = $('#Voucher').val();
                d.VoucherType = $('#VoucherType').val();
                
                d.startdate = $('#startdate').val();
                d.enddate = $('#enddate').val();
            }
        },

        "columns":[
            { "data": "VoucherMstID" },
            { "data": "Voucher" },
            { "data": "VoucherTypeName" },
            { 
                    "data": "Date",
                    "render": function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            var date = new Date(data);
                            var day = ("0" + date.getDate()).slice(-2);
                            var month = ("0" + (date.getMonth() + 1)).slice(-2);
                            var year = date.getFullYear();
                            return day + '/' + month + '/' + year;
                        }
                        return data;
                    }
                },
            { "data": "Narration" },
            
 

            { "data": "Amount" , render: $.fn.dataTable.render.number(',', '.', 2, '')},
            { "data": "action" },
        ],
        "order": [[3, 'desc']],
     });

       // Handle filter button click
    $('#filter-button').on('click', function() {

        table.draw();
    });

   $('#reset-dates-button').on('click', function() {
        // Clear all input fields
        $('#Voucher').val('');
        $('#VoucherType').val('');
        $('#startdate').val('');
        $('#enddate').val('');

        // Optionally, reset any filters in your DataTable
        table.search('').columns().search('').draw();
    });

      


});
</script>

   <script>
        $(document).ready(function() {
    $('#startdate').on('change', function() {
        var startDate = $(this).val();
        var endDate = $('#enddate').val();

            if (!endDate || new Date(endDate) < new Date(startDate)) {
                $('#enddate').val(startDate);
            }


        $('#enddate').attr('min', startDate);
    });
});



    </script>
   
  @endsection