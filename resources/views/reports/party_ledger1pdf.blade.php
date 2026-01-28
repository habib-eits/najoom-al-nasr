<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{$pagetitle}}</title>
    <style type="text/css">
<!--
.style1 {
	font-size: 18px;
	font-weight: bold;
}
body,td,th {
	font-size: 13px;
}
-->


    </style>


    @php
  
$company = DB::table('company')->first();

@endphp


<meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
<body>
<div align="center" class="style1">{{$company->Name}}</div>
<div align="center">{{$party[0]->PartyName}} - {{$party[0]->PartyID}}</div>
<div align="center">Contact : {{$party[0]->Phone}}</div>
<div align="center">From {{session::get('StartDate')}} TO {{session::get('EndDate')}}
    </div>
 
        <p>
          <?php 
            $DrTotal=0;
            $CrTotal=0;
          
		       ?>
          @if(count($journal)>0)    
          <table width="100%" border="1" align="center" cellpadding="3" style="border-collapse: collapse;" >
          
              <thead style="background-color: rgb(166, 166, 166);">
          <th class="col-md-1 text-center">DATE</th>
          <th class="col-md-1 text-center" >VHNO</th>
          <th class="col-md-1 text-center">Type</th>
          <th class="col-md-5 text-center">Description</th>
          <th class="col-md-1 text-center">DR</th>
          <th class="col-md-1 text-center">CR</th>
          <th class="col-md-1 text-center">Balance</th>
           </thead>
          
          <tbody>
            <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>Opending Balance</td>
            <td></td>
            <td></td>
            <td style="text-align: right;">{{$sql[0]->Balance}}</td>
          </tr>
          @foreach ($journal as $key =>$value)
           <tr>
           <td class="text-center">{{dateformatman($value->Date)}}</td>
           <td class="text-center">{{$value->VHNO}}</td>
           <td class="text-center">{{$value->JournalType}}</td>
           <td >{{$value->Narration}}</td>
           <td style="text-align: right;"><div> {{($value->Dr==0) ? '' : number_format($value->Dr,2)}}</div></td>
           <td style="text-align: right;"><div> {{($value->Cr==0) ? '' : number_format($value->Cr,2)}}</div></td>
              <td style="text-align: right;">
               

               <?php 

if(!isset($balance)) { 

             $balance  =  $sql[0]->Balance + ($value->Dr-$value->Cr);
             $DrTotal = $DrTotal+$value->Dr;
             $CrTotal = $CrTotal+$value->Cr;
             echo $balance;


}
else
{
  $balance = $balance + ($value->Dr-$value->Cr);
  $DrTotal = $DrTotal+$value->Dr;
             $CrTotal = $CrTotal+$value->Cr;
  echo $balance;
}
              ?> 
{{($balance>0) ? "DR" : "CR"}}
             </td>
           </tr>
           @endforeach   
          <tr  style="font-weight: bolder;">
              
           <td></td>
           <td></td>
           <td>TOTAL</td>
            <td ></td>
           <td style="text-align: right;">{{number_format($DrTotal,2)}}</td>
           <td style="text-align: right;">{{number_format($CrTotal,2)}}</td>
            
            <td style="text-align: right;" class="text-end fw-bolder">{{ number_format($balance, 2) }} {{ ($balance > 0) ? "DR" : "CR" }}</td>
          </tr>
           </tbody>
           </table>
          
           @else
             <p class=" text-danger">No data found</p>
           @endif
		   
		   
</body>
</html>