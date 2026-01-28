@extends('tmp')

@section('title', $pagetitle)


@section('content')




 



 
<div class="main-content">

  <div class="page-content">
    <div class="container-fluid">




      <!-- start page title -->
      <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Saleman Ticket Register</h4>



          </div>
        </div>
      </div>


      <div class="card">

        <div class="card-body">
          <!-- enctype="multipart/form-data" -->
          <form action="{{URL('/SalemanTicketRegister1')}}" method="post" name="form1" id="form1"> {{csrf_field()}}


 @include('components.start_end_date')




        </div>
        <div class="card-footer bg-light">
          <button type="submit" class="btn btn-success w-lg float-right" id="online">Submit</button>
        </div>
      </div>
              @if (Session::get('error'))

<div class="alert alert-{{ Session::get('class') }} p-1" id="success-alert">

  {{ Session::get('error') }}
 

@endif
      </form>
    </div>
  </div>

</div>
</div>
</div>
<!-- END: Content-->

@endsection