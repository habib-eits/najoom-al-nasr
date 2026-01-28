@extends('tmp')

@section('title', $pagetitle)


@section('content')


<div class="main-content">

  <div class="page-content">
    <div class="container-fluid">


      <div class="card shadow-sm">
        <div class="card-body">
          <!-- enctype="multipart/form-data" -->
          <form action="{{URL('/PartyBalance1')}}" method="post" name="form1" id="form1"> {{csrf_field()}}








            <div class="col-md-4">
              <div class="mb-1">
                <label for="basicpill-firstname-input">Parties</label>
                <select name="PartyID" id="" class="select2 form-select" id="select2-basic">
                  <option value="">All Parties</option>
                  <?php foreach ($party as $key => $value): ?>
                      <option value="{{$value->PartyID}}">{{$value->PartyID}}-{{$value->PartyName}}-{{$value->Phone}}</option>

                  <?php endforeach ?>
                </select>
              </div>
            </div>


            <div class="col-md-4">
              <div class="mb-0">
                <label for="basicpill-firstname-input"></label>
                <select name="ReportType" id="" class="  form-select" id="select2-basic">
                  <option value="D">Debitor Customer</option>
                  <option value="C">Creditor Customer</option>

                </select>
              </div>
            </div>

              {{-- 
                Render a component for selecting start and end dates.
                file path: resources\views\components\start-end-date.blade.php 
              --}}
            
 @include('components.start_end_date')








        </div>
        <div class="card-footer bg-light">
          <button type="submit" class="btn btn-success w-lg float-right">Submit</button>
          <button type="submit" class="btn btn-success w-lg float-right" id="pdf">PDF</button>
          <a href="{{URL('/')}}" class="btn btn-secondary w-lg float-right">Cancel</a>
        </div>
        
      </div>
      </form>
    </div>
  </div>

</div>
</div>
</div>
<!-- END: Content-->

<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
  crossorigin="anonymous"></script>

<script>
  $(document).ready(function(){
    $('#pdf').click(function(event){
        event.preventDefault();
        $('#form1').removeAttr('target');
        $('#form1').attr('action', '{{URL("/PartyBalance1PDF")}}');
        $('#form1').attr('target', '_blank');
        $('#form1').submit();
    });
    $('#online').click(function(event){
        event.preventDefault();
        $('#form1').removeAttr('target');
        $('#form1').attr('action', '{{URL("/PartyBalance1")}}');
        $('#form1').submit();
    });
});
</script>


@endsection