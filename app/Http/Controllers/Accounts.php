<?php

namespace App\Http\Controllers;

use PDF;
use URL;

use File;
use Excel;
use Image;
use DateTime;
use Carbon\Carbon;
use App\Mail\SendMail;
// for excel export
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
// end for excel export
use Yajra\DataTables\DataTables;

use Illuminate\Support\Facades\DB;
use App\Exports\SupplierLedgerExcel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Accounts extends Controller
{

  public function __construct()
  {
    if (Session::get('UserID') == 1) {
      echo "null";
    }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */

  public function CheckUserRole1($userid, $tablename, $action)
  {
    // $allow= check_role(Session::get('UserID'),'Petty Cash','List');

    $allow = DB::table('user_role')->where('UserID', $userid)
      ->where('Table', $tablename)
      ->where('Action', $action)
      ->get();

    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
  }

  public function Login()
  {

    // Encrypt the message 'Hello, Universe'.
    // $encrypted = Crypt::encrypt('Hello, Universe');
    // echo $encrypted;
    // echo "<br>";

    // // Decrypt the $encrypted message.
    // $message   = Crypt::decrypt($encrypted);

    // echo $message;
    //         die;


    $company = DB::table('company')->where('CompanyID',1)->get();
    return view('login_1.login',compact('company'));
  }

  public function UserVerify(request $request)
  {
    //

    // dd($request->all());


     $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:2'
        ]);



    $input = $request->only(['email', 'password']);



    $username = $input['email'];
    $password =  $input['password'];

    $data = DB::table('user')->where('Email', '=', $username)
      ->where('Password', '=', $password)
      ->where('Active', '=', 'Yes')
      ->get();



        
     
    if (count($data) > 0) {
      Session::put('FullName', $data[0]->FullName);
      Session::put('UserID', $data[0]->UserID);
      
      Session::put('UserType', $data[0]->UserType);
      Session::put('Type', $data[0]->UserType);
    
           return response()->json([
                'success' => true,
                'message' => 'Welcome to Admin Panel'
            ]);


    } else {

      

           return response()->json([
                'success' => false,
                'message' => 'Email or password is invalid'
            ]);
    }

    // for staff login


  




  }


  

  public  function PettyCash()
  {

    // $data = DB::table('data')->get();

    // $id = DB::table('customer')->where('customer_id',$customer_id)->delete();

    Session::put('menu', 'PettyCash');
    $pagetitle = 'Petty Cash';

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Petty Cash', 'List');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    return view('pettycash', compact('pagetitle'))->with('error', 'Logout Successfully.')->with('class', 'success');
  }

  public function ajax_pettycash(Request $request)
  {
    Session::put('menu', 'PettyCash');
    $pagetitle = 'Petty Cash';
    if ($request->ajax()) {
      $data = DB::table('v_pettycash_master')->orderBy('PettyMstID')->get();
      return Datatables::of($data)
        ->addIndexColumn()

        ->addColumn('action', function ($row) {
          // if you want to use direct link instead of dropdown use this line below
          // <a href="javascript:void(0)"  onclick="edit_data('.$row->customer_id.')" >Edit</a> | <a href="javascript:void(0)"  onclick="del_data('.$row->customer_id.')"  >Delete</a>

          $btn = ' 
          <div class="d-flex align-items-center col-actions">
            <a href="' . URL('/PettyCashEdit/' . $row->PettyMstID) . '"><i class="bx bx-pencil align-middle me-1 text-secondary"></i></a> 
            <a href="' . URL('/PettyCashDelete/' . $row->PettyMstID) . '"><i class="bx bx-trash align-middle me-1 text-secondary"></i></a> 
          </div>';

          //class="edit btn btn-primary btn-sm"
          // <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    return view('invoice', 'pagetitle');
  }

  public  function PettyCashCreate()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Petty Cash', 'Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    Session::put('menu', 'PettyCash');
    $pagetitle = 'Petty Cash';
    $voucher_type = DB::table('voucher_type')->get();
    $items = DB::table('item')->get();
    $chartofaccount = DB::table('chartofaccount')->where(DB::raw('right(ChartOfAccountID,3)'), '<>', 000)->get();
    $supplier = DB::table('supplier')->get();
    $vhno = DB::table('invoice_master')->select(DB::raw('max(InvoiceMasterID)+1 as VHNO'))->get();

    return view('pettycash_create', compact('voucher_type', 'items', 'supplier', 'vhno', 'pagetitle', 'chartofaccount'))->with('error', 'Logout Successfully.')->with('class', 'success');
  }

  public  function PettyCashSave(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Petty Cash', 'Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());
    $invoice_mst = array(
      'PettyVoucher' => $request->input('Voucher'),
      'ChOfAcc' => $request->input('ChartOfAcc'),
      'Date' => $request->input('VHDate'),
      'Narration' => $request->input('Narration_mst'),
      'Credit' => $request->input('TotalDebit'),

    );

    // dd($invoice_mst);

    // $id= DB::table('')->insertGetId($data);

    $id = DB::table('pettycash_master')->insertGetId($invoice_mst);

    for ($i = 0; $i < count($request->ChartOfAcc2); $i++) {
      $invoice_det = array(
        'PettyMstID' => $id,
        'PettyVoucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => $request->ChartOfAcc2[$i],
        'Narration' => $request->Narration[$i],
        'Invoice' => $request->Invoice[$i],
        'RefNo' => $request->RefNo[$i],
        'Debit' => $request->Debit[$i],
        'FromChOfAcc' => $request->input('ChartOfAcc'),

      );

      // dd($invoice_det);  

      $iddd = DB::table('pettycash_detail')->insert($invoice_det);
    }

    return redirect('PettyCash')->with('error', 'Record Saved')->with('class', 'success');
  }

  public  function PettyCashEdit($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Petty Cash', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'PettyCash');
    $pagetitle = 'Petty Cash';
    $chartofaccount = DB::table('chartofaccount')->where('L3', '!=', 'L2')->where('L1', '!=', 'L2')->get();
    $pettycash_master = DB::table('pettycash_master')->where('PettyMstID', $id)->get();
    $pettycash_detail = DB::table('pettycash_detail')->where('PettyMstID', $id)->get();

    return view('pettycash_edit', compact('chartofaccount', 'pettycash_master', 'pettycash_detail', 'pagetitle'));
  }

  public  function PettyCashUpdate(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Petty Cash', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());
    $invoice_mst = array(
      'PettyVoucher' => $request->input('PettyVoucher'),
      'ChOfAcc' => $request->input('ChartOfAcc'),
      'Date' => $request->input('VHDate'),
      'Narration' => $request->input('Narration_mst'),
      'Credit' => $request->input('TotalDebit'),

    );

    // dd($invoice_mst);

    // $id= DB::table('')->insertGetId($data);

    $id = DB::table('pettycash_master')->where('PettyMstID', $request->input('PettyMstID'))->update($invoice_mst);

    $id = DB::table('pettycash_detail')->where('PettyMstID', $request->input('PettyMstID'))->delete();

    for ($i = 0; $i < count($request->ChartOfAcc2); $i++) {
      $invoice_det = array(
        'PettyMstID' => $request->input('PettyMstID'),
        'PettyVoucher' => $request->input('PettyVoucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => $request->ChartOfAcc2[$i],
        'Narration' => $request->Narration[$i],
        'Invoice' => $request->Invoice[$i],
        'RefNo' => $request->RefNo[$i],
        'Debit' => $request->Debit[$i],
        'FromChOfAcc' => $request->input('ChartOfAcc'),

      );

      // dd($invoice_det);  

      $idd = DB::table('pettycash_detail')->where('PettyMstID', $request->input('Voucher'))->insert($invoice_det);
    }

    return redirect('PettyCash')->with('error', 'Record Updated')->with('class', 'success');
  }

  // petty udate end 

  public  function JV()
  {
    Session::put('menu', 'Vouchers');
    $pagetitle = 'Vouchers';

    $voucher_type = DB::table('voucher_type')->where('VoucherCode', 'JV')->get();

    $chartofaccount = DB::table('chartofaccount')->where(DB::raw('right(ChartOfAccountID,3)'), '<>', 000)->get();

    $voucher_code = match ('JV') {
      'BP' => '1',
      'BR' => '2',
      'CP' => '4',
      'CR' => '5',
      'JV' => '7',
    };

    $data = DB::table('voucher_master')
      ->select(DB::raw('LPAD(IFNULL(MAX(SUBSTR(Voucher,7)),0)+1,4,0) as vhno'))
      ->where('VoucherCodeID', $voucher_code)
      ->first();

    $vhno = 'JV' . date('ym') . $data->vhno;

    $supplier = DB::table('supplier')->get();
    $party = DB::table('party')->get();

    return view('jv_create', compact('voucher_type', 'chartofaccount', 'supplier', 'vhno', 'pagetitle', 'party', 'vhno'));
  }

  public function PettyCashDelete($id)
  {

    $id = DB::table('pettycash_master')->where('PettyMstID', $id)->delete();
    $id = DB::table('pettycash_detail')->where('PettyMstID', $id)->delete();
    return redirect()->back()->with('error', 'Deleted Successfully.')->with('class', 'success');
  }

  public  function Voucher()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Voucher', 'List');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Vouchers');
    $pagetitle = 'Vouchers';
    $voucher_type = DB::table('voucher_type')->get();
    $chartofaccount = DB::table('chartofaccount')->where('L3', '!=', 'L2')->where('L1', '!=', 'L2')->get();
    $supplier = DB::table('supplier')->get();
    $vhno = DB::table('invoice_master')->select(DB::raw('max(InvoiceMasterID)+1 as VHNO'))->get();

    return view('voucher', compact('voucher_type', 'chartofaccount', 'supplier', 'vhno', 'pagetitle'))->with('error', 'Logout Successfully.')->with('class', 'success');
  }

  public function ajax_voucher(Request $request)
  {



    Session::put('menu', 'Vouchers');
    $pagetitle = 'Vouchers';
    if ($request->ajax()) {
      
          $query = DB::table('v_voucher');

      // Apply filters if they are present in the request
      // if ($request->has('item_name') && !empty($request->item_name)) {
      //     $query->where('ItemName', 'like', '%' . $request->item_name . '%');
      // }

      if ($request->has('Voucher') && !empty($request->Voucher)) {
        $query->where('Voucher', 'like', '%' . $request->Voucher . '%');
      }
      if ($request->has('VoucherType') && !empty($request->VoucherType)) {
        $query->where('VoucherTypeName', 'like', '%' . $request->VoucherType . '%');
      }

      if ($request->has('startdate') && !empty($request->startdate)) {
        $query->whereDate('Date', '>=', $request->startdate);
      }
      if ($request->has('enddate') && !empty($request->enddate)) {
        $query->whereDate('Date', '<=', $request->enddate);
      }

      $data = $query->orderBy('VoucherMstID')->get();

      return Datatables::of($data)
        ->addIndexColumn()

        ->addColumn('action', function ($row) {
          // if you want to use direct link instead of dropdown use this line below
          // <a href="javascript:void(0)"  onclick="edit_data('.$row->customer_id.')" >Edit</a> | <a href="javascript:void(0)"  onclick="del_data('.$row->customer_id.')"  >Delete</a>

          $btn = ' 
 
                       <div class="d-flex align-items-center col-actions">
                     
 
<a href="' . URL('/VoucherView/' . $row->VoucherMstID) . '"><i class="font-size-18 mdi mdi-eye-outline align-middle me-1 text-secondary"></i></a> 

<a href="' . URL('/VoucherEdit/' . $row->VoucherMstID) . '"><i class="font-size-18 mdi mdi-pencil align-middle me-1 text-secondary"></i></a> 

<a href="javascript:void(0)" onclick="delete_voucher(' . $row->VoucherMstID . ')" ><i class="font-size-18 bx bx-trash align-middle me-1 text-secondary"></i></a>

                       </div>';

          //class="edit btn btn-primary btn-sm"
          // <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    return view('invoice', 'pagetitle');
  }

  public  function VoucherCreate($vouchertype)
  {

    $payment_method = 'BP';

    $voucher_code = match ($vouchertype) {
      'BP' => '1',
      'BR' => '2',
      'CP' => '4',
      'CR' => '5',
      'JV' => '7',
    };

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Voucher', 'Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    if ($vouchertype == 'BR') {

      $chartofaccount1 = DB::table('chartofaccount')->where('Category', 'BANK')->get();
    } elseif ($vouchertype == 'BP') {
      $chartofaccount1 = DB::table('chartofaccount')->where('Category', 'BANK')->get();
    } elseif ($vouchertype == 'CR') {
      $chartofaccount1 = DB::table('chartofaccount')->where('Category', 'CASH')->get();
    } elseif ($vouchertype == 'CP') {
      $chartofaccount1 = DB::table('chartofaccount')->where('Category', 'CASH')->get();
    }

    $data = DB::table('voucher_master')
      ->select(DB::raw('LPAD(IFNULL(MAX(SUBSTR(Voucher,7)),0)+1,4,0) as vhno'))
      ->where('VoucherCodeID', $voucher_code)
      ->first();

    $vhno = $vouchertype . date('ym') . $data->vhno;

    Session::put('menu', 'Vouchers');
    $pagetitle = 'Vouchers';

    $voucher_type = DB::table('voucher_type')->where('VoucherCode', $vouchertype)->get();

    $supplier = DB::table('supplier')->get();
    $party = DB::table('party')->orderby('PartyName')->get();

    $chartofaccount = DB::table('chartofaccount')->where(DB::raw('right(ChartOfAccountID,3)'), '<>', 000)
      ->get();

    return view('voucher_create', compact('voucher_type', 'chartofaccount', 'chartofaccount1', 'supplier', 'vhno', 'pagetitle', 'party', 'vhno'))->with('error', 'Logout Successfully.')->with('class', 'success');
  }

  public function VoucherSave(request $request)
  {


     ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Voucher', 'Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());
    $voucher_mst = array(
      'VoucherCodeID' => $request->input('VoucherType'),
      'Voucher' => $request->input('Voucher'),
      'Narration' => $request->input('Narration_mst'),
      'Date' => $request->input('VHDate'),

    );

    // dd($invoice_mst);

    // $id= DB::table('')->insertGetId($data);

    $id = DB::table('voucher_master')->insertGetId($voucher_mst);


    // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => 0,
      'Date' => date('Y-m-d H:i:s'), 
      'Section' => 'Voucher Created', 
      'VHNO' => $request->input('Voucher'), 
      'Narration' => $request->input('Narration_mst'), 
      'Trace' => 301,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 




    if ((substr($request->Voucher, 0, 2) == 'BP') || ((substr($request->Voucher, 0, 2) == 'CP')))

      // start for loop
      for ($i = 0; $i < count($request->ChOfAcc); $i++) {

        $voucher_det_dr = array(
          'VoucherMstID' => $id,
          'Voucher' => $request->input('Voucher'),
          'Date' =>  $request->input('VHDate'),
          'ChOfAcc' => $request->ChOfAcc[$i],
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID[$i],
          'Narration' => $request->Narration[$i],
          'InvoiceNo' => $request->Invoice[$i],
          'RefNo' => $request->RefNo[$i],
          'Debit' => $request->Debit[$i],

        );

        $id1 = DB::table('voucher_detail')->insert($voucher_det_dr);

              // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i],
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher Created', 
      'VHNO' => $request->input('Voucher'), 
      'Narration' => $request->input('Narration_mst') . 'Invoice# ' . $request->Invoice[$i] . $request->RefNo[$i]. ' amount '. ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i], 
      'Trace' => 302,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 

        $voucher_det_cr = array(
          'VoucherMstID' => $id,
          'Voucher' => $request->input('Voucher'),
          'Date' =>  $request->input('VHDate'),
          'ChOfAcc' => $request->ChartOfAccount1,
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID[$i],
          'Narration' => $request->Narration[$i],
          'InvoiceNo' => $request->Invoice[$i],
          'RefNo' => $request->RefNo[$i],
          'Credit' => $request->Debit[$i],

        );

        $id2 = DB::table('voucher_detail')->insert($voucher_det_cr);

              // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i],
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher Created', 
      'VHNO' => $request->input('Voucher'), 
      'Narration' => $request->input('Narration_mst') . 'Invoice# ' . $request->Invoice[$i] . $request->RefNo[$i]. ' amount '. ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i], 
      'Trace' => 303,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 


      }
    // end for each
    else {

      // start for loop
      for ($i = 0; $i < count($request->ChOfAcc); $i++) {

        $voucher_det_dr = array(
          'VoucherMstID' => $id,
          'Voucher' => $request->input('Voucher'),
          'Date' =>  $request->input('VHDate'),
          'ChOfAcc' => $request->ChartOfAccount1,
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID[$i],
          'Narration' => $request->Narration[$i],
          'InvoiceNo' => $request->Invoice[$i],
          'RefNo' => $request->RefNo[$i],
          'Debit' => $request->Debit[$i],

        );

        $id2 = DB::table('voucher_detail')->insert($voucher_det_dr);


                      // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i],
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher creatd', 
      'VHNO' => $request->input('Voucher'), 
      'Narration' =>  $request->Narration[$i] . ' Invoice# ' . $request->Invoice[$i] . $request->RefNo[$i]. ' amount '. ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i], 
      'Trace' => 304,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 

        $voucher_det_cr = array(
          'VoucherMstID' => $id,
          'Voucher' => $request->input('Voucher'),
          'Date' =>  $request->input('VHDate'),
          'ChOfAcc' => $request->ChOfAcc[$i],
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID[$i],
          'Narration' => $request->Narration[$i],
          'InvoiceNo' => $request->Invoice[$i],
          'RefNo' => $request->RefNo[$i],
          'Credit' => $request->Debit[$i],

        );

        $id1 = DB::table('voucher_detail')->insert($voucher_det_cr);

                      // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i],
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher created', 
      'VHNO' => $request->input('Voucher'), 
      'Narration' =>  $request->Narration[$i] . ' Invoice# ' . $request->Invoice[$i] . $request->RefNo[$i]. ' amount '. ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i], 
      'Trace' => 305,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 



      }
      // end for each

    }

    return redirect('Voucher')->with('error', 'Record Saved')->with('class', 'success');
  }

  public  function VoucherEdit($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Voucher', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    Session::put('menu', 'Vouchers');
    $pagetitle = 'Vouchers';
    $voucher_type = DB::table('voucher_type')->get();
    $chartofaccount = DB::table('chartofaccount')->where('L3', '!=', 'L2')->where('L1', '!=', 'L2')->orderby('ChartOfAccountName')->get();
    $supplier = DB::table('supplier')->get();
    $party = DB::table('party')->get();
    $voucher_master = DB::table('voucher_master')->where('VoucherMstID', $id)->get();
    $voucher_detail = DB::table('voucher_detail')->where('VoucherMstID', $id)->get();

    return view('voucher_edit', compact('voucher_type', 'chartofaccount', 'supplier', 'pagetitle', 'voucher_master', 'voucher_detail', 'party'));
  }

  public  function VoucherUpdate(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Voucher', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());
    $voucher_mst = array(
      'VoucherCodeID' => $request->input('VoucherType'),
      'Voucher' => $request->input('Voucher'),
      'Narration' => $request->input('Narration_mst'),
      'Date' => $request->input('VHDate'),

    );

    // dd($invoice_mst);

    // $id= DB::table('')->insertGetId($data);

    $id = DB::table('voucher_master')->where('VoucherMstID', $request->input('VoucherMstID'))->update($voucher_mst);

    $idd = DB::table('voucher_detail')->where('VoucherMstID', $request->input('VoucherMstID'))->delete();
    $idd = DB::table('journal')->where('VoucherMstID', $request->input('VoucherMstID'))->delete();

      // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => null,
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher Updated', 
      'VHNO' => $request->input('Voucher'), 
      'Narration' => $request->input('Narration_mst'), 
      'Trace' => 202,
      'UserID' => Session::get('UserID'),
    );
 
    $log= DB::table('log')->insertGetId($logdata);

    // log input 



    for ($i = 0; $i < count($request->ChOfAcc); $i++) {
      $invoice_det = array(
        'VoucherMstID' => $request->input('VoucherMstID'),
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => $request->ChOfAcc[$i],
        'SupplierID' => $request->SupplierID[$i],
        'PartyID' => $request->PartyID[$i],
        'Narration' => $request->Narration[$i],
        'InvoiceNo' => $request->Invoice[$i],
        'RefNo' => $request->RefNo[$i],
        'Debit' => $request->Debit[$i],
        'Credit' => $request->Credit[$i],

      );

      // dd($invoice_det);  

          // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i],
      'Date' => date('Y-m-d H:i:s'), 
      'Section' => 'Voucher Updated', 
      'VHNO' => $request->input('Voucher'), 
      'Narration' => $request->input('Narration_mst') . 'Invoice# ' . $request->Invoice[$i] . $request->RefNo[$i]. ' amount '. ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i], 
      'Trace' => 203,
      'UserID' => Session::get('UserID'),
    );

 
    $log= DB::table('log')->insertGetId($logdata);

    // log input 



      $iddd = DB::table('voucher_detail')->insert($invoice_det);
    }

    return redirect('Voucher')->with('error', 'Record Updated')->with('class', 'success');
  }

  public function VoucherView($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Voucher', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'VoucherReport');
    $pagetitle = 'Voucher Report';

    // dd($request->all());
    // dd($request->VoucherTypeID);

    // dd(  $voucher_type);

    $voucher_master = DB::table('v_voucher_master')
      // ->whereBetween('Date',array($request->StartDate,$request->EndDate))
      ->where('VoucherMstID', $id)

      ->get();

    return view('voucher_view', compact('pagetitle', 'voucher_master'));
  }

  public  function VoucherDelete($id)
  {

      /////////////////////// USER RIGHT & CONTROL ///////////////////////////////////////////
 
try {
    $allow = check_role(Session::get('UserID'), 'Voucher', 'Delete');
    if ($allow[0]->Allow == 'N') {
        return redirect()->back()->with('error', 'Your access is limited')->with('class', 'danger');
    }

    //////////////////////////// END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'VoucherReport');
    $pagetitle = 'Voucher';

    // Begin transaction
    DB::beginTransaction();

    // Fetch the voucher details
    $voucher_master = DB::table('voucher_master')->where('VoucherMstID', $id)->first();
    if (!$voucher_master) {
        DB::rollBack(); // Rollback if the voucher doesn't exist
        return redirect()->back()->with('error', 'Voucher not found')->with('class', 'danger');
    }

    // Log input
    $logdata = array(
        'UserName' => Session::get('FullName'),
        'Amount' => null,
        'Date' => date('Y-m-d H:i:s'),
        'Section' => 'Voucher Deleted',
        'VHNO' => $voucher_master->Voucher,
        'Narration' => 'Voucher Deleted from journal too',
        'Trace' => 401,
        'UserID' => Session::get('UserID'),
    );

    $log = DB::table('log')->insertGetId($logdata);


    
    
      
    
    DB::table('invoice_master')
    ->where('Voucher', $voucher_master->Voucher)
    ->update(['Voucher' => null]);


    // Perform deletion
    DB::table('voucher_master')->where('VoucherMstID', $id)->delete();
    DB::table('voucher_detail')->where('VoucherMstID', $id)->delete();
    DB::table('journal')->where('VoucherMstID', $id)->delete();

    // Commit transaction
    DB::commit();

    // Return success response
    return redirect('Voucher')->with('error', 'Record Deleted')->with('class', 'success');
} catch (\Exception $e) {
    // Rollback transaction on error
    DB::rollBack();
    // Handle the exception
 
   return redirect('Voucher')->with('error', 'An error occurred: ' . $e->getMessage())->with('class', 'danger');


}

 

  }

  public function GetSpecificInvoice($id)
  {
    // Fetch the invoice data by ID
 

    $invoice = DB::table('ajax_invoice')->where('InvoiceMasterID', $id)->first();
    
    // return response()->json($invoice);

     return response()->json(
          $invoice
     );

     
  }

    public function GetSpecificInvoice1($id)
  {
    // Fetch the invoice data by ID
 

    $invoice = DB::table('v_invoice_master')->where('InvoiceMasterID', $id)->first();
    
    // return response()->json($invoice);

     return response()->json(
          $invoice
     );

     
  }


 

  public  function Invoice()
  {

    Session::put('menu', 'Invoice');
    $pagetitle = 'Invoice';

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'List');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////      

    Session()->forget('LeadID');
    Session()->forget('PartyID');

      
    $voucher_type = DB::table('v_invoice_master')->get();
    // $chartofaccount = DB::table('chartofaccount')->where('Code', 'E')->get();
    $chartofaccount = DB::table('chartofaccount')->where('ChartOfAccountID', '560110')->get();

    return view('invoice', compact('pagetitle', 'chartofaccount'));
  }

  public function ajax_invoice(Request $request)
  {
    Session::put('menu', 'Invoice');
    $pagetitle = 'Invoice';
    if ($request->ajax()) {
      $query = DB::table('v_invoice_detail')->whereNotIn('ItemCode',['UB','UA']);
       // Apply filters if they are present in the request
      // if ($request->has('item_name') && !empty($request->item_name)) {
      //     $query->where('ItemName', 'like', '%' . $request->item_name . '%');
      // }

       // If the user is NOT admin, filter by their UserID
    if (Session::get('UserType') !== 'Admin') {
        $query->where('UserID', Session::get('UserID'));
    }

      if ($request->has('party_name') && !empty($request->party_name)) {
        $query->where('PartyName', 'like', '%' . $request->party_name . '%');
      }
  if ($request->has('Phone') && !empty($request->Phone)) {
    // Clean the input phone number (remove spaces)
    $clean_phone = str_replace(' ', '', trim($request->Phone));

    // Extract the last 9 digits from the input phone
    $last_9_digits = substr($clean_phone, -9);

    // Modify the query to match the last 9 digits
    $query->where(function ($subQuery) use ($last_9_digits) {
        $subQuery->where(DB::raw("REPLACE(Phone, ' ', '')"), 'like', '%' . $last_9_digits . '%');
    });
}


      if ($request->has('startdate') && !empty($request->startdate)) {
        $query->whereDate('Date', '>=', $request->startdate);
      }
      if ($request->has('enddate') && !empty($request->enddate)) {
        $query->whereDate('Date', '<=', $request->enddate);
      }

      if ($request->has('UserID') && !empty($request->UserID)) {
        $query->where('UserID', $request->UserID);
      }

      if ($request->has('ItemID') && !empty($request->ItemID)) {
        $query->where('ItemID', $request->ItemID);
      }

      $data = $query->orderBy('InvoiceMasterID')->get();
      $PaymentMode = DB::table('chartofaccount')->distinct()->pluck('category');

      return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {


//  $editLink = '';

//                 // Check condition for ItemCode
//               if ($row->ItemCode == 'UA' || $row->ItemCode == 'UB') {
//     $editLink = '<a href="' . URL('/UmrahEdit/' . $row->InvoiceMasterID) . '" class="dropdown-item">
//         <i class="bx bx-pencil font-size-16 text-secondary me-1"></i> Edit Umrah Invoice
//     </a>';
// } else {
//     $editLink = '<a href="' . URL('/InvoiceEdit/' . $row->InvoiceMasterID) . '" class="dropdown-item">
//         <i class="bx bx-pencil font-size-16 text-secondary me-1"></i> Edit Invoice
//     </a>';
// }



    //                            <a href="' . URL('/UmrahEdit/' . $row->InvoiceMasterID) . '" class="dropdown-item">
    //     <i class="bx bx-pencil font-size-16 text-secondary me-1"></i> Edit Umrah Invoice
    // </a>


          $btn = '
                    <div class="d-flex align-items-center col-actions">
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-dots-horizontal font-size-18"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate(-33px, 27px);" data-popper-placement="bottom-end">
                                
                                
                                <li><a href="javascript:void(0)" onclick="openLedgerModal('.$row->PartyID.', \'' . $row->PartyName . '\')" class="dropdown-item"><i class="mdi mdi-account-details font-size-18  me-1" style="color:#FF5733;"></i> <strong> Party Ledger</strong></a></li>
                              

                              <div class="dropdown-divider"></div>
 <a href="' . URL('/InvoiceRefund/' . $row->InvoiceMasterID) . '" class="dropdown-item">
        <i class="mdi mdi-backup-restore font-size-18 text-danger me-1"></i> Invoice Refund
    </a>
    
    
          <div class="dropdown-divider"></div>

    
    <li>
  <a href="#" class="dropdown-item record-payment" data-invoicemasterid="'.$row->InvoiceMasterID.'" data-invoicetypeid="'.$row->InvoiceTypeID.'">
    <i class="mdi mdi-cash-usd font-size-18 text-success me-1"></i>
    Record Payment
  </a>
</li>



     
    
    <div class="dropdown-divider"></div>

                                 <li><a href="javascript:void(0)" onclick="loadPDF(\'' . URL('/InvoicePDFView/' . $row->InvoiceMasterID) . '\')" class="dropdown-item"><i class="mdi mdi-file-pdf font-size-16  me-1" style="color:#FF5733;"></i> View Invoice</a></li>
                              

 

    <a href="' . URL('/InvoiceEdit/' . $row->InvoiceMasterID) . '" class="dropdown-item">
        <i class="bx bx-pencil font-size-16 text-secondary me-1"></i> Edit Invoice
    </a>



                                
                                <li><a href="' . URL('/InvoicePDF/' . $row->InvoiceMasterID) . '/download' . '" target="_blank" class="dropdown-item"><i class="mdi mdi-file-pdf-outline font-size-16  me-1" style="color:#AF0505;"></i> Download Invoice</a></li>
                                
                                <li><a href="' . URL('/InvoicePDF/' . $row->InvoiceMasterID) . '" target="_blank" class="dropdown-item"><i class="mdi mdi-eye-outline font-size-16 text-secondary me-1"></i> View PDF</a></li>
                                
                                <li><a href="javascript:void(0)" onclick="delete_invoice(' . $row->InvoiceMasterID . ')" class="dropdown-item"><i class="bx bx-trash font-size-16 text-danger me-1"></i> Delete Invoice</a></li>
                            </ul>
                        </div>
                    </div>';
          return $btn;
        })
        ->rawColumns(['action'])
        ->with('paymentModes', $PaymentMode)
        ->make(true);
    }

    return view('invoice', compact('pagetitle'));
  }

  public function ajax_getAccountsByCategory(Request $request)
  {
    if ($request->ajax()) {
      $category = $request->get('category');
      $accounts = DB::table('chartofaccount')
        ->where('Category', strtoupper($category))
        ->get(); // Retrieve all accounts for the selected category
      return response()->json($accounts);
    }
    return response()->json([], 400); // Return an empty array and a 400 status code if the request is not AJAX
  }

  public function ajax_getVoucherNumber(Request $request)
  {
    $VoucherCodeID = $request->input('voucher_code'); // Get voucher code from request

    // Query to fetch voucher number
    $data = DB::table('voucher_master')
      ->select(DB::raw('LPAD(IFNULL(MAX(SUBSTR(Voucher,7)),0)+1,4,0) as vhno'))
      ->where('VoucherCodeID', $VoucherCodeID)
      ->first();

    $vhno = date('ym') . $data->vhno; // Combine voucher type, year, and vhno

    return response()->json(['vhno' => $vhno]); // Return vhno as JSON response
  }

  public function modelVoucherSave(Request $request)
  {
    
    $request->validate([
      'voucher_number' => 'required',
      'deposit_to' => 'required',
      'ChartOfAccountID' => 'required',
      'partyID' => 'required',
      'InvoiceMasterID' => 'required',
    ]);

   
    if($request->InvoiceTypeID == 1){
      $VoucherType = ($request->input('payment_mode') == 'CASH') ? 5 : 2;
      $acc_company = 'Debit';
      $acc_party = 'Credit';

    }
    elseif($request->InvoiceTypeID == 3){
  $VoucherType = ($request->input('payment_mode') == 'CASH') ? 5 : 2;
      $acc_company = 'Debit';
      $acc_party = 'Credit';
    }
  else
    {
      $VoucherType = ($request->input('payment_mode') == 'CASH') ? 4 : 1;
      $acc_company = 'Credit';
      $acc_party = 'Debit';
    }



    $invoice_mst = array(
     
      'PaymentMode' => $request->input('payment_mode'),
      'Voucher' => $request->input('voucher_number'),
      'Note' => $request->input('selectedAccountName'),
      

    );

    $id = DB::table('invoice_master')->where('InvoiceMasterID',  $request->InvoiceMasterID)->update($invoice_mst);


// log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $request->input('amount_received'),
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Invoice', 
      'VHNO' => $request->InvoiceMasterID, 
      'Narration' => 'Invoice Payment from invoice popup having' . ' vhno-> ' . $request->input('voucher_number'). ' voucher , payment mode and notes updated.' , 
      'Trace' => 1,
      'UserID' => Session::get('UserID'),
    );

$log= DB::table('log')->insertGetId($logdata);

// log input 

   
    $voucher_mst = array(
      'VoucherCodeID' => $VoucherType,
      'Voucher' => $request->input('voucher_number'),
      'Narration' => $request->notes,
      'Date' => dateformatpc($request->Date),
    );

    $id = DB::table('voucher_master')->insertGetId($voucher_mst);


    // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
       'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher', 
      'VHNO' =>  $request->InvoiceMasterID, 
      'Narration' => 'voucher master created from invoice popup '. $request->input('voucher_number'), 
      'Trace' => 2,
      'UserID' => Session::get('UserID'),
    );

$log= DB::table('log')->insertGetId($logdata);

// log input 

    $voucher_det_dr = array(
      'VoucherMstID' => $id,
      'Voucher' =>  $request->input('voucher_number'),
      'Date' => dateformatpc($request->Date),
      'ChOfAcc' => $request->input('deposit_to'),

      'PartyID' => $request->input('partyID'),
      'Narration' => $request->notes,
      'InvoiceNo' => $request->InvoiceMasterID,
      // 'RefNo' => '',
      
      
      $acc_company => $request->input('amount_received') ,
    );

    // dd( $voucher_det_dr);



    $id1 = DB::table('voucher_detail')->insert($voucher_det_dr);
    // dd('Done');


        // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $request->input('amount_received'),
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher', 
      'VHNO' => $request->InvoiceMasterID, 
      'Narration' => 'Invoice Payment from invoice popup voucher created having' . 'vhno-> ' . $request->input('voucher_number') . ' amount value -> '.   $request->input('amount_received') .'-> '. $request->input('deposit_to') .' '.  $acc_company.' action ', 
      'Trace' => 3,
      'UserID' => Session::get('UserID'),
    );

$log= DB::table('log')->insertGetId($logdata);

// log input 




    $voucher_det_cr = array(
      'VoucherMstID' => $id,
      'Voucher' =>  $request->input('voucher_number'),
      'Date' =>  dateformatpc($request->Date),
      'ChOfAcc' => 110400,
      // 'SupplierID' => '',
      'PartyID' =>  $request->input('partyID'),
      'Narration' => $request->notes,
      'InvoiceNo' => $request->InvoiceMasterID,
      // 'RefNo' => '',
      $acc_party => $request->input('amount_received'),

    );

    $id2 = DB::table('voucher_detail')->insert($voucher_det_cr);

    // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $request->input('amount_received'),
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher', 
      'VHNO' => $request->InvoiceMasterID, 
      'Narration' => 'Invoice Payment from invoice popup voucher created having' . 'vhno-> ' . $request->input('voucher_number') . ' amount value -> '.   $request->input('amount_received') .'-> 110400'. $acc_party .' action ', 
      'Trace' => 4,
      'UserID' => Session::get('UserID'),
    );

$log= DB::table('log')->insertGetId($logdata);

// log input 



    $invoice = DB::table('v_inv_paid')
      ->where('InvoiceMasterID', $request->InvoiceMasterID)
      ->first();
 
    // $updatePaidValue = DB::table('invoice_master')
    //   ->where('InvoiceMasterID', $request->InvoiceMasterID)
    //   ->update([
    //     'paid' => $invoice->Paid+($request->bank_charges ?? 0),
    //     'balance' => $invoice->Balance 
    //   ]);





// log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $invoice->Paid,
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Invoice', 
      'VHNO' => $request->InvoiceMasterID, 
      'Narration' => 'invoice paid amount updated in invoice master table with paid-> '. $invoice->Paid .' and balance =' .  $invoice->Balance, 
      'Trace' => 5,
      'UserID' => Session::get('UserID'),
    );

$log= DB::table('log')->insertGetId($logdata);

    // log input 
    if ($request->bank_charges > 0) {

      $voucher_det_dr_bc = array(
        'VoucherMstID' => $id,
        'Voucher' =>  $request->input('voucher_number'),
        'Date' => dateformatpc($request->Date),
        // 'ChOfAcc' => 210318, //Bank Charges Recovery Income comes
        // 'ChOfAcc' => $request->ChartOfAccountID, // bank charges drop menu
         'ChOfAcc' => $request->deposit_to,

        'PartyID' => $request->input('partyID'),
        'Narration' => $request->notes . 'Bank Charges',
        'InvoiceNo' => $request->InvoiceMasterID,
        // 'RefNo' => '',
        'Debit' => $request->bank_charges,
      );

 
      $id1 = DB::table('voucher_detail')->insert($voucher_det_dr_bc);
      // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $request->bank_charges,
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher', 
      'VHNO' => $request->input('voucher_number'), 
      'Narration' => 'Bank charges Payment from invoice popup voucher created having' . 'vhno-> ' . $request->input('voucher_number') . ' amount value -> '.   $request->bank_charges .'-> '.' DR -chart of account '. $request->bank_charges, 
      'Trace' => 6,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 
      $voucher_det_cr_bank_charges = array(
        'VoucherMstID' => $id,
        'Voucher' =>  $request->input('voucher_number'),
        'Date' =>  dateformatpc($request->Date),
        // 'ChOfAcc' => $request->input('deposit_to'),
        'ChOfAcc' => 110400, //Account Receivable
        // 'SupplierID' => '',
        'PartyID' =>  $request->input('partyID'),
        'Narration' => $request->notes . 'Bank Charges',
        'InvoiceNo' => $request->InvoiceMasterID,
        // 'RefNo' => '',
        'Credit' => $request->bank_charges,

      );
      $id2 = DB::table('voucher_detail')->insert($voucher_det_cr_bank_charges);

    // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $request->bank_charges,
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Voucher', 
      'VHNO' => $request->input('voucher_number'), 
      'Narration' => 'Bank charges Payment from invoice popup voucher created having' . 'vhno-> ' . $request->input('voucher_number') . ' amount value -> '.   $request->bank_charges .'-> '.' CR -chart of account '. $request->bank_charges, 
      'Trace' => 7,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 

    // 210318


      $voucher_det_bank_charges_dr = array(
        'VoucherMstID' => $id,
        'Voucher' =>  $request->input('voucher_number'),
        'Date' =>  dateformatpc($request->Date),
        // 'ChOfAcc' => $request->input('deposit_to'),
        'ChOfAcc' => 210318, //bank charges
        // 'SupplierID' => '',
        'PartyID' =>  $request->input('partyID'),
        'Narration' => $request->notes . 'Bank Charges',
        'InvoiceNo' => $request->InvoiceMasterID,
        // 'RefNo' => '',
        'Debit' => $request->bank_charges,

      );
      
      DB::table('voucher_detail')->insert($voucher_det_bank_charges_dr);

     
      $voucher_det_bank_cr = array(
        'VoucherMstID' => $id,
        'Voucher' =>  $request->input('voucher_number'),
        'Date' =>  dateformatpc($request->Date),
        // 'ChOfAcc' => $request->input('deposit_to'),
        'ChOfAcc' => $request->deposit_to,
        // 'SupplierID' => '',
        'PartyID' =>  $request->input('partyID'),
        'Narration' => $request->notes . 'Bank Charges',
        'InvoiceNo' => $request->InvoiceMasterID,
        // 'RefNo' => '',
        'Credit' => $request->bank_charges,

      );
      
      DB::table('voucher_detail')->insert($voucher_det_bank_cr);


    }

    // Handle file uploads
    if ($request->hasFile('file')) {
      foreach ($request->file('file') as $file) {
        $filePath = $file->store('uploads', 'public');
        // Save file paths or additional logic here
      }
    }


  if($request->input('InvoiceTypeID') == 3 || $request->input('InvoiceTypeID') == 1)
{
       $invoice = DB::table('v_inv_paid')
      ->where('InvoiceMasterID', $request->InvoiceMasterID)
      ->first();
}
else
{
        $invoice = DB::table('v_inv_paid_refund')
        ->where('InvoiceMasterID', $request->InvoiceMasterID)
        ->first();
}


 
    $updatePaidValue = DB::table('invoice_master')
      ->where('InvoiceMasterID', $request->InvoiceMasterID)
      ->update([
        'paid' => $invoice->Paid,
        'balance' => $invoice->Balance 
      ]);



    // Return a success response or redirect
    return redirect()->back()->with('error', 'Payment recorded successfully!')->with('class', 'success');
  }

  public function InvoicePDFView($id, $download = null)
  {
    // Check user access rights (assuming `check_role` function exists)
    $allow = check_role(Session::get('UserID'), 'Invoice', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return response()->json(['error' => 'You access is limited'], 403);
    }

    Session::put('menu', 'Invoice');
    $invoice_type = DB::table('invoice_type')->get();
    $items = DB::table('item')->get();
    $supplier = DB::table('supplier')->get();
    $vhno = DB::table('invoice_master')->select(DB::raw('max(InvoiceMasterID)+1 as VHNO'))->get();
    $company = DB::table('company')->where('CompanyID', 1)->get();
    $invoice_mst = DB::table('v_invoice_master')->where('InvoiceMasterID', $id)->get();
    $invoice_det = DB::table('v_invoice_detail2')->where('InvoiceMasterID', $id)->get();
    $invoice = DB::table('invoice_master')->select('total', 'paid')->where('InvoiceMasterID', $id)->first();
    $balance = $invoice->total - $invoice->paid;


    $voucher_detail = DB::table('v_voucher_detail')->where('InvoiceNo',$id)->get();

    // Load the HTML view instead of generating PDF directly
    $html = view('invoice_pdf', compact('balance', 'invoice_type', 'items', 'supplier', 'vhno', 'invoice_mst', 'invoice_det', 'company','voucher_detail'))->render();

    return response()->json(['html' => $html], 200);
  }

  public  function InvoiceCreate()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Invoice');
    $invoice_type = DB::table('invoice_type')->whereIn('InvoiceTypeID',[1,2])->get();

    $items = DB::table('item')->where('ItemType','I')->get();
    $supplier = DB::table('supplier')->get();
    $party = DB::table('party')->get();
    
    // $saleman = DB::table('user')->where('Active','Yes')->get();
    // $saleman = DB::table('user')->where('UserType','Saleman')->where('Active','Yes')->get();

    $query = DB::table('user')->where('Active', 'Yes');

    if (Session::get('UserType') !== 'Admin') {
        $query->where('UserID', Session::get('UserID'));
    }

    $saleman = $query->get();

    $vhno = DB::table('invoice_master')->select(DB::raw('IFNULL(max(InvoiceMasterID)+1,1) as VHNO'))->get();

    return view('invoice_create', compact('invoice_type', 'items', 'supplier', 'vhno', 'saleman','party'))->with('error', 'Logout Successfully.')->with('class', 'success');
  }

  public  function Ajax_Balance(request $request)
  {

    $data = array('SupplierID' => $request->input('SupplierID'));

    $supplier = DB::table('v_journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
      ->where('SupplierID', $request->SupplierID)
      ->where('ChartOfAccountID', 210100)
      ->get();

    return view('ajax_balance', compact('supplier'));
  }

  public  function Ajax_Balance_party(request $request)
  {

    $data = array('PartyID' => $request->input('PartyID'));

    $party = DB::table('v_journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
      ->where('PartyID', $request->PartyID)
      ->where('ChartOfAccountID', 110400)
      ->get();

    return $party[0]->Balance;
  }

  public  function Ajax_Ticket(request $request)
  {

    $data = array('RefNo' => $request->input('RefNo'));

    $ticket = DB::table('invoice_detail')

      ->where('RefNo', $request->RefNo)
      ->get();

    return view('ajax_ticket', compact('ticket'));
  }

  public  function InvoiceSave(request $request)
  {
      
     ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////


        try {
                DB::beginTransaction();
                 // your alll  queries here -->
    
             


    if ($request->input('PaymentMode') == 'Cash') {
      $PaymentMode = '110101'; //Cash in hand
    } elseif ($request->input('PaymentMode') == 'Credit Card') {

      $PaymentMode = '110250'; //Credit Card ACCOUNT.

    } elseif ($request->input('PaymentMode') == 'ENBD Bank') {

      $PaymentMode = '110201'; //ENBD Bank

    } else {
      $PaymentMode = '110202'; //ADCB Bank
    }

    $invoice_type = DB::table('invoice_type')->where('InvoiceTypeID', $request->input('InvoiceTypeID'))->get();

    if ($request->PartyID == 1) {

      $data = array(
        'PartyName' => $request->WalkinCustomerName,
        'Phone' => $request->WalkinCustomerMobile
      );

      $party_id = DB::table('party')->insertGetId($data);

      $request->PartyID = $party_id;
    }

    $invoice_mst = array(
      'InvoiceMasterID' => $request->input('VHNO'),
      'InvoiceTypeID' => $request->input('InvoiceTypeID'),
      'LeadID' => Session::get('LeadID'),
      'Date' => $request->input('Date'),
      'ReferenceNo' => $request->input('ReferenceNo'),
      'PartyID' => $request->PartyID,
      'DueDate' => $request->input('DueDate'),
      'PaymentMode' => $request->input('PaymentMode'),
      'Total' => $request->input('Total'),
      'Paid' => $request->input('amountPaid'),
      'Balance' => $request->input('amountDue'),
      'UserID' => $request->input('SalemanID'),
      'Source' => $request->input('Source'),
      'Note' => $request->input('remarks'),
       // bank charges additional fields
      'BankName' => $request->input('BankName'),
      'Percentage' => $request->input('Percentage') ?? 0,
      'BankCharges' => $request->input('PercentageValue') ?? 0,
      'GrandTotal' => $request->input('GrandTotal'),

    );

    // $id= DB::table('')->insertGetId($data);

    $id = DB::table('invoice_master')->insertGetId($invoice_mst);
    $InvoiceMasterID = $id; // assigning for Session 


      // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $request->input('Total'),
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Invoice Create', 
      'VHNO' => $InvoiceMasterID, 
      'Narration' => 'Invoice Created', 
      'Trace' => 101,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 


    
    
 

    //  start for item array from invoice
    for ($i = 0; $i < count($request->ItemID0); $i++) {

     if (!empty($request->TicketNo[$i])) {
    $airline_code = substr($request->TicketNo[$i], 0, 3);
    $airline = \App\Models\Airline::where('code', $airline_code)->first();
    $airline_name = $airline->name ?? ''; // adjust column name if different
    } else {
        $airline_name = '';
    }
      


      $invoice_det = array(
        'InvoiceMasterID' => $request->input('VHNO'),
        'ItemID' => $request->ItemID0[$i],
        'SupplierID' => $request->SupplierID[$i],

        'VisaType' => $request->VisaType[$i],
        'PaxName' => $request->PaxName[$i],

        'Passport' => $request->Passport[$i],
        'TicketNo' => $request->TicketNo[$i],
        'DepartureDate' => $request->DepartureDate[$i],
        'AirlineName' => $airline_name ,

        'PNR' => $request->PNR[$i],
        'Sector' => $request->Sector[$i],
        'Fare' => $request->Fare[$i],
        'RefNo' => $request->RefNo[$i],
        'Taxable' => $request->Taxable[$i],
        'TaxPer' => $request->TaxPer[$i],
        'Service' => $request->Service[$i],
        'OPVAT' => $request->OPVAT[$i],
        'IPVAT' => $request->IPVAT[$i],
        'Discount' => $request->Discount[$i],
        'Total' => $request->ItemTotal[$i],

      );

      $idd = DB::table('invoice_detail')->insertGetId($invoice_det);

      // journal entry start from here when full payment is made part 1
      if ($request->input('InvoiceTypeID') == 1) {

        // A/R
        $loop_AR = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '110400',  // A/R
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Dr' => $request->ItemTotal[$i],
          'Narration' => $request->PaxName[$i],

          'Trace' => 105
        );
        $id = DB::table('journal')->insertGetId($loop_AR);

        $loop_purchase_cr = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '510103',  // PURCHASE OF TICKET
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Cr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 106
        );
        $id = DB::table('journal')->insertGetId($loop_purchase_cr);

        // Services Charges
        if ($request->Service[$i] > 0) {

          $comission = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // COMISSION 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => $request->Service[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 107
          );

          $id = DB::table('journal')->insertGetId($comission);

          // AR
        }
        ///////////////////udpatejune
        else {
          $comission = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // COMISSION 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => abs($request->Service[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 108
          );

          $id = DB::table('journal')->insertGetId($comission);
        }

        // Purchase of Ticket - > PIA
        $loop_purchase_dr = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '510103',  // PURCHASE OF TICKET
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Dr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 109
        );

        $id = DB::table('journal')->insertGetId($loop_purchase_dr);

        // A/P - > PIA
        $loop_ap = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '210100',  // A/P - > PIA
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Cr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 110,
          'ReferenceNo' => $request->input('ReferenceNo')
        );

        $id = DB::table('journal')->insertGetId($loop_ap);

        // tax start from here
        // if tax is > 0 
        if ($request->Taxable[$i] > 0) {

          // tax Debit
          $tax_payable = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => 210300, // TAX PAYABLES
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => $request->Taxable[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 111
          );
          $id = DB::table('journal')->insertGetId($tax_payable);

          // tax credit from comission
          // $tax_expense = array(
          //     'VHNO' => $invoice_type[0]->InvoiceTypeCode.$request->input('VHNO'), 
          //     'JournalType' => $invoice_type[0]->InvoiceTypeCode, 
          //     'ChartOfAccountID' => 410101, // COMISSION (TAX WILL MINUS FROM COMISSION)
          //     'SupplierID' => $request->SupplierID[$i], 
          //     'PartyID' => $request->PartyID, 
          //     'InvoiceMasterID' => $request->input('VHNO'), 
          //     'Date' => $request->input('Date'),
          //     'Trace' => 112,
          //      'Dr' => $request->Taxable[$i], // kamal disable this code due to net amount posted in commsion. net off 100 - tax 
          //   );

          //  $id= DB::table('journal')->insertGetId($tax_expense);

        } else {
          // tax Debit
          $tax_payable = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => 210300, // TAX PAYABLES
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => abs($request->Taxable[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 111
          );
          $id = DB::table('journal')->insertGetId($tax_payable);
        }

        // tax end here 

        //discount

        if ($request->Discount[$i] > 0) {

          $discount_given = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410155', // Discount Received -> commsion update chart of account
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => $request->Discount[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 203
          );

          $id = DB::table('journal')->insertGetId($discount_given);
        }
        
        
 
      }
      // journal entry end here part 1

      // SALE RETURN FOR EACH ROW

      // journal entry start from here when full payment is made part 2
      if ($request->input('InvoiceTypeID') == 2) {

        // A/R
        $loop_AR = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '110400',  // A/R
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Cr' => $request->ItemTotal[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 201
        );
        $id = DB::table('journal')->insertGetId($loop_AR);

        // Services Charges
        if ($request->Service[$i] > 0) {

          $comission = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // COMISSION 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => $request->Service[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 202
          );

          $id = DB::table('journal')->insertGetId($comission);
        } else {
          $comission = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // COMISSION 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => abs($request->Service[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 2022
          );

          $id = DB::table('journal')->insertGetId($comission);
        }

        // Services Charges -> commsion update chart of account
        if ($request->Discount[$i] > 0) {

          $discount_rec = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // Discount Received -> commsion update chart of account
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => $request->Discount[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 203
          );

          $id = DB::table('journal')->insertGetId($discount_rec);
        } else {
          $discount_rec = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // Discount Received 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => abs($request->Discount[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 2033
          );

          $id = DB::table('journal')->insertGetId($discount_rec);
        }

        // Purchase of Ticket - > PIA - DEBIT
        $loop_purchase_dr = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '510103',  // PURCHASE OF TICKET
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Dr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 204
        );

        $id = DB::table('journal')->insertGetId($loop_purchase_dr);

        // Purchase of Ticket - > PIA - CREDIT
        $loop_purchase_cr = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '510103',  // PURCHASE OF TICKET
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Cr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 205
        );
        $id = DB::table('journal')->insertGetId($loop_purchase_cr);

        // A/P - > PIA
        $loop_ap = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '210100',  // A/P - > PIA
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Dr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 206,
           'ReferenceNo' => $request->input('ReferenceNo')
        );

        $id = DB::table('journal')->insertGetId($loop_ap);



             if ($request->Taxable[$i] > 0) {

          // tax Debit
          $tax_payable = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => 210300, // TAX PAYABLES
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => $request->Taxable[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 2111
          );
          $id = DB::table('journal')->insertGetId($tax_payable);

          // tax credit from comission
          // $tax_expense = array(
          //     'VHNO' => $invoice_type[0]->InvoiceTypeCode.$request->input('VHNO'), 
          //     'JournalType' => $invoice_type[0]->InvoiceTypeCode, 
          //     'ChartOfAccountID' => 410101, // COMISSION (TAX WILL MINUS FROM COMISSION)
          //     'SupplierID' => $request->SupplierID[$i], 
          //     'PartyID' => $request->PartyID, 
          //     'InvoiceMasterID' => $request->input('VHNO'), 
          //     'Date' => $request->input('Date'),
          //     'Trace' => 112,
          //      'Dr' => $request->Taxable[$i], // kamal disable this code due to net amount posted in commsion. net off 100 - tax 
          //   );

          //  $id= DB::table('journal')->insertGetId($tax_expense);

        } else {
          // tax Debit
          $tax_payable = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => 210300, // TAX PAYABLES
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => abs($request->Taxable[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 2110
          );
          $id = DB::table('journal')->insertGetId($tax_payable);
        }




      }
      // journal entry end here part 1

      // END SALE RETURN FOR EACH ROW

    }

    // end foreach



         //bank charges entry 

        if ($request->PercentageValue > 0) {

             $bank_charges_dr = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '110400', // Account Receivable
            
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => $request->PercentageValue,
            'Narration' => 'Bank Charges Applied from '. $request->BankName . ' at rate of ' . $request->Percentage . '%',
            'Trace' => 1203
          );

          DB::table('journal')->insertGetId($bank_charges_dr);



          $bank_charges_cr = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '210318', // Bank Charges Recovery Income comes
            
            
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => $request->PercentageValue,
            'Narration' => 'Bank Charges Applied from '. $request->BankName . ' at rate of ' . $request->Percentage . '%',
            'Trace' => 1204
          );

          DB::table('journal')->insertGetId($bank_charges_cr);
          
          
          
       
        }    

        // end of bank charges entry




    // queries end here  -->
    DB::commit();
    return redirect('Invoice')
      ->with('error', 'Invoice Saved')
      ->with('class', 'success')
      ->with('invoiceMasterID', $InvoiceMasterID);



       
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage())->with('class', 'danger');
            }


  }

  

  public  function InvoiceRefund($id = null)
  {
 
    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Invoice');

        try {
  
                 // your alll  queries here -->
    
             

    $invoice_type = DB::table('invoice_type')->where('InvoiceTypeID',2)->get();

    $items = DB::table('item')->get();
    $supplier = DB::table('supplier')->get();
    $party = DB::table('party')->get();
    
    // $saleman = DB::table('user')->where('Active','Yes')->get();

    $query = DB::table('user')->where('Active', 'Yes');

if (Session::get('UserType') !== 'Admin') {
    $query->where('UserID', Session::get('UserID'));
}

$saleman = $query->get();


    // $saleman = DB::table('user')->where('UserType','Saleman')->where('Active','Yes')->get();
    $vhno = DB::table('invoice_master')->select(DB::raw('max(InvoiceMasterID)+1 as VHNO'))->get();

    $invoice_mst = DB::table('invoice_master')->where('InvoiceMasterID', $id)->get();
    $invoice_det = DB::table('invoice_detail')->where('InvoiceMasterID', $id)->get();


// queries end here  -->
  

    return view('invoice_refund', compact('invoice_type', 'items', 'supplier', 'vhno', 'invoice_mst', 'invoice_det', 'party', 'saleman','vhno'))->with('error', 'Logout Successfully.')->with('class', 'success');



                
            } catch (\Exception $e) {
                
                return back()->with('error', $e->getMessage())->with('class', 'danger');
            }





  }




    public  function InvoiceEdit($id = null)
  {
 
    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Invoice');

        try {
  
                 // your alll  queries here -->
    
             

    $invoice_type = DB::table('invoice_type')->whereIn('InvoiceTypeID',[1,2])->get();

    $items = DB::table('item')->get();
    $supplier = DB::table('supplier')->get();
    $party = DB::table('party')->get();
    
    // $saleman = DB::table('user')->where('Active','Yes')->get();

    $query = DB::table('user')->where('Active', 'Yes');

if (Session::get('UserType') !== 'Admin') {
    $query->where('UserID', Session::get('UserID'));
}

$saleman = $query->get();


    // $saleman = DB::table('user')->where('UserType','Saleman')->where('Active','Yes')->get();
    $vhno = DB::table('invoice_master')->select(DB::raw('max(InvoiceMasterID)+1 as VHNO'))->get();

    $invoice_mst = DB::table('invoice_master')->where('InvoiceMasterID', $id)->get();
    $invoice_det = DB::table('invoice_detail')->where('InvoiceMasterID', $id)->get();


// queries end here  -->
  

    return view('invoice_edit', compact('invoice_type', 'items', 'supplier', 'vhno', 'invoice_mst', 'invoice_det', 'party', 'saleman'))->with('error', 'Logout Successfully.')->with('class', 'success');



                
            } catch (\Exception $e) {
                
                return back()->with('error', $e->getMessage())->with('class', 'danger');
            }





  }

  public  function InvoiceUpdate(request $request)
  {




 
    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////



        try {
                DB::beginTransaction();
                 // your alll  queries here -->
    
             


    if ($request->input('PaymentMode') == 'Cash') {
      $PaymentMode = '110101'; //Cash in hand
    } elseif ($request->input('PaymentMode') == 'Credit Card') {

      $PaymentMode = '110250'; //Credit Card ACCOUNT.

    } elseif ($request->input('PaymentMode') == 'ENBD Bank') {

      $PaymentMode = '110201'; //ENBD Bank

    } else {
      $PaymentMode = '110202'; //ADCB Bank
    }

    // dd($request->all());
    $invoice_mst = array(
      'InvoiceTypeID' => $request->input('InvoiceTypeID'),
      'Date' => $request->input('Date'),
      'PartyID' => $request->input('PartyID'),
      'DueDate' => $request->input('DueDate'),
      'ReferenceNo' => $request->input('ReferenceNo'),

      // 'PaymentMode' => $request->input('PaymentMode'),
      'Total' => $request->input('Total'),
      'Paid' => $request->input('amountPaid'),
      'Balance' => $request->input('amountDue') ,
      'UserID' => $request->input('SalemanID'),
      'Source' => $request->input('Source'),
      'Note' => $request->input('Note'),
      'Voucher' => $request->input('Voucher'),
      
      'BankName' => $request->input('BankName'),
      'Percentage' => $request->input('Percentage'),
      'BankCharges' => $request->input('PercentageValue'),
      'GrandTotal' => $request->input('GrandTotal'),


    );

    
    $id11 = DB::table('invoice_master')->where('InvoiceMasterID', $request->VHNO)->update($invoice_mst);

    $invoice_type = DB::table('invoice_type')->where('InvoiceTypeID', $request->input('InvoiceTypeID'))->get();

    $id1 = DB::table('invoice_detail')->where('InvoiceMasterID', $request->VHNO)->delete();

 $id2 = DB::table('journal')->where('InvoiceMasterID', $request->VHNO)
     ->whereNotNull('SupplierID')
    ->delete();
    
    
 $id222 = DB::table('journal')->where('InvoiceMasterID', $request->VHNO)
     ->whereIn('Trace',['1203','1204'])
    ->delete();

          // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $request->input('Total'),
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Invoice updated', 
      'VHNO' => $request->VHNO, 
      'Narration' => 'Invoice updated, invoice detail item deleted as programming and journal enteries deleted except the payment rec from voucher against this invoice', 
      'Trace' => 101,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 

    


 

    //  start for item array from invoice
    for ($i = 0; $i < count($request->ItemID0); $i++) {

     if (!empty($request->TicketNo[$i])) {
    $airline_code = substr($request->TicketNo[$i], 0, 3);
    $airline = \App\Models\Airline::where('code', $airline_code)->first();
    $airline_name = $airline->name ?? ''; // adjust column name if different
    } else {
        $airline_name = '';
    }
      


      $invoice_det = array(
        'InvoiceMasterID' => $request->input('VHNO'),
        'ItemID' => $request->ItemID0[$i],
        'SupplierID' => $request->SupplierID[$i],

        'VisaType' => $request->VisaType[$i],
        'PaxName' => $request->PaxName[$i],

        'Passport' => $request->Passport[$i],
        'TicketNo' => $request->TicketNo[$i],
        'DepartureDate' => $request->DepartureDate[$i],
        'AirlineName' => $airline_name ,

        'PNR' => $request->PNR[$i],
        'Sector' => $request->Sector[$i],
        'Fare' => $request->Fare[$i],
        'RefNo' => $request->RefNo[$i],
        'Taxable' => $request->Taxable[$i],
        'TaxPer' => $request->TaxPer[$i],
        'Service' => $request->Service[$i],
        'OPVAT' => $request->OPVAT[$i],
        'IPVAT' => $request->IPVAT[$i],
        'Discount' => $request->Discount[$i],
        'Total' => $request->ItemTotal[$i],

      );


      

      $id = DB::table('invoice_detail')->insertGetId($invoice_det);

      // journal entry start from here when full payment is made part 1
      if ($request->input('InvoiceTypeID') == 1) {

        // A/R
        $loop_AR = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '110400',  // A/R
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->input('PartyID'),
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Dr' => $request->ItemTotal[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 305
        );
        $id305 = DB::table('journal')->insertGetId($loop_AR);

        $loop_purchase_cr = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '510103',  // PURCHASE OF TICKET
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->input('PartyID'),
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Cr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 306
        );
        $id306 = DB::table('journal')->insertGetId($loop_purchase_cr);

        // Services Charges
        if ($request->Service[$i] > 0) {

          $comission = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // COMISSION 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->input('PartyID'),
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => $request->Service[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 307
          );

          $id3077 = DB::table('journal')->insertGetId($comission);
        } else {
          $comission = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // COMISSION 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->input('PartyID'),
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => abs($request->Service[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 307
          );

          $id307 = DB::table('journal')->insertGetId($comission);
        }

        // Purchase of Ticket - > PIA
        $loop_purchase_dr = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '510103',  // PURCHASE OF TICKET
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->input('PartyID'),
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Dr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 308
        );

        $id308 = DB::table('journal')->insertGetId($loop_purchase_dr);

        // A/P - > PIA
        $loop_ap = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '210100',  // A/P - > PIA
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->input('PartyID'),
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Cr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 309,
           'ReferenceNo' => $request->input('ReferenceNo')
        );

        $id309 = DB::table('journal')->insertGetId($loop_ap);

        // tax start from here
        // if tax is > 0 
        if ($request->Taxable[$i] > 0) {

          // tax Debit
          $tax_payable = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => 210300, // TAX PAYABLES
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->input('PartyID'),
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => $request->Taxable[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 310
          );
          $id310 = DB::table('journal')->insertGetId($tax_payable);
        } else {

          // tax Debit
          $tax_payable = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => 210300, // TAX PAYABLES
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->input('PartyID'),
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => abs($request->Taxable[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 310
          );
          $id310 = DB::table('journal')->insertGetId($tax_payable);

          // tax credit from comission
          // $tax_expense = array(
          //     'VHNO' => $invoice_type[0]->InvoiceTypeCode.$request->input('VHNO'), 
          //     'JournalType' => $invoice_type[0]->InvoiceTypeCode, 
          //     'ChartOfAccountID' => 410101, // COMISSION (TAX WILL MINUS FROM COMISSION)
          //     'SupplierID' => $request->SupplierID[$i], 
          //     'PartyID' => $request->input('PartyID'), 
          //     'InvoiceMasterID' => $request->input('VHNO'), 
          //     'Date' => $request->input('Date'),
          //     'Dr' => $request->Taxable[$i],  
          //     'Trace' => 311
          //   );

          //  $id= DB::table('journal')->insertGetId($tax_expense);

        }

        // tax end here 

        //discount

        if ($request->Discount[$i] > 0) {

          $discount_given = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410155', // Discount Received -> commsion update chart of account
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->input('PartyID'),
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => $request->Discount[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 203
          );

          $id = DB::table('journal')->insertGetId($discount_given);
        }
      }
      // journal entry end here part 1

      // SALE RETURN FOR EACH ROW

      // journal entry start from here when full payment is made part 2
      if ($request->input('InvoiceTypeID') == 2) {

        // A/R
        $loop_AR = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '110400',  // A/R
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Cr' => $request->ItemTotal[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 201
        );
        $id = DB::table('journal')->insertGetId($loop_AR);

        // Services Charges
        if ($request->Service[$i] > 0) {

          $comission = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // COMISSION 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => $request->Service[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 202
          );

          $id = DB::table('journal')->insertGetId($comission);
        } else {
          $comission = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // COMISSION 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => abs($request->Service[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 2022
          );

          $id = DB::table('journal')->insertGetId($comission);
        }

        // Services Charges -> commsion update chart of account
        if ($request->Discount[$i] > 0) {

          $discount_rec = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // Discount Received -> commsion update chart of account
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => $request->Discount[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 203
          );

          $id = DB::table('journal')->insertGetId($discount_rec);
        } else {
          $discount_rec = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '410101', // Discount Received 
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => abs($request->Discount[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 2033
          );

          $id = DB::table('journal')->insertGetId($discount_rec);
        }

        // Purchase of Ticket - > PIA - DEBIT
        $loop_purchase_dr = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '510103',  // PURCHASE OF TICKET
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Dr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 204
        );

        $id = DB::table('journal')->insertGetId($loop_purchase_dr);

        // Purchase of Ticket - > PIA - CREDIT
        $loop_purchase_cr = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '510103',  // PURCHASE OF TICKET
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Cr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 205
        );
        $id = DB::table('journal')->insertGetId($loop_purchase_cr);

        // A/P - > PIA
        $loop_ap = array(
          'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
          'JournalType' => $invoice_type[0]->InvoiceTypeCode,
          'ChartOfAccountID' => '210100',  // A/P - > PIA
          'SupplierID' => $request->SupplierID[$i],
          'PartyID' => $request->PartyID,
          'InvoiceMasterID' => $request->input('VHNO'),
          'Date' => $request->input('Date'),
          'Dr' => $request->Fare[$i],
          'Narration' => $request->PaxName[$i],
          'Trace' => 206,
           'ReferenceNo' => $request->input('ReferenceNo')
        );

        $id = DB::table('journal')->insertGetId($loop_ap);



             if ($request->Taxable[$i] > 0) {

          // tax Debit
          $tax_payable = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => 210300, // TAX PAYABLES
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => $request->Taxable[$i],
            'Narration' => $request->PaxName[$i],
            'Trace' => 2111
          );
          $id = DB::table('journal')->insertGetId($tax_payable);

          // tax credit from comission
          // $tax_expense = array(
          //     'VHNO' => $invoice_type[0]->InvoiceTypeCode.$request->input('VHNO'), 
          //     'JournalType' => $invoice_type[0]->InvoiceTypeCode, 
          //     'ChartOfAccountID' => 410101, // COMISSION (TAX WILL MINUS FROM COMISSION)
          //     'SupplierID' => $request->SupplierID[$i], 
          //     'PartyID' => $request->PartyID, 
          //     'InvoiceMasterID' => $request->input('VHNO'), 
          //     'Date' => $request->input('Date'),
          //     'Trace' => 112,
          //      'Dr' => $request->Taxable[$i], // kamal disable this code due to net amount posted in commsion. net off 100 - tax 
          //   );

          //  $id= DB::table('journal')->insertGetId($tax_expense);

        } else {
          // tax Debit
          $tax_payable = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => 210300, // TAX PAYABLES
            'SupplierID' => $request->SupplierID[$i],
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => abs($request->Taxable[$i]),
            'Narration' => $request->PaxName[$i],
            'Trace' => 2110
          );
          $id = DB::table('journal')->insertGetId($tax_payable);
        }




      }
      // journal entry end here part 1

      // END SALE RETURN FOR EACH ROW

    }
    // end for each
    $InvoiceMasterID = $request->input('VHNO');

    $invoice = DB::table('invoice_master')
      ->where('invoiceMasterID', $InvoiceMasterID)
      ->first();


//bank charges entry 

        if ($request->PercentageValue > 0) {

             $bank_charges_dr = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '110400', // Account Receivable
            
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Dr' => $request->PercentageValue,
            'Narration' => 'Bank Charges Applied from '. $request->BankName . ' at rate of ' . $request->Percentage . '%',
            'Trace' => 1203
          );

          DB::table('journal')->insertGetId($bank_charges_dr);



          $bank_charges_cr = array(
            'VHNO' => $invoice_type[0]->InvoiceTypeCode . $request->input('VHNO'),
            'JournalType' => $invoice_type[0]->InvoiceTypeCode,
            'ChartOfAccountID' => '210318', // Bank Charges Recovery Income comes
            
            
            'PartyID' => $request->PartyID,
            'InvoiceMasterID' => $request->input('VHNO'),
            'Date' => $request->input('Date'),
            'Cr' => $request->PercentageValue,
            'Narration' => 'Bank Charges Applied from '. $request->BankName . ' at rate of ' . $request->Percentage . '%',
            'Trace' => 1204
          );

          DB::table('journal')->insertGetId($bank_charges_cr);
          
          
          
       
        }    

        // end of bank charges entry


$invoice = DB::table('v_inv_paid')
    ->where('InvoiceMasterID', $request->input('VHNO'))
    ->first();

// if ($invoice) {
//     $paid = $invoice->Paid + ($request->bank_charges ?? 0);
//     $balance = $invoice->Balance;
// } else {
//     $paid = ($request->bank_charges ?? 0);
//     $balance = 0;
// }

// $updatePaidValue = DB::table('invoice_master')
//     ->where('InvoiceMasterID', $request->input('VHNO'))
//     ->update([
//         'paid'    => $paid,
//         'balance' => $balance,
//     ]);



        
// queries end here  -->
    DB::commit();

  
if ((optional($invoice)->Paid ?? 0) == (optional($invoice)->Total ?? 0)) {
  
 
      return redirect('Invoice')
        ->with('error', 'Invoice Updated')
        ->with('class', 'success');
    } else {
      return redirect('Invoice')
        ->with('error', 'Invoice Updated')
        ->with('class', 'success')
        ->with('invoiceMasterID', $InvoiceMasterID);
    }



    
    
            } catch (\Exception $e) {

                 DB::rollBack();
                return back()->with('error', $e->getMessage())->with('class', 'danger');
            }





  }

  public  function InvoiceDelete($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'Delete');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    DB::table('journal')->where('InvoiceMasterID', $id)->delete();

    DB::table('invoice_detail')->where('InvoiceMasterID', $id)->delete();

    $data = DB::table('invoice_master')->where('InvoiceMasterID', $id)->first();

    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => $data->Total,
      'Date' =>date('Y-m-d H:i:s'), 
      'Section' => 'Invoice Deleted', 
      'VHNO' => $id, 
      'Narration' =>  'Invoice Deleted total amount-> '. $data->Total. ' customer id '. $data->PartyID, 
      'Trace' => 401,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input

    DB::table('invoice_master')->where('InvoiceMasterID', $id)->delete();

                          // log input



    return redirect()->back()->with('error', 'Deleted successfully')->with('class', 'success');
  }

  public  function InvoiceView($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Invoice');
    $invoice_type = DB::table('invoice_type')->get();

    $items = DB::table('item')->get();
    $supplier = DB::table('supplier')->get();

    $vhno = DB::table('invoice_master')->select(DB::raw('max(InvoiceMasterID)+1 as VHNO'))->get();

    $invoice_mst = DB::table('v_invoice_master')->where('InvoiceMasterID', $id)->get();
    $invoice_det = DB::table('v_invoice_detail')->where('InvoiceMasterID', $id)->get();

    return View('invoice_view', compact('invoice_type', 'items', 'supplier', 'vhno', 'invoice_mst', 'invoice_det'));

    // $filename = $invoice_mst[0]->InvoiceCode.'-'.$invoice_mst[0]->Date.'-PartyCode-'.$invoice_mst[0]->PartyID;

    // $pdf = PDF::loadView ('invoice_pdf',compact('invoice_type','items','supplier','vhno','invoice_mst','invoice_det'));
    // $pdf->setpaper('A4', 'portiate');
    // return $pdf->download($filename.'.pdf');
    // return $pdf->stream();

  }

  public  function InvoicePDF($id, $download = null)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Invoice');
    $invoice_type = DB::table('invoice_type')->get();

    $items = DB::table('item')->get();
    $supplier = DB::table('supplier')->get();

    $vhno = DB::table('invoice_master')->select(DB::raw('max(InvoiceMasterID)+1 as VHNO'))->get();

          $voucher_detail = DB::table('v_voucher_detail')->where('InvoiceNo',$id)->get();


    $company = DB::table('company')->where('CompanyID', 1)->get();
    $invoice_mst = DB::table('v_invoice_master')->where('InvoiceMasterID', $id)->get();
    $invoice_det = DB::table('invoice_detail')
    ->leftJoin('item', 'invoice_detail.ItemID', '=', 'item.ItemID')
    ->where('invoice_detail.InvoiceMasterID', $id)
    ->select('invoice_detail.*', 'item.ItemName')
    ->get();


    $invoice = DB::table('invoice_master')->select('total', 'paid')->where('InvoiceMasterID', $id)->first();
    $balance = $invoice->total - $invoice->paid;

    $voucher_detail = DB::table('voucher_detail')->where('InvoiceNo', $id)->get();

    // return View ('invoice_pdf',compact('invoice_type','items','supplier','vhno','invoice_mst','invoice_det'));

    $filename = $invoice_mst[0]->InvoiceCode . '-' . $invoice_mst[0]->Date . '-PartyCode-' . $invoice_mst[0]->PartyID;

    $pdf = PDF::loadView('invoice_pdf', compact('balance', 'invoice_type', 'items', 'supplier', 'vhno', 'invoice_mst', 'invoice_det', 'company','voucher_detail'));
    $pdf->setpaper('A4', 'portiate');

    if ($download == 'download') {
      return $pdf->download($filename . '.pdf');
    } else {
      return $pdf->stream();
    }
  }

  public  function Ajax_VHNO(request $request)
  {

    $d = array(

      'VocherTypeID' => $request->VocherTypeID,
      'VocherCode' => $request->VocherCode,
      'VHDate' => $request->VHDate
    );

    $data = DB::table('voucher_master')
      ->select(DB::raw('LPAD(IFNULL(MAX(SUBSTR(Voucher,7)),0)+1,4,0) as vhno'))
      ->where('VoucherCodeID', $request->VocherTypeID)
      ->get();

    // $data = DB::table('voucher_master')
    //           ->select( DB::raw('LPAD(IFNULL(MAX(SUBSTR(Voucher,7)),0)+1,4,0) as vhno'))
    //           ->where ('VoucherCodeID',$request->VocherTypeID)->where(DB::raw('DATE_FORMAT(Date,"%Y%m")'),$request->VHDate)
    //            ->get();

    return view('ajax_vhno', compact('data', 'd'));
  }

  public  function Ajax_PVHNO(request $request)
  {

    $d = array(

      'VocherCode' => 'PC',
      'VHDate' => $request->VHDate
    );

    $data = DB::table('pettycash_master')
      ->select(DB::raw('LPAD(IFNULL(MAX(SUBSTR(PettyVoucher,7)),0)+1,4,0) as vhno'))
      // ->where(DB::raw('DATE_FORMAT(Date,"%Y%m")'),$request->VHDate)
      ->get();

    return view('ajax_pvhno', compact('data', 'd'));
  }


  public function paymentSummary(){
    Session::put('menu', 'PaymentSummary');
    $pagetitle = 'Payment Summary';
    $invoice_type = DB::table('invoice_type')->get();
    $item = DB::table('item')->get();
    $saleman = DB::table('saleman')->get();
    $supplier = DB::table('supplier')->get();

    return view('reports.payment_summary', compact('pagetitle', 'invoice_type', 'supplier', 'item', 'saleman'));
    
  }

  public function paymentSummary1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Ticket Register', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Payment Summary';
    $where = array();

    if (($request->InvoiceTypeID == 1) || ($request->InvoiceTypeID == 2)) {

      $where = array('InvoiceTypeID' => $request->InvoiceTypeID);
    }

    if ($request->SupplierID > 0) {

      $where = Arr::add($where, 'SupplierID', $request->SupplierID);
    }

    if ($request->ItemID > 0) {

      $where = Arr::add($where, 'ItemID', $request->ItemID);
    }

    if ($request->UserID > 0) {

      $where = Arr::add($where, 'UserID', $request->UserID);
    }

    $cash_payments = DB::table('invoice_master')
      ->where($where)
      ->whereBetween('Date', [$request->StartDate, $request->EndDate])
      ->where('Voucher', 'LIKE', 'CR%')
      ->orderBy('InvoiceMasterID')
      ->orderBy('Date')
      ->get();



    $bank_payments = DB::table('invoice_master')
      ->where($where)
      ->whereBetween('Date', [$request->StartDate, $request->EndDate])
      ->where('Voucher', 'LIKE', 'BR%')
      ->orderBy('InvoiceMasterID')
      ->orderBy('Date')
      ->get();

  

    // $pdf = PDF::loadView ('airline_summary1',compact('supplier'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    // return $pdf->stream();

    return View('reports.payment_summary1', compact('cash_payments','bank_payments' ,'pagetitle'));
  }

  public function Dashboard()
  {


 
    Session::put('menu', 'Dashboard');
   
    $start_date = date('Y-01-01');
    $end_date = date('Y-m-t');

    $pagetitle = 'Dashboard';

    $invoice_master = DB::table('invoice_master')
      ->select(DB::raw('ifnull(sum(IFNULL(Paid,0)),0) as Paid'))->where('Date', date('Y-m-d'))->get();

$v_cashflow = DB::table('v_cashflow')
    ->whereIn('Year', [date('Y'), date('Y') - 1])
    ->orderBy('Year')
    ->orderBy('mMonthName')
    ->get();
    $data = array();

    foreach ($v_cashflow as $key => $value) {

      $cashflow_chart[] = $value->Balance;
    }

    // todays income
    $expense = DB::table('v_journal')
      ->select(DB::raw('sum(if(ISNULL(Cr),0,Cr))-sum(if(ISNULL(Dr),0,Dr)) as Balance'))
      ->where('CODE', 'R')
      ->where(DB::raw('DATE_FORMAT(Date,"%Y-%m-%d")'), date('Y-m-d'))
      ->get();

    $revenue = DB::table('v_journal')
      ->select(DB::raw('sum(if(ISNULL(Cr),0,Cr))-sum(if(ISNULL(Dr),0,Dr)) as Balance'))
      ->where('CODE', 'R')
      ->where(DB::raw('DATE_FORMAT(Date,"%Y-%m")'), date('Y-m'))
      ->get();

    $r = DB::table('v_journal')
      ->select(DB::raw('sum(if(ISNULL(Cr),0,Cr))-sum(if(ISNULL(Dr),0,Dr)) as Balance'))
      ->whereBetween('Date', array($start_date, $end_date))
      ->where('CODE', 'R')
      ->get();

    $e = DB::table('v_journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr))-sum(if(ISNULL(Cr),0,Cr)) as Balance'))
      ->whereBetween('Date', array($start_date, $end_date))
      ->where('CODE', 'E')
      ->get();

    $r = ($r[0]->Balance == null) ? '0' :  $r[0]->Balance;
    $e = ($e[0]->Balance == null) ? '0' :  $e[0]->Balance;

    $profit_loss = abs($r) - abs($e);

    $cash = DB::table('v_journal')
      ->select('ChartOfAccountName',  DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)) as Balance'))
      ->whereIn('Category', ['CASH','BANK','CARD'])
      // ->where('ChartOfAccountID',$request->ChartOfAccountID)
      ->groupBy('ChartOfAccountName')
      ->get();

      $cash1 = DB::table('v_income_expense')
      ->whereIn('yDate', [date('Y'), date('Y') - 1])
      ->orderByDesc('yDate')
      ->orderByDesc('mDate')
      ->take(12)
      ->get()
      ->reverse();    
      
      // $cash1 = DB::table('v_income_expense')->whereIn('yDate', [date('Y'), date('Y') - 1])->orderby('yDate')->orderby('mDate')->get();
    $exp_chart = DB::table('v_expense_chart')->where('MonthName', date('F-Y'))->get();
 
    // $party_balance = DB::table('v_journal')
    //            ->select('ChartOfAccountName',  DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)) as Balance'))
    //            ->whereIn('ChartOfAccountID',[110400])

    //         // ->where('ChartOfAccountID',$request->ChartOfAccountID)
    //            ->groupBy('ChartOfAccountName')
    //                  ->get(); 

    //110400

    $party_balance = DB::table('v_party_bal')
      ->select(DB::raw('sum(if(ISNULL(Balance),0,Balance)) as Balance'))
      ->get();

    $ticket_register = DB::table('v_invoice_detail1')
      ->select('SalemanName', DB::raw('count(*) as TotalInvoices'), DB::raw('sum(Fare) as Fare'), DB::raw('ROUND(sum(Service), 2) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->whereBetween('Date', array(date('Y-m-1'), date('Y-m-d')))
      ->groupBy('SalemanName')
      // ->orderBy('Date')
      ->get();

    $ticket_register = $ticket_register->map(function ($item) {
        $item->Service = (float) $item->Service;
        $item->Fare = (float) $item->Fare;
        $item->Total = (float) $item->Total;
        return $item;
    });

    $invoice_summary = DB::table('v_invoice_detail1')
      ->select(
        DB::raw('count(*) as TotalInvoices'), 
        DB::raw('sum(Fare) as Fare'), 
        DB::raw('sum(Service) as Service'), 
        DB::raw('sum(Total) as Total'), 
        DB::raw('sum(Taxable) as Taxable'),
        DB::raw('sum(Discount) as Discount'))
      ->whereBetween('Date', array(date('Y-m-1'), date('Y-m-d')))
      // ->orderBy('Date')
      ->get();
    
    $date1 = date_create(date('Y-m-1'));   // First day of the month
    $date2 = date_create(date('Y-m-d'));   // Current date

    $interval = $date1->diff($date2);
    $diff = ($interval->days == 0) ? 1 : $interval->days + 1;  // Adding 1 to include today's date

    $avg = ($invoice_summary[0]->Service / $diff);  // Average based on total days including today


  $average= $revenue[0]->Balance/$diff;



  $leads_unassigned = DB::table('leads')->whereNull('agent_id')->count();
     if(Session::get('UserType')=='Admin')
  {
    $lead_summary = DB::table('v_lead_summary')->first();

}
    else
    {
    $lead_summary = DB::table('v_lead_summary_user')->where('agent_id',Session::get('UserID'))->first();
   } 
        
             $fourDaysAgo = Carbon::now()->subDays(4);
            
            $leadsNotUpdatedIn4Days = DB::table('leads')
            ->where('status','Pending')
            ->where('updated_at', '<', $fourDaysAgo)->count();
           
   
            $booking_payment = DB::table('v_bookings_admin')->where('amount','>',0)->where('status','Pending')->count();
            
            $agents = DB::table('user')->where('UserType' , 'Agent')->get();

            $today = Carbon::today();

            $leads_created_today = DB::table('leads')
          
            ->whereDate('created_at', $today)
            ->count();
    
            $leads_updated_today =  DB::table('leads')
           
            ->whereDate('updated_at', $today)
            ->count();


     $sale_report = DB::table('v_invoice_detail')
    ->select('ItemName', DB::raw('count(InvoiceMasterID) as Total'))
    ->whereBetween('date', array(date('Y-m-1'), date('Y-m-d')))
    ->groupBy('ItemName')
    ->get();


 
    $followup = DB::table('lead_details')->whereDate('follow_up_date', $today)->count();

   
    $monthly_income = DB::table('journal')
    ->select( DB::raw('sum(if(ISNULL(Cr),0,Cr)) - sum(if(ISNULL(Dr),0,Dr)) as Monthly_Income'))
    // ->whereIn('Category', ['CASH','BANK','CARD'])
    ->where('ChartOfAccountID','410101')
    // ->groupBy('ChartOfAccountName')
      ->where(DB::raw('DATE_FORMAT(Date,"%Y-%m")'), date('Y-m'))

    ->first();


    //Bank Charges
      $bank_charges = DB::table('v_journal')
        ->where('ChartOfAccountID',560110)
        ->whereNotNull('InvoiceMasterID')
        ->where(DB::raw('DATE_FORMAT(Date,"%Y-%m-%d")'), date('Y-m-d'))
        ->sum('Dr');
      
      $monthlyBankCharges = DB::table('v_journal')
        ->where('ChartOfAccountID',560110)
        ->whereNotNull('InvoiceMasterID')
        ->where(DB::raw('DATE_FORMAT(Date,"%Y-%m")'), date('Y-m'))
        ->sum('Dr');

      $todaySales = DB::table('journal')
        ->selectRaw("
            SUM(CASE WHEN ChartOfAccountID = 110101 THEN Dr ELSE 0 END) AS CASH,
            SUM(CASE WHEN ChartOfAccountID = 110202 THEN Dr ELSE 0 END) AS ADCB,
            SUM(CASE WHEN ChartOfAccountID = 110201 THEN Dr ELSE 0 END) AS ENBD,
            SUM(CASE WHEN ChartOfAccountID = 110203 THEN Dr ELSE 0 END) AS NOMOD,
            SUM(CASE WHEN ChartOfAccountID = 110204 THEN Dr ELSE 0 END) AS TABBY,
            SUM(CASE WHEN ChartOfAccountID = 110605 THEN Dr ELSE 0 END) AS TAMARA,
            SUM(CASE WHEN ChartOfAccountID = 110607 THEN Dr ELSE 0 END) AS BOTIM,
            SUM(Dr) AS TOTAL_SALES
        ")
        ->whereIn('ChartOfAccountID', [
            110101, // CASH
            110202, // ADCB
            110201, // ENBD
            110203, // NOMOD
            110204, // TABBY
            110605, // TAMARA
            110607  // BOTIM
        ])
        ->where('InvoiceMasterID','!=',NULL)
        ->whereDate('Date', now())
      ->first();


      $cards = [
        [
            'title' => 'ADCB BANK',
            'amount' => $todaySales->ADCB,
            'icon' => 'bx bxs-bank',
            'color' => '#bf2e26',
        ],
        [
            'title' => 'ENBD BANK',
            'amount' => $todaySales->ENBD,
            'icon' => 'bx bxs-bank',
            'color' => '#003A8F',
        ],
        [
            'title' => 'NOMOD BANK',
            'amount' => $todaySales->NOMOD,
            'icon' => 'bx bxs-bank',
            'color' => '#0046FF',
        ],
        [
            'title' => 'TABBY BANK',
            'amount' => $todaySales->TABBY,
            'icon' => 'bx bxs-credit-card',
            'color' => '#003228',
        ],
        [
            'title' => 'TAMARA BANK',
            'amount' => $todaySales->TAMARA,
            'icon' => 'bx bxs-credit-card',
            'color' => '#9600F1',
        ],
        [
            'title' => 'BOTIM BANK',
            'amount' => $todaySales->BOTIM,
            'icon' => 'bx bxs-bank',
            'color' => '#011FE5',
        ],
        [
            'title' => "TODAY'S CASH SALE",
            'amount' => $todaySales->CASH,
            'icon' => 'bx bxs-wallet',
            'color' => '#0D6EFD',
        ],
        [
            'title' => "TODAY'S TOTAL SALE",
            'amount' => $todaySales->TOTAL_SALES,
            'icon' => 'bx bxs-credit-card',
            'color' => '#E83E8C',
        ],
      ];

      $adminCards = [
        [
            'title' => 'PARTY BALANCES',
            'amount' => $party_balance[0]->Balance,
            'icon' => 'bx bxs-spreadsheet',
            'color' => '#0DCAF0',
            'link' => url('/PartyBalanceShowAll'),
        ],
        [
            'title' => "TODAY'S INCOME",
            'amount' =>$expense[0]->Balance,
            'icon' => 'bx bx-trending-up',
            'color' => '#DC3545',
            'link' => url('/SalemanTicketShowAll'),
        ],
        [
            'title' => 'MONTHLY INCOME',
            'amount' => $invoice_summary[0]->Service,
            'icon' => 'bx bx-trending-up',
            'color' => '#198754',
            'link' => '#'
        ],
        [
            'title' => 'CURRENT YEAR P&L',
            'amount' => $profit_loss,
            'icon' => 'bx bxs-chart',
            'color' => '#dea908ff',
            'link' => '#'
        ],
    ];
          
     return view('dashboard', compact('pagetitle','adminCards','cards','todaySales' ,'v_cashflow', 'invoice_master', 'expense', 'revenue', 'profit_loss', 'cash', 'cash1', 'exp_chart', 'party_balance', 'ticket_register', 'avg','lead_summary','sale_report','leads_unassigned','leads_created_today','leads_updated_today','followup','average','invoice_summary','monthly_income','bank_charges','monthlyBankCharges'));
  }


  public  function ItemWiseSale1()
    {

      $pagetitle='Item wise report';
                
      $item_detail = DB::table('v_invoice_detail')
    ->select(DB::raw('count(InvoiceMasterID) as Total'))
    ->addSelect('SalemanName')
    ->addSelect(DB::raw('sum(IF(ItemID = 7, 1, 0)) AS Approval'))
    ->addSelect(DB::raw('sum(IF(ItemID = 8, 1, 0)) AS Covid'))
    ->addSelect(DB::raw('sum(IF(ItemID = 9, 1, 0)) AS V1'))
    ->addSelect(DB::raw('sum(IF(ItemID = 10, 1, 0)) AS V2'))
    ->addSelect(DB::raw('sum(IF(ItemID = 11, 1, 0)) AS V3'))
    ->addSelect(DB::raw('sum(IF(ItemID = 12, 1, 0)) AS V4'))
    ->addSelect(DB::raw('sum(IF(ItemID = 20, 1, 0)) AS V5'))
    ->addSelect(DB::raw('sum(IF(ItemID = 21, 1, 0)) AS V6'))
    ->addSelect(DB::raw('sum(IF(ItemID = 13, 1, 0)) AS Freelancer'))
    ->addSelect(DB::raw('sum(IF(ItemID = 14, 1, 0)) AS Hotel'))
    ->addSelect(DB::raw('sum(IF(ItemID = 15, 1, 0)) AS KSA'))
    ->addSelect(DB::raw('sum(IF(ItemID = 16, 1, 0)) AS Safari'))
    ->addSelect(DB::raw('sum(IF(ItemID = 17, 1, 0)) AS Ticket'))
    ->addSelect(DB::raw('sum(IF(ItemID = 18, 1, 0)) AS Visa'))
    ->addSelect(DB::raw('sum(IF(ItemID = 19, 1, 0)) AS Umrah'))
    ->addSelect(DB::raw('sum(IF(ItemID = 24, 1, 0)) AS S1'))
    ->addSelect(DB::raw('sum(IF(ItemID = 25, 1, 0)) AS GT'))
    ->whereBetween('date', array(date('Y-m-1'), date('Y-m-d')))
    ->groupBy('SalemanName')
    ->get();

    return view ('itemwise_report1',compact('item_detail','pagetitle'));
    }
  public function ExpenseDetail()
  {

    $pagetitle = 'Expense';

    $expense = DB::table('v_journal')
      // ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr))-sum(if(ISNULL(Cr),0,Cr)) as Balance') )
      ->where('CODE', 'E')
      ->where(DB::raw('DATE_FORMAT(Date,"%Y-%m")'), date('Y-m'))
      ->where('ChartOfAccountID', '<>', 510103)
      // ->where(DB::raw('DATE_FORMAT(Date,"%Y-%m")'),'2023-03')
      ->orderBy('Date')
      ->get();

    return view('expense_detail', compact('pagetitle', 'expense'));
  }

  public  function Item()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Item/Inventory', 'List / Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Item');
    $pagetitle = 'Item';

    $item = DB::table('item')->get();
    return view('item', compact('pagetitle', 'item'));
  }

  public  function ItemSave(request $request)
  {
    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Item/Inventory', 'List / Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $this->validate(
      $request,
      [

        'ItemCode' => 'required',

        'ItemName' => 'required',

      ],
      [
        'ItemCode.required' => 'Item Code is required',
        'ItemName.required' => 'Item Name is required',

      ]
    );

    $data = array(
      'ItemCode' => $request->input('ItemCode'),
      'ItemName' => $request->input('ItemName'),
      'Taxable' => $request->input('Taxable'),
      'Percentage' => $request->input('Percentage'),

    );

    $id = DB::table('item')->insertGetId($data);

    return redirect('Item')->with('error', 'Save Successfully.')->with('class', 'success');
  }

  public  function ItemEdit($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Item/Inventory', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Item');
    $pagetitle = 'Item';

    $item = DB::table('item')->where('ItemID', $id)->get();

    return view('item_edit', compact('pagetitle', 'item'));
  }

  public  function ItemUpdate(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Item/Inventory', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $this->validate(
      $request,
      [

        'ItemCode' => 'required',

        'ItemName' => 'required',

      ],
      [
        'ItemCode.required' => 'Item Code is required',
        'ItemName.required' => 'Item Name is required',

      ]
    );

    $data = array(
      'ItemCode' => $request->input('ItemCode'),
      'ItemName' => $request->input('ItemName'),
      'Taxable' => $request->input('Taxable'),
      'Percentage' => $request->input('Percentage'),

    );

    $id = DB::table('item')->where('ItemID', $request->input('ItemID'))->update($data);

    return redirect('Item')->with('error', 'Updated Successfully.')->with('class', 'success');
  }

  public  function ItemDelete($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Item/Inventory', 'Delete');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////           

    $id = DB::table('item')->where('ItemID', $id)->delete();

    return redirect('Item')->with('error', 'Deleted Successfully')->with('class', 'success');
  }

  public  function Supplier()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier', 'List / Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Supplier');
    $pagetitle = 'Supplier';

    $supplier = DB::table('v_supplier')->get();
    $supplier_category = DB::table('supplier_category')->get();
    return view('supplier', compact('pagetitle', 'supplier', 'supplier_category'));
  }

  public  function SaveSupplier(request $request)
  {

    
    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier', 'List / Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $this->validate(
      $request,
      [

        'SupplierName' => 'required',

      ],
      [
        'SupplierCatID.required' => 'Suppier Type  is required',
        'SupplierName.required' => 'Supplier Name is required',

      ]
    );

    $data = array(

      'SupplierCatID' => $request->input('SupplierCatID'),
      'SupplierName' => $request->input('SupplierName'),
      'Address' => $request->input('Address'),
      'Phone' => $request->input('Phone'),
      'TRN' => $request->input('TRN'),
      'Email' => $request->input('Email'),
      'Active' => $request->input('Active'),
      'InvoiceDueDays' => $request->input('InvoiceDueDays'),

    );

    $id = DB::table('supplier')->insertGetId($data);

    return redirect('Supplier')->with('error', 'Save Successfully.')->with('class', 'success');
  }

  public  function SupplierEdit($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Supplier');
    $pagetitle = 'Supplier';

    $supplier = DB::table('v_supplier')->where('SupplierID', $id)->get();
    $supplier_category = DB::table('supplier_category')->get();
    return view('supplier_edit', compact('pagetitle', 'supplier', 'supplier_category'));
  }

  public  function SupplierUpdate(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    $this->validate(
      $request,
      [

        'SupplierName' => 'required',

      ],
      [
        'SupplierCatID.required' => 'Suppier Type  is required',
        'SupplierName.required' => 'Supplier Name is required',

      ]
    );

    $data = array(

      'SupplierCatID' => $request->input('SupplierCatID'),
      'SupplierName' => $request->input('SupplierName'),
      'Address' => $request->input('Address'),
      'Phone' => $request->input('Phone'),
      'TRN' => $request->input('TRN'),
      'Email' => $request->input('Email'),
      'Active' => $request->input('Active'),
      'InvoiceDueDays' => $request->input('InvoiceDueDays'),

    );

    $id = DB::table('supplier')->where('SupplierID', $request->input('SupplierID'))->update($data);

    return redirect('Supplier')->with('error', 'Updated Successfully.')->with('class', 'success');
  }
  public  function SupplierDelete($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier', 'Delete');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $id = DB::table('supplier')->where('SupplierID', $id)->delete();

    return redirect('Supplier')->with('error', 'Deleted Successfully')->with('class', 'success');
  }

  // parties 

  public  function Parties()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party / Customers', 'List / Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Party');
    $pagetitle = 'Parties';

    $supplier = DB::table('party')->get();
    return view('party', compact('pagetitle', 'supplier'));
  }

  public function ajax_party_validate(request $request)
  {

    $phone = substr($request->Phone, -9);

    $party = DB::table('party')->where('Phone', 'like', '%' . $phone . '%')->get();

    if (!$party->isEmpty()) {
      return response()->json(['total' => 1]);
    } else {

      return response()->json(['total' => 0]);
    }
  }

  public function ajax_party_save(request $request)
  {

    $data = array(
      'PartyName' =>  $request->PartyName,
      'Phone' =>  $request->Phone,
    );

    $party = DB::table('party')->insertGetId($data);

    return response()->json([
      'PartyID' => $party, 'PartyName' =>  $request->PartyName,
      'Phone' =>  $request->Phone
    ]);
  }

  public  function SaveParties(request $request)
  {
    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party / Customers', 'List / Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $this->validate(
      $request,
      [
        'PartyName' => 'required',
        'Phone' => 'required|numeric|unique:party,Phone',
        'Active' => 'required',

      ],
      [
        'PartyName.required' => 'Party / Cusomter Name is required',
        'Phone.unique' => 'The phone number already exist.'

      ]
    );

    $data = array(

      'PartyName' => $request->input('PartyName'),
      'Address' => $request->input('Address'),
      'Phone' => $request->input('Phone'),
      'Email' => $request->input('Email'),
      'Active' => $request->input('Active'),
      'InvoiceDueDays' => $request->input('InvoiceDueDays'),

    );

    $id = DB::table('party')->insertGetId($data);

    return redirect('Parties')->with('error', 'Save Successfully.')->with('class', 'success');
  }

  public  function PartiesEdit($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party / Customers', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'Party');
    $pagetitle = 'Party';

    $supplier = DB::table('party')->where('PartyID', $id)->get();

    return view('party_edit', compact('pagetitle', 'supplier'));
  }

  public  function PartiesUpdate(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party / Customers', 'Update');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $this->validate(
      $request,
      [
        'PartyName' => 'required',

        'Active' => 'required',

      ],
      [
        'PartyName.required' => 'Party / Cusomter Name is required',

      ]
    );

    $data = array(

      'PartyName' => $request->input('PartyName'),
      'Address' => $request->input('Address'),
      'Phone' => $request->input('Phone'),
      'Email' => $request->input('Email'),
      'Active' => $request->input('Active'),
      'InvoiceDueDays' => $request->input('InvoiceDueDays'),

    );

    $id = DB::table('party')->where('PartyID', $request->input('PartyID'))->update($data);

    return redirect('Parties')->with('error', 'Updated Successfully.')->with('class', 'success');
  }
  public  function PartiesDelete($id)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party / Customers', 'Delete');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    try {
      DB::beginTransaction();
      $id = DB::table('party')->where('PartyID', $id)->delete();
      DB::commit();
      return redirect('Parties')->with('error', 'Deleted Successfully')->with('class', 'danger');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->with('error', $e->getMessage())->with('class', 'danger');
    }
  }

  public  function ChartOfAcc()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Chart of Account', 'List / Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'ChartOfAcc');
    $pagetitle = 'ChartOfAcc';
    $chartofaccount = DB::table('chartofaccount')->get();
    $chart = DB::table('chartofaccount')->get();

    return view('chart_of_account', compact('pagetitle', 'chartofaccount', 'chart'));
  }

  public function UserProfile()
  {

    $v_users = DB::table('user')->where('UserID', Session::get('UserID'))->get();

    return  view('user_profile', compact('v_users'));
  }

  public function ChangePassword()
  {

    $v_users = DB::table('user')->where('UserID', Session::get('UserID'))->get();

    return  view('change_password', compact('v_users'));
  }

  public function UpdatePassword(request $request)
  {

    $user = DB::table('user')->where('UserID', Session::get('UserID'))->get();

    if ($user[0]->Password != $request->input('old_password')) {

      return redirect('ChangePassword')->with('error', 'Old password doesnot matched')->with('class', 'danger');
    }

    $this->validate($request, [

      'old_password' => 'required',
      'new_password' => 'required|min:6',
      'new_confirm_passowrd' => 'required_with:new_password|same:new_password|min:6'
    ]);
    // ,[
    //   'old_password.required' => 'Old Password is required',
    //        'new_password.required' => 'New Password is required ',
    //        'new_confirm_passowrd.required' => 'Confirm Password is required '

    // ]);

    $data = array(
      'Password' => $request->input('new_password')

    );

    $id = DB::table('user')->where('UserID', Session::get('UserID'))->update($data);
    return redirect('Dashboard')->with('error', 'Password updated Successfully')->with('class', 'success');
  }

  public function Role($UserID)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'User Rights', 'Assign');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'User Rights & Control';
    $users = DB::table('user')->where('UserID', $UserID)->get();

    $role = DB::table('role')->select('Table')->distinct()->get();

    return view('role', compact('pagetitle', 'role', 'users'));
  }

  public function RoleView($UserID)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'User Rights', 'Assign');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'User Rights & Control';
    $users = DB::table('user')->where('UserID', $UserID)->get();

    $role = DB::table('role')->select('Table')->distinct()->get();

    return view('view_role', compact('pagetitle', 'role', 'users'));
  }

  public function RoleSave(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'User Rights', 'Assign');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $TableName = $request->TableName;
    $Action = $request->Action;
    $Allow = $request->Allow;

    $tot = count($request->TableName);
    // echo count($box); // count how many values in array
    for ($i = 0; $i < $tot; $i++) {
      // echo $TableName[$i] .'-' . $Action[$i] .'-'.  $Allow[$i] . "<BR>";

      $data = array(

        'UserID' => $request->UserID,
        'Table' => $TableName[$i],
        'Action' => $Action[$i],
        'Allow' => $Allow[$i],

      );

      $id = DB::table('user_role')->insertGetId($data);
    }

    return redirect('User')->with('error', 'User perission saved Successfully')->with('class', 'success');
  }

  public function RoleUpdate(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'User Rights', 'Assign');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $id = DB::table('user_role')->where('UserID', $request->UserID)->delete();

    $TableName = $request->TableName;
    $Action = $request->Action;
    $Allow = $request->Allow;

    $tot = count($request->TableName);
    // echo count($box); // count how many values in array
    for ($i = 0; $i < $tot; $i++) {
      // echo $TableName[$i] .'-' . $Action[$i] .'-'.  $Allow[$i] . "<BR>";

      $data = array(

        'UserID' => $request->UserID,
        'Table' => $TableName[$i],
        'Action' => $Action[$i],
        'Allow' => $Allow[$i],

      );

      $id = DB::table('user_role')->insertGetId($data);
    }

    return redirect('User')->with('error', 'User perission saved Successfully')->with('class', 'success');
  }

  public function SendEMail(request $request)
  {

    $email = $request->input('Email');
    // $email = ['kashif@inu.edu.pk', 'kashif_mushtaq2008@htomail.com','kashif.mushtaq2050@gmail.com'];

    $data = array(

      'Name' => $request->input('Name'),
      'Email' => $request->input('Email'),
      'Subject' => $request->input('Subject'),
      'Message' => $request->input('Message'),

    );
    // Mail::to($email)->send(new SendMail($data));
    return redirect($request->input('PageLink'))->with('error', 'Email sent!')->with('class', 'success');
  }

  public function ComposeEmail($EmployeeID)
  {
    $pagetitle = 'Compose Email';

    $employee =  DB::table('v_employee')->where('EmployeeID', $EmployeeID)->get();
    return view('compose_email', compact('employee', 'pagetitle'));
  }

  public function ForgotPassword()
  {
    return view('forgot_password');
  }

  public function SendForgotEmail(request $request)
  {

    if ($request->StaffType == 'Management') {

      $username = $request->input('Email');

      $user = DB::table('users')->where('Email', '=', $username)
        ->get();

      if (count($user) > 0) {

        $email = $user[0]->Email;

        // $data = array (

        //               'Name' => $request->input('Name'),
        //               'Email' => $request->input('Email'),
        //               'Subject' => $request->input('Subject'),
        //               'Message' => $request->input('Message'),

        //      );
        //Mail::to($email) ->send(new SendMail($data));
        return redirect('EmailPin')->with('error', 'Enter Code')->with('class', 'success');
      } else {

        return redirect('ForgotPassword')->with('error', 'Invalid Email')->with('class', 'success');
      }
    } else {

      $username = $request->input('Email');

      $data = DB::table('employee')->where('Email', '=', $username)
        // ->where('Active', '=', 'Y' )
        ->get();

      if (count($data) > 0) {

        $data[0]->Email;

        return redirect('EmailPin')->with('error', 'Enter Code')->with('class', 'success');
      } else {
        //Session::flash('error', 'Invalid username or Password. Try again'); 

        return redirect('ForgotPassword')->withinput($request->all())->with('error', 'Invalid Email. Try again');
      }
    }
    // for staff login
  }

  public function EmailPin()
  {
    return view('email_pin');
  }

  public function NewPassword(request $request)
  {
    $employee = DB::table('employee')->get();
  }

  public function UpdateNewPassword(request $request)
  {
    $employee = DB::table('employee')->get();
  }

  public function DepositExport($type)
  {

    // $employees = DB::table('v_property')->get();

    $fcb = DB::table('v_fcb')->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'S.NO');
    $sheet->setCellValue('B1', 'ID');
    $sheet->setCellValue('C1', 'Agent');
    $sheet->setCellValue('D1', 'FTD Amount');
    $sheet->setCellValue('E1', 'Date');
    $sheet->setCellValue('F1', 'Compliant');
    $sheet->setCellValue('G1', 'KYC Sent');
    $sheet->setCellValue('H1', 'Dialer');

    $rows = 2;
    foreach ($fcb as $key => $value) {

      $sheet->setCellValue('A' . $rows, ++$key);
      $sheet->setCellValue('B' . $rows, $value->ID);
      $sheet->setCellValue('C' . $rows, $value->FirstName);
      $sheet->setCellValue('D' . $rows, $value->FTDAmount);
      $sheet->setCellValue('E' . $rows, $value->Date);
      $sheet->setCellValue('F' . $rows, $value->Compliant);
      $sheet->setCellValue('G' . $rows, $value->KYCSent);
      $sheet->setCellValue('H' . $rows, $value->Dialer);

      $rows++;
    }

    $fileName = "Deposit." . $type;
    if ($type == 'xlsx') {
      $writer = new Xlsx($spreadsheet);
    } else if ($type == 'xls') {
      $writer = new Xls($spreadsheet);
    }
    $writer->save($fileName);
    header("Content-Type: application/vnd.ms-excel");
    return redirect(url('/') . "/" . $fileName);
  }

  public function PartyLedger()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Ledger', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'PartyLedger');
    $pagetitle = 'Party Ledger';
    $party = DB::table('party')->get();
    $voucher_type = DB::table('voucher_type')->get();
    $chartofaccount = DB::table('chartofaccount')
      ->where('ChartOfAccountID', 110400)->get();
    return view('reports.party_ledger', compact('pagetitle', 'party', 'voucher_type', 'chartofaccount'));
  }

  public function PartyLedger1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Ledger', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'PartyLedger');
    $pagetitle = 'Party Ledger';

    Session::put('StartDate', $request->StartDate);
    Session::put('EndDate', $request->EndDate);

    $vouchertype = DB::table('voucher_type')->where('VoucherTypeID', $request->VoucherTypeID)->get();

    $where = array();

    if ($request->VoucherTypeID > 0) {

      $where = Arr::add($where, 'JournalType', $vouchertype[0]->VoucherCode);
    }

    if ($request->PartyID > 0) {

      $where = Arr::add($where, 'PartyID', $request->PartyID);
    }

    if ($request->ChartOfAccountID > 0) {

      $where = Arr::add($where, 'ChartOfAccountID', $request->ChartOfAccountID);
    }

    $sql = DB::table('journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
      // ->where('PartyID',$request->PartyID)
      ->where($where)
      ->where('Date', '<', $request->StartDate)
      // ->whereBetween('date',array($request->StartDate,$request->EndDate))

      ->get();

    // dd($sql[0]->Balance);
    // $sql= DB::select( DB::raw( 'SET @total := '.$sql[0]->Balance.''));
    // $sql= DB::select( DB::raw( 'select @total as t'));

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    // $a = DB::select(DB::raw('select * from v_journal where PartyID = @total'));
    // $journal = DB::select(DB::raw('SELECT a.JournalID, a.ChartOfAccountID, a.*, IF(ISNULL(a.Dr),0,a.Dr) as Dr, a.Cr,sum(if(ISNULL(b.Dr),0,b.Dr)-if(ISNULL(b.Cr),0,b.Cr))+'.$sql[0]->Balance.' as Balance FROM   v_journal a,  v_journal b WHERE b.JournalID <= a.JournalID and a.PartyID='.$request->PartyID.' and b.PartyID='.$request->PartyID.' and a.ChartOfAccountID=110400 and b.ChartOfAccountID=110400 GROUP BY a.JournalID, a.ChartOfAccountID, a.Dr, a.Cr ORDER BY a.JournalID'));
    // $a = DB::table('v_journal')->where('PartyID',DB::raw( '@total'))->get();

    $party = DB::table('party')->where('PartyID', $request->PartyID)->get();

    $journal = DB::table('v_journal')->where('PartyID', $request->PartyID)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->where($where)
      // ->orderBy('VHNO', 'asc')
      ->orderBy('Date', 'asc') // Sort by Date in ascending order
      ->orderBy('JournalID', 'asc')   // Sort by ID in ascending order
      ->get();

    //          $pdf = PDF::loadView ('party_ledger1pdf',compact('journal','pagetitle','sql' ,'party')); 
    // //return $pdf->download('pdfview.pdf');
    //    $pdf->setpaper('A4', 'portiate');
    //       return $pdf->stream();

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();
    return view('reports.party_ledger1', compact('journal', 'pagetitle', 'sql', 'party'));
  }



  public function ajax_party_ledger($partyid)
  {

   
    Session::put('menu', 'PartyLedger');
    $pagetitle = 'Party Ledger';
 
    $sql = DB::table('journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
       ->where('PartyID',$partyid)
      ->where('ChartOfAccountID',110400)
      ->where('Date', '<', '2020-01-01')
      // ->whereBetween('date',array($request->StartDate,$request->EndDate))

      ->get();

    // dd($sql[0]->Balance);
    // $sql= DB::select( DB::raw( 'SET @total := '.$sql[0]->Balance.''));
    // $sql= DB::select( DB::raw( 'select @total as t'));

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    // $a = DB::select(DB::raw('select * from v_journal where PartyID = @total'));
    // $journal = DB::select(DB::raw('SELECT a.JournalID, a.ChartOfAccountID, a.*, IF(ISNULL(a.Dr),0,a.Dr) as Dr, a.Cr,sum(if(ISNULL(b.Dr),0,b.Dr)-if(ISNULL(b.Cr),0,b.Cr))+'.$sql[0]->Balance.' as Balance FROM   v_journal a,  v_journal b WHERE b.JournalID <= a.JournalID and a.PartyID='.$request->PartyID.' and b.PartyID='.$request->PartyID.' and a.ChartOfAccountID=110400 and b.ChartOfAccountID=110400 GROUP BY a.JournalID, a.ChartOfAccountID, a.Dr, a.Cr ORDER BY a.JournalID'));
    // $a = DB::table('v_journal')->where('PartyID',DB::raw( '@total'))->get();

    $party = DB::table('party')->where('PartyID', $partyid)->get();

    $journal = DB::table('v_journal')->where('PartyID', $partyid)
      ->whereBetween('Date', array('2020-01-01', date('Y-m-d')))
       ->where('ChartOfAccountID',110400)
      // ->orderBy('VHNO', 'asc')
      ->orderBy('Date', 'asc') // Sort by Date in ascending order
      ->orderBy('JournalID', 'asc')   // Sort by ID in ascending order
      ->get();

    //          $pdf = PDF::loadView ('party_ledger1pdf',compact('journal','pagetitle','sql' ,'party')); 
    // //return $pdf->download('pdfview.pdf');
    //    $pdf->setpaper('A4', 'portiate');
    //       return $pdf->stream();

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();
    return view('blank_party_ledger1', compact('journal', 'pagetitle', 'sql', 'party'));
  }

  public function PartyLedger1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Ledger', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'PartyLedger');
    $pagetitle = 'Party Ledger';

    Session::put('StartDate', $request->StartDate);
    Session::put('EndDate', $request->EndDate);

    $vouchertype = DB::table('voucher_type')->where('VoucherTypeID', $request->VoucherTypeID)->get();

    $where = array();

    if ($request->VoucherTypeID > 0) {

      $where = Arr::add($where, 'JournalType', $vouchertype[0]->VoucherCode);
    }

    if ($request->PartyID > 0) {

      $where = Arr::add($where, 'PartyID', $request->PartyID);
    }

    if ($request->ChartOfAccountID > 0) {

      $where = Arr::add($where, 'ChartOfAccountID', $request->ChartOfAccountID);
    }

    $sql = DB::table('journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
      // ->where('PartyID',$request->PartyID)
      ->where($where)
      ->where('Date', '<', $request->StartDate)
      // ->whereBetween('date',array($request->StartDate,$request->EndDate))
      ->get();

    // dd($sql[0]->Balance);
    // $sql= DB::select( DB::raw( 'SET @total := '.$sql[0]->Balance.''));
    // $sql= DB::select( DB::raw( 'select @total as t'));

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    // $a = DB::select(DB::raw('select * from v_journal where PartyID = @total'));
    // $journal = DB::select(DB::raw('SELECT a.JournalID, a.ChartOfAccountID, a.*, IF(ISNULL(a.Dr),0,a.Dr) as Dr, a.Cr,sum(if(ISNULL(b.Dr),0,b.Dr)-if(ISNULL(b.Cr),0,b.Cr))+'.$sql[0]->Balance.' as Balance FROM   v_journal a,  v_journal b WHERE b.JournalID <= a.JournalID and a.PartyID='.$request->PartyID.' and b.PartyID='.$request->PartyID.' and a.ChartOfAccountID=110400 and b.ChartOfAccountID=110400 GROUP BY a.JournalID, a.ChartOfAccountID, a.Dr, a.Cr ORDER BY a.JournalID'));
    // $a = DB::table('v_journal')->where('PartyID',DB::raw( '@total'))->get();

    $party = DB::table('party')->where('PartyID', $request->PartyID)->get();

    $journal = DB::table('v_journal')->where('PartyID', $request->PartyID)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->where($where)
      ->where('ChartOfAccountID', 110400)
      ->orderBy('Date', 'asc')   // Sort by ID in ascending order
    ->orderBy('JournalID', 'asc')   // Sort by ID in ascending order
      ->get();

    //          $pdf = PDF::loadView ('party_ledger1pdf',compact('journal','pagetitle','sql' ,'party')); 
    // //return $pdf->download('pdfview.pdf');
    //    $pdf->setpaper('A4', 'portiate');
    //       return $pdf->stream();

    $pdf = PDF::loadView('reports.party_ledger1pdf', compact('journal', 'pagetitle', 'sql', 'party'));
    //return $pdf->download('pdfview.pdf');
    $pdf->setpaper('A4', 'landscape');
    return $pdf->stream();

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();
    // return view ('party_ledger1pdf',compact('journal','pagetitle','sql' ,'party')); 
  }

  public  function AdjustmentBalance()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Adjustment Balance', 'Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    $pagetitle = 'AdjustmentBalance';

    Session::put('menu', 'AdjustmentBalance');
    $voucher_type = DB::table('voucher_type')->where('VoucherTypeID', 7)->get();
    $party = DB::table('party')->get();

    return view('adjust_balance', compact('voucher_type', 'pagetitle', 'party'));
  }

  public function AdjustmentBalanceSave(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Adjustment Balance', 'Create');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'AdjustmentBalance');
    $pagetitle = 'AdjustmentBalance';
    list($InvoiceTypeID, $InvoiceTypeCode) = explode("-", $request->InvoiceType1);

    // dd($request->all());
    $voucher_mst = array(
      'VoucherCodeID' => $InvoiceTypeID,
      'Voucher' => $request->input('Voucher'),
      'Narration' => $request->input('Narration'),
      'Date' => $request->input('VHDate'),

    );

    // dd($invoice_mst);

    // $id= DB::table('')->insertGetId($data);

    $id = DB::table('voucher_master')->insertGetId($voucher_mst);

    if ($request->CustomType == 1) //discount allowed
    {
      $DISCOUNT_ALLOWED = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 510104, // discount allowed
        'PartyID' => $request->PartyID,
        'Narration' => 'Discount allowed',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $AR = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 110400, //A/R
        'PartyID' => $request->PartyID,
        'Narration' => 'Discount allowed',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id2 = DB::table('voucher_detail')->insert($DISCOUNT_ALLOWED);
      $id1 = DB::table('voucher_detail')->insert($AR);
    } elseif ($request->CustomType == 2) { //discount received

      $AR = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 110400,
        'PartyID' => $request->PartyID,
        'Narration' => 'Discount received',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $DISCOUNT_REC = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 410152,
        'PartyID' => $request->PartyID,
        'Narration' => 'Discount received',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id1 = DB::table('voucher_detail')->insert($AR);
      $id2 = DB::table('voucher_detail')->insert($DISCOUNT_REC);
    } elseif ($request->CustomType == 3) { //Increase receivable

      $AR = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 110400, //A/R
        'PartyID' => $request->PartyID,
        'Narration' => 'Increase receivable',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $INCREASE_REC = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 210103, //Balance adjustment
        'PartyID' => $request->PartyID,
        'Narration' => 'Increase receivable',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id1 = DB::table('voucher_detail')->insert($AR);
      $id2 = DB::table('voucher_detail')->insert($INCREASE_REC);
    } elseif ($request->CustomType == 4) { //Decrease receivable

      $DECREASE_REC = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 210103, //Balance adjustment
        'PartyID' => $request->PartyID,
        'Narration' => 'Decrease receivable',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $AR = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 110400, //A/R
        'PartyID' => $request->PartyID,
        'Narration' => 'Decrease receivable',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id2 = DB::table('voucher_detail')->insert($DECREASE_REC);
      $id1 = DB::table('voucher_detail')->insert($AR);
    } elseif ($request->CustomType == 5) { //Increased payable

      $BALANCE_ADJ = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 210103, //Balance adjustment
        'SupplierID' => $request->SupplierID,
        'Narration' => 'Increased payable',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $AP = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 210100,  // A/P
        'SupplierID' => $request->SupplierID,
        'Narration' => 'Increased payable',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id1 = DB::table('voucher_detail')->insert($AP);
      $id2 = DB::table('voucher_detail')->insert($BALANCE_ADJ);
    } elseif ($request->CustomType == 6) { // Decrease payable

      $AP = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 110400, // A/P
        'SupplierID' => $request->SupplierID,
        'Narration' => 'Decrease payable',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $BALANCE_ADJ = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 210103, //Balance adjustment
        'SupplierID' => $request->SupplierID,
        'Narration' => 'Decrease payable',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id1 = DB::table('voucher_detail')->insert($AP);
      $id2 = DB::table('voucher_detail')->insert($BALANCE_ADJ);
    } elseif ($request->CustomType == 7) { // Fee charged / billed increased

      $AR = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 110400, //A/R
        'PartyID' => $request->PartyID,
        'Narration' => 'Fee charged / billed increased',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $FEE_CHARGED = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 560111, //Fee charged
        'PartyID' => $request->PartyID,
        'Narration' => 'Fee charged / billed increased',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id1 = DB::table('voucher_detail')->insert($AR);
      $id2 = DB::table('voucher_detail')->insert($FEE_CHARGED);
    } elseif ($request->CustomType == 8) { // Fee charged / billed decreased

      $FEE_CHARGED = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 560111, //Fee charged
        'PartyID' => $request->PartyID,
        'Narration' => 'Fee charged / billed increased',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );
      $AR = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 110400, //AR
        'PartyID' => $request->PartyID,
        'Narration' => 'Fee charged / billed increased',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id2 = DB::table('voucher_detail')->insert($FEE_CHARGED);
      $id1 = DB::table('voucher_detail')->insert($AR);
    } elseif ($request->CustomType == 9) { //Fee bill / paid increase

      $FEE_CHARGED = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 560111, //Fee charged
        'SupplierID' => $request->SupplierID,
        'Narration' => 'Fee bill / paid increase',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $AP = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 210100, //AP
        'SupplierID' => $request->SupplierID,
        'Narration' => 'Fee bill / paid increase',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id2 = DB::table('voucher_detail')->insert($FEE_CHARGED);
      $id1 = DB::table('voucher_detail')->insert($AP);
    } else  //Fee bill / paid decrease  (10)
    {

      $AP = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 210100, //AP
        'SupplierID' => $request->SupplierID,
        'Narration' => 'Fee bill / paid decreased',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Debit' => $request->Amount,

      );

      $FEE_CHARGED = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => 560111, //Fee charged
        'SupplierID' => $request->SupplierID,
        'Narration' => 'Fee bill / paid decreased',
        'InvoiceNo' => null,
        'RefNo' => null,
        'Credit' => $request->Amount,

      );

      $id1 = DB::table('voucher_detail')->insert($AP);
      $id2 = DB::table('voucher_detail')->insert($FEE_CHARGED);
    }

    // echo "<pre>";
    // print_r($voucher_det);  

    // $idd= DB::table('voucher_detail')->insert($voucher_det);

    return redirect('Voucher')->with('error', 'Saved Successfully')->with('class', 'success');
  }

 public function SupplierBalance()
  {

    Session::put('menu', 'SupplierBalance');
    $pagetitle = 'SupplierBalance';
    return view('reports.supplier_balance', compact('pagetitle'));
  }

  public  function SupplierBalance1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier Balance', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'SupplierBalance');
    $pagetitle = 'Supplier Balance';

    $supplier = DB::table('supplier')->get();


 


  

$where = array();

$where = [];

 
 
 
 
if ($request->ReportType == 'D' || $request->ReportType == 'C') {
 
   $supplier = DB::table('journal')
    ->select('supplier.SupplierID', 'supplier.SupplierName', DB::raw('SUM(IFNULL(Dr, 0))  as Dr'),  DB::raw(' SUM(IFNULL(Cr, 0)) as Cr'), DB::raw('SUM(IFNULL(Dr, 0)) - SUM(IFNULL(Cr, 0)) as balance'))
    ->join('supplier', 'journal.SupplierID', '=', 'supplier.SupplierID')
    ->whereBetween('journal.Date', [$request->StartDate, $request->EndDate])
    ->where('journal.ChartOfAccountID', 210100)
    ->groupBy('supplier.SupplierID', 'supplier.SupplierName')
     ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), ($request->ReportType == 'C') ? '<' : '>', 0)
     ->get();
}

else
{


    $supplier = DB::table('journal')
    ->select('supplier.SupplierID', 'supplier.SupplierName', DB::raw('SUM(IFNULL(Dr, 0))  as Dr'),  DB::raw(' SUM(IFNULL(Cr, 0)) as Cr'), DB::raw('SUM(IFNULL(Dr, 0)) - SUM(IFNULL(Cr, 0)) as balance'))
    ->join('supplier', 'journal.SupplierID', '=', 'supplier.SupplierID')
    ->whereBetween('journal.Date', [$request->StartDate, $request->EndDate])
    ->where('journal.ChartOfAccountID', 210100)
    ->groupBy('supplier.SupplierID', 'supplier.SupplierName')
     // ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), ($request->ReportType == 'C') ? '<' : '>', 0)
     ->get();

} 






    return view('reports.supplier_balance1', compact('supplier', 'pagetitle'));
  }

  public  function SupplierBalance1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier Balance', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    Session::put('menu', 'SupplierBalance');
    $pagetitle = 'SupplierBalance';

 


$where = array();

$where = [];

 
 
 
 
if ($request->ReportType == 'D' || $request->ReportType == 'C') {
 
   $supplier = DB::table('journal')
    ->select('supplier.SupplierID', 'supplier.SupplierName', DB::raw('SUM(IFNULL(Dr, 0))  as Dr'),  DB::raw(' SUM(IFNULL(Cr, 0)) as Cr'), DB::raw('SUM(IFNULL(Dr, 0)) - SUM(IFNULL(Cr, 0)) as balance'))
    ->join('supplier', 'journal.SupplierID', '=', 'supplier.SupplierID')
    ->whereBetween('journal.Date', [$request->StartDate, $request->EndDate])
    ->where('journal.ChartOfAccountID', 210100)
    ->groupBy('supplier.SupplierID', 'supplier.SupplierName')
     ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), ($request->ReportType == 'C') ? '<' : '>', 0)
     ->get();
}

else
{


    $supplier = DB::table('journal')
    ->select('supplier.SupplierID', 'supplier.SupplierName', DB::raw('SUM(IFNULL(Dr, 0))  as Dr'),  DB::raw(' SUM(IFNULL(Cr, 0)) as Cr'), DB::raw('SUM(IFNULL(Dr, 0)) - SUM(IFNULL(Cr, 0)) as balance'))
    ->join('supplier', 'journal.SupplierID', '=', 'supplier.SupplierID')
    ->whereBetween('journal.Date', [$request->StartDate, $request->EndDate])
    ->where('journal.ChartOfAccountID', 210100)
    ->groupBy('supplier.SupplierID', 'supplier.SupplierName')
     // ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), ($request->ReportType == 'C') ? '<' : '>', 0)
     ->get();

} 




    $pdf = PDF::loadView('supplier_balance1pdf', compact('supplier'));
    //return $pdf->download('pdfview.pdf');
    $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return view('reports.supplier_balance1pdf', compact('supplier'));
  }

  public function PartyList()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party List', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Party List';
    $party = DB::table('party')->get();

    return view('reports.party_list', compact('party', 'pagetitle'));
  }

  public function PartyListPDF()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party List', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    $pagetitle = 'Party List';

    $party = DB::table('party')->get();
    $party = DB::table('party')->get();

    $pdf = PDF::loadView('reports.party_listPDF', compact('party', 'pagetitle'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return view('reports.party_list', cocompact('party', 'pagetitle'));
  }

  public function OutStandingInvoice()
  {
    $pagetitle = 'Out Standing Invoice';
    Session::put('menu', 'OutStandingInvoice');
    $party = DB::table('party')->get();
    return view('reports.outstanding_invoice', compact('party', 'pagetitle'));
  }

  public function OutStandingInvoice1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Outstanding Invoices', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////


    $party = DB::table('journal')
    ->select(DB::raw('SUM(IFNULL(Cr, 0)) as balance'))
    ->join('party', 'journal.PartyID', '=', 'party.PartyID')
    // ->whereBetween('journal.date', [$request->StartDate, $request->EndDate])
    ->where('journal.PartyID', $request->PartyID)
    ->where('journal.ChartOfAccountID', 110400)
    ->first();

     
    $inputAmount=$party->balance;

    // dd($inputAmount);

     $invoices = DB::table('invoice_master')
    ->where('Total', '>', 'Paid1')
    ->where('PartyID',$request->PartyID)
    ->orderBy('Date')
    ->get();


     foreach ($invoices as $invoice) {
      $remaining = $invoice->Total - $invoice->Paid1;
  
      if ($inputAmount <= 0) break;
  
      $payNow = min($inputAmount, $remaining);
      $newPaid = $invoice->Paid + $payNow;
  
      // Update the invoice
      DB::table('invoice_master')
      ->where('PartyID',$request->PartyID)
  
          ->where('InvoiceMasterID', $invoice->InvoiceMasterID)
          ->update([
              'Paid1' => $newPaid
          ]);
  
      $inputAmount -= $payNow;
  }





    $pagetitle = 'Out Standing Invoice';
    if ($request->PartyID > 0) {
      $invoice = DB::table('v_invoice_master')->where('PartyID', $request->PartyID)->whereColumn('Total', '>', 'Paid1')->whereBetween('date', array($request->StartDate, $request->EndDate))->get();
    } else {

      $invoice = DB::table('v_invoice_master')->whereColumn('Total', '>', 'Paid1')->whereBetween('date', array($request->StartDate, $request->EndDate))->get();
    }

    // $pdf = PDF::loadView ('outstanding_invoice1',compact('invoice'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    // return $pdf->stream();

    return view('reports.outstanding_invoice1', compact('invoice', 'pagetitle'));
  }

  public function OutStandingInvoice1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Outstanding Invoices', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $party = DB::table('journal')
    ->select(DB::raw('SUM(IFNULL(Cr, 0)) as balance'))
    ->join('party', 'journal.PartyID', '=', 'party.PartyID')
    // ->whereBetween('journal.date', [$request->StartDate, $request->EndDate])
    ->where('journal.PartyID', $request->PartyID)
    ->where('journal.ChartOfAccountID', 110400)
    ->first();

     
    $inputAmount=$party->balance;

    // dd($inputAmount);

     $invoices = DB::table('invoice_master')
    ->where('Total', '>', 'Paid1')
    ->where('PartyID',$request->PartyID)
    ->orderBy('Date')
    ->get();


     foreach ($invoices as $invoice) {
      $remaining = $invoice->Total - $invoice->Paid1;
  
      if ($inputAmount <= 0) break;
  
      $payNow = min($inputAmount, $remaining);
      $newPaid = $invoice->Paid + $payNow;
  
      // Update the invoice
      DB::table('invoice_master')
      ->where('PartyID',$request->PartyID)
  
          ->where('InvoiceMasterID', $invoice->InvoiceMasterID)
          ->update([
              'Paid1' => $newPaid
          ]);
  
      $inputAmount -= $payNow;
  }

    $pagetitle = 'Out Standing Invoice';
    if ($request->PartyID > 0) {
      $invoice = DB::table('v_invoice_master')->where('PartyID', $request->PartyID)->whereColumn('Total', '>', 'Paid1')->whereBetween('date', array($request->StartDate, $request->EndDate))->get();
    } else {

      $invoice = DB::table('v_invoice_master')->whereColumn('Total', '>', 'Paid1')->whereBetween('date', array($request->StartDate, $request->EndDate))->get();
    }

    $pdf = PDF::loadView('reports.outstanding_invoice1PDF', compact('invoice', 'pagetitle'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return view('reports.outstanding_invoice1', compact('invoice', 'pagetitle'));
  }

  public function PartyWiseSale()
  {

    Session::put('menu', 'PartyLedger');
    $pagetitle = 'Party Ledger';
    $invoice_type = DB::table('invoice_type')->get();
    $party = DB::table('party')->get();

    return view('reports.partywise_sale', compact('pagetitle', 'invoice_type', 'party'));
  }

  public function PartyWiseSale1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Wise Sale', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Party wise sale';

    if (($request->PartyID > 0) && ($request->InvoiceTypeID == 'both')) {

      $party_wise = DB::table('v_partywise_sale')->where('PartyID', $request->PartyID)->get();
    } elseif (($request->PartyID > 0) && ($request->InvoiceTypeID == 1)) {

      $party_wise = DB::table('v_partywise_sale')->where('PartyID', $request->PartyID)->where('InvoiceTypeID', 1)->get();
    } elseif (($request->PartyID > 0) && ($request->InvoiceTypeID == 2)) {

      $party_wise = DB::table('v_partywise_sale')->where('PartyID', $request->PartyID)->where('InvoiceTypeID', 2)->get();
    } elseif (($request->PartyID == 0) && ($request->InvoiceTypeID == 'both')) {

      $party_wise = DB::table('v_partywise_sale')->get();
    } elseif (($request->PartyID == 0) && ($request->InvoiceTypeID == 1)) {

      $party_wise = DB::table('v_partywise_sale')->where('InvoiceTypeID', 1)->get();
    } elseif (($request->PartyID == 0) && ($request->InvoiceTypeID == 2)) {

      $party_wise = DB::table('v_partywise_sale')->where('InvoiceTypeID', 2)->get();
    }

    //       $pdf = PDF::loadView ('partywise_sale1',compact('party_wise'));
    // //return $pdf->download('pdfview.pdf');
    //   // $pdf->setpaper('A4', 'portiate');
    //       return $pdf->stream();

    return View('reports.partywise_sale1', compact('party_wise', 'pagetitle'));
  }

  public function PartyWiseSale1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Wise Sale', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Party wise sale';
    if (($request->PartyID > 0) && ($request->InvoiceTypeID == 'both')) {

      $party_wise = DB::table('v_partywise_sale')->where('PartyID', $request->PartyID)->get();
    } elseif (($request->PartyID > 0) && ($request->InvoiceTypeID == 1)) {

      $party_wise = DB::table('v_partywise_sale')->where('PartyID', $request->PartyID)->where('InvoiceTypeID', 1)->get();
    } elseif (($request->PartyID > 0) && ($request->InvoiceTypeID == 2)) {

      $party_wise = DB::table('v_partywise_sale')->where('PartyID', $request->PartyID)->where('InvoiceTypeID', 2)->get();
    } elseif (($request->PartyID == 0) && ($request->InvoiceTypeID == 'both')) {

      $party_wise = DB::table('v_partywise_sale')->get();
    } elseif (($request->PartyID == 0) && ($request->InvoiceTypeID == 1)) {

      $party_wise = DB::table('v_partywise_sale')->where('InvoiceTypeID', 1)->get();
    } elseif (($request->PartyID == 0) && ($request->InvoiceTypeID == 2)) {

      $party_wise = DB::table('v_partywise_sale')->where('InvoiceTypeID', 2)->get();
    }

    $pdf = PDF::loadView('reports.partywise_sale1PDF', compact('party_wise', 'pagetitle'));
    // //return $pdf->download('pdfview.pdf');
    //   // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return View('partywise_sale1', compact('party_wise'));
  }

  public function PartyBalance()
  {
    Session::put('menu', 'PartyLedger');
    $pagetitle = 'Party Balance';

    $party = DB::table('party')->get();

    return view('reports.party_balance', compact('pagetitle', 'party'));
  }

  public function PartyBalance1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Balance', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Party Balance';

    

   // $party = DB::table('journal')
   //  ->select('party.PartyID', 'party.PartyName', DB::raw('SUM(IFNULL(Dr, 0))  as Dr'),  DB::raw(' SUM(IFNULL(Cr, 0)) as Cr'), DB::raw('SUM(IFNULL(Dr, 0)) - SUM(IFNULL(Cr, 0)) as balance'))
   //  ->join('party', 'journal.PartyID', '=', 'party.PartyID')
   //  ->whereBetween('journal.date', [$request->StartDate, $request->EndDate])
   //  ->groupBy('party.PartyID', 'party.PartyName')
   //   ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), ($request->ReportType == 'C') ? '<' : '>', 0)
   //   ->get();


 // dd($party);
    if ($request->PartyID > 0) {

      $party = DB::table('journal')
    ->select('party.PartyID', 'party.PartyName', DB::raw('SUM(IFNULL(Dr, 0))  as Dr'),  DB::raw(' SUM(IFNULL(Cr, 0)) as Cr'), DB::raw('SUM(IFNULL(Dr, 0)) - SUM(IFNULL(Cr, 0)) as balance'))
    ->join('party', 'journal.PartyID', '=', 'party.PartyID')
    // ->whereBetween('journal.date', [$request->StartDate, $request->EndDate])
    ->where('journal.PartyID', $request->PartyID)
    ->where('journal.ChartOfAccountID', 110400)
    ->groupBy('party.PartyID', 'party.PartyName')
      ->get();
    } else {

   $party = DB::table('journal')
    ->select('party.PartyID', 'party.PartyName', DB::raw('SUM(IFNULL(Dr, 0))  as Dr'),  DB::raw(' SUM(IFNULL(Cr, 0)) as Cr'), DB::raw('SUM(IFNULL(Dr, 0)) - SUM(IFNULL(Cr, 0)) as balance'))
    ->join('party', 'journal.PartyID', '=', 'party.PartyID')
    // ->whereBetween('journal.date', [$request->StartDate, $request->EndDate])
    ->where('journal.ChartOfAccountID', 110400)
    ->groupBy('party.PartyID', 'party.PartyName')
     ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), ($request->ReportType == 'C') ? '<' : '>', 0)
     ->get();
    }




    return  View('reports.party_balance1', compact('party', 'pagetitle'));
  }
  
  
  public function PartyBalanceShowAll()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Balance', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Party Balance';

  
 

   $party = DB::table('journal')
    ->select('party.PartyID', 'party.PartyName', DB::raw('SUM(IFNULL(Dr, 0))  as Dr'),  DB::raw(' SUM(IFNULL(Cr, 0)) as Cr'), DB::raw('SUM(IFNULL(Dr, 0)) - SUM(IFNULL(Cr, 0)) as balance'))
    ->join('party', 'journal.PartyID', '=', 'party.PartyID')
    // ->whereBetween('journal.date', [$request->StartDate, $request->EndDate])
    ->where('journal.ChartOfAccountID', 110400)
    ->groupBy('party.PartyID', 'party.PartyName')
     ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'),  '>', 0)
     ->get();
  




    return  View('reports.party_balance_showall', compact('party', 'pagetitle'));
  }

  public function PartyBalance1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Balance', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Party Balance';

    if ($request->PartyID > 0) {

      $party = DB::table('v_party_balance')->select('PartyID', 'PartyName', DB::raw('sum(Dr) as Dr'), DB::raw('sum(Cr) as Cr'))
        ->whereBetween('date', array($request->StartDate, $request->EndDate))
        ->where('PartyID', $request->PartyID)
        ->groupBy('PartyID', 'PartyName')
        ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), ($request->ReportType == 'C') ? '<' : '>', 0)
        ->get();
    } else {

      $party = DB::table('v_party_balance')->select('PartyID', 'PartyName', DB::raw('sum(Dr) as Dr'), DB::raw('sum(Cr) as Cr'))
        ->whereBetween('date', array($request->StartDate, $request->EndDate))
        // ->where('PartyID',$request->PartyID)
        ->groupBy('PartyID', 'PartyName')
        ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), ($request->ReportType == 'C') ? '<' : '>', 0)
        ->get();
    }

    $pdf = PDF::loadView('reports.party_balance1PDF', compact('party'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return  View('party_balance1PDF', compact('party', 'pagetitle'));
  }

  public function PartyBalanceList()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Party Balance', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Party Balance';

    $party = DB::table('v_party_balance')->select('PartyID', 'PartyName', DB::raw('sum(Dr) as Dr'), DB::raw('sum(Cr) as Cr'))
      // ->whereBetween('date',array($request->StartDate,$request->EndDate))
      // ->where('PartyID',$request->PartyID)
      ->groupBy('PartyID', 'PartyName')
      ->having(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'), '>', 0)
      ->orderByDesc(DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))'))
      ->get();

    return  View('party_balance1', compact('party', 'pagetitle'));
  }

  public function PartyYearlyBalance()
  {
    Session::put('menu', 'SupplierBalance');
    $pagetitle = 'SupplierBalance';
    return view('reports.party_yearly_balance', compact('pagetitle'));
  }

  public  function PartyYearlyBalance1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Yearly Report', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'SupplierBalance');
    $pagetitle = 'SupplierBalance';

    $party = DB::table('party')->get();

    return view('reports.party_yearly_balance1', compact('party'));
  }

  public  function PartyYearlyBalance1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Yearly Report', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'SupplierBalance');
    $pagetitle = 'SupplierBalance';

    $party = DB::table('party')->get();
    $pdf = PDF::loadView('reports.party_yearly_balance1PDF', compact('party'));
    //return $pdf->download('pdfview.pdf');
    $pdf->setpaper('A4', 'landscape');
    return $pdf->stream();

    return view('reports.party_yearly_balance1PDF', compact('party'));
  }

  // SUPPLIER REPORTS

  public function SupplierLedger()
  {

    Session::put('menu', 'SupplierLedger');
    $pagetitle = 'Supplier Ledger';

    $supplier = DB::table('supplier')->get();

    $voucher_type = DB::table('voucher_type')->get();

    $chartofaccount = DB::table('chartofaccount')->where('ChartOfAccountID', 210100)->get();
    return view('reports.supplier_ledger', compact('pagetitle', 'supplier', 'voucher_type', 'chartofaccount'));
  }

  public function SupplierLedger1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier Ledger', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());

    Session::put('menu', 'SupplierLedger');
    $pagetitle = 'Supplier Ledger';

    $sql = DB::table('journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
      ->where('SupplierID', $request->SupplierID)
      ->where('ChartOfAccountID', $request->ChartOfAccountID)
      ->where('Date', '<', $request->StartDate)
      // ->whereBetween('date',array($request->StartDate,$request->EndDate))

      ->get();

    // dd($sql[0]->Balance);
    // $sql= DB::select( DB::raw( 'SET @total := '.$sql[0]->Balance.''));
    // $sql= DB::select( DB::raw( 'select @total as t'));

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    // $a = DB::select(DB::raw('select * from v_journal where PartyID = @total'));
    // $journal = DB::select(DB::raw('SELECT a.JournalID, a.ChartOfAccountID, a.*, IF(ISNULL(a.Dr),0,a.Dr) as Dr, a.Cr,sum(if(ISNULL(b.Dr),0,b.Dr)-if(ISNULL(b.Cr),0,b.Cr))+'.$sql[0]->Balance.' as Balance FROM   v_journal a,  v_journal b WHERE b.JournalID <= a.JournalID and a.PartyID='.$request->PartyID.' and b.PartyID='.$request->PartyID.' and a.ChartOfAccountID=110400 and b.ChartOfAccountID=110400 GROUP BY a.JournalID, a.ChartOfAccountID, a.Dr, a.Cr ORDER BY a.JournalID'));
    // $a = DB::table('v_journal')->where('PartyID',DB::raw( '@total'))->get();

    $supplier = DB::table('supplier')->where('SupplierID', $request->SupplierID)->get();

    $journal = DB::table('v_journal')->where('SupplierID', $request->SupplierID)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->where('ChartOfAccountID', $request->ChartOfAccountID)
      ->orderBy('Date', 'asc')
      ->get();

    //          $pdf = PDF::loadView ('party_ledger1pdf',compact('journal','pagetitle','sql' ,'party')); 
    // //return $pdf->download('pdfview.pdf');
    //    $pdf->setpaper('A4', 'portiate');
    //       return $pdf->stream();

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();
    return view('reports.supplier_ledger1', compact('journal', 'pagetitle', 'sql', 'supplier'));
  }

  function SupplierLedgerExcelExport(request $request)
  {

    return Excel::download(new SupplierLedgerExcel($request->SupplierID, $request->StartDate, $request->EndDate), 'supplierledger.xlsx');
  }

  public function SupplierLedger1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Supplier Ledger', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());

    Session::put('menu', 'SupplierLedger');
    $pagetitle = 'Supplier Ledger';

    $sql = DB::table('journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
      ->where('SupplierID', $request->SupplierID)
      ->where('ChartOfAccountID', $request->ChartOfAccountID)
      ->where('Date', '<', $request->StartDate)
      // ->whereBetween('date',array($request->StartDate,$request->EndDate))

      ->get();

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    $supplier = DB::table('supplier')->where('SupplierID', $request->SupplierID)->get();

    $journal = DB::table('v_journal')->where('SupplierID', $request->SupplierID)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->where('ChartOfAccountID', $request->ChartOfAccountID)
      ->orderBy('Date', 'asc')
      ->get();

    $pdf = PDF::loadView('reports.supplier_ledger1pdf', compact('journal', 'pagetitle', 'sql', 'supplier'));
    // //return $pdf->download('pdfview.pdf');
    //    $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();
    return view('supplier_ledger1', compact('journal', 'pagetitle', 'sql', 'supplier'));
  }

  public function SupplierWiseSale()
  {
    Session::put('menu', 'SupplierLedger');
    $pagetitle = 'Supplier Ledger';
    $invoice_type = DB::table('invoice_type')->get();
    $supplier = DB::table('supplier')->get();

    return view('reports.supplierwise_sale', compact('pagetitle', 'invoice_type', 'supplier'));
  }

  public function SupplierWiseSale1(request $request)
  {


     ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Sales Report', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Supplier wise sale';

    $query = DB::table('v_invoice_detail')
    ->select(
        'SupplierID', 
        'InvoiceTypeCode', 
        'SupplierName', 
        DB::raw('sum(Fare) as VHNO'), 
        DB::raw('sum(Taxable) as Taxable'), 
        DB::raw('sum(Service) as Service'), 
        DB::raw('sum(Fare) as Fare'), 
        DB::raw('sum(OPVAT) as OPVAT'), 
        DB::raw('sum(IPVAT) as IPVAT'), 
        DB::raw('sum(Discount) as Discount'), 
        DB::raw('sum(Total) as Total')
    )
    ->whereBetween('Date', [$request->StartDate, $request->EndDate]);

if ($request->SupplierID > 0) {
    $query->where('SupplierID', $request->SupplierID);
}

if ($request->InvoiceTypeID !== 'both') {
    $query->where('InvoiceTypeID', $request->InvoiceTypeID);
}

$supplier = $query->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')->get();



    return View('reports.supplierwise_sale1', compact('supplier', 'pagetitle'));
  }

  public function SupplierWiseSale1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Sales Report', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    $pagetitle = 'Supplier wise sale';

    $query = DB::table('v_invoice_detail')
    ->select(
        'SupplierID', 
        'InvoiceTypeCode', 
        'SupplierName', 
        DB::raw('sum(Fare) as VHNO'), 
        DB::raw('sum(Taxable) as Taxable'), 
        DB::raw('sum(Service) as Service'), 
        DB::raw('sum(Fare) as Fare'), 
        DB::raw('sum(OPVAT) as OPVAT'), 
        DB::raw('sum(IPVAT) as IPVAT'), 
        DB::raw('sum(Discount) as Discount'), 
        DB::raw('sum(Total) as Total')
    )
    ->whereBetween('Date', [$request->StartDate, $request->EndDate]);

if ($request->SupplierID > 0) {
    $query->where('SupplierID', $request->SupplierID);
}

if ($request->InvoiceTypeID !== 'both') {
    $query->where('InvoiceTypeID', $request->InvoiceTypeID);
}

$supplier = $query->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')->get();


    $pdf = PDF::loadView('reports.supplierwise_sale1pdf', compact('supplier', 'pagetitle'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return View('supplierwise_sale1', compact('supplier'));
  }

  public function TaxReport()
  {

    Session::put('menu', 'TaxReport');
    $pagetitle = 'Tax Report';
    $invoice_type = DB::table('invoice_type')->get();
    $item = DB::table('item')->get();

    return view('reports.tax_report', compact('pagetitle', 'invoice_type', 'item'));
  }

  public function TaxReport1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Tax Report', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Tax Report';


      $where = array();
      if ($request->ItemID > 0) 
      {
         $where = Arr::add($where, 'ItemID', $request->ItemID);


      }


       if ($request->InvoiceTypeID > 0) 
      {
         $where = Arr::add($where, 'InvoiceTypeID', $request->InvoiceTypeID);


      }


 

    
      $invoice_detail = DB::table('v_invoice_detail')
        // ->where('ItemID',$request->ItemID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where($where)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
   

    return View('reports.tax_report1', compact('invoice_detail', 'pagetitle'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');

  }

  public function TaxReport1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Tax Report', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Tax Report';

    $query = DB::table('v_invoice_detail')
        ->whereBetween('Date', [$request->StartDate, $request->EndDate])
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date');

    if ($request->ItemID > 0) {
        $query->where('ItemID', $request->ItemID);
    }

    // Check if InvoiceTypeID is set and not empty
    if ($request->InvoiceTypeID > 0) {
        $query->where('InvoiceTypeID', $request->InvoiceTypeID);
    }

    $invoice_detail = $query->get();

 

    $pdf = PDF::loadView('reports.tax_report1pdf', compact('invoice_detail', 'pagetitle'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();
  }

  public function SalemanInvoiceBalance()
  {

    Session::put('menu', 'SalemanReport');
    $pagetitle = 'Saleman Invoice Balance';
    $invoice_type = DB::table('invoice_type')->get();
    $item = DB::table('item')->get();
    // $saleman = DB::table('user')->where('UserType','Saleman')->get();

          $query = DB::table('user')->where('Active', 'Yes');

if (Session::get('UserType') !== 'Admin') {
    $query->where('UserID', Session::get('UserID'));
}

$saleman = $query->get();



    return view('reports.saleman_invoice_balance', compact('pagetitle', 'invoice_type', 'saleman', 'item'));
  }

  public  function SalemanInvoiceBalance1(request $request)
    {

    $pagetitle = 'Saleman Invoice Balance';

 $query = DB::table('v_invoice_master')
    ->select(
        'v_invoice_master.FullName',
        DB::raw('SUM(v_invoice_master.Total) as Total'),
        DB::raw('SUM(v_invoice_balance.Balance) as Balance')
    )
    ->join('v_invoice_balance', 'v_invoice_master.InvoiceMasterID', '=', 'v_invoice_balance.InvoiceMasterID')
    ->whereBetween('Date', [$request->StartDate, $request->EndDate])
    ->groupBy('v_invoice_master.FullName');

if (Session::get('UserType') !== 'Admin') {
    $query->where('v_invoice_master.UserID', Session::get('UserID'));
}

$invoice_master = $query->get();

    return view ('reports.saleman_invoice_balance1',compact('pagetitle','invoice_master'));
    }


  public  function SalemanInvoiceList($user,$start,$end)
  {

    $pagetitle = 'Saleman Invoice Balance';

    $invoice_master = DB::table('v_invoice_master')
    ->join('v_invoice_balance', 'v_invoice_master.InvoiceMasterID', '=', 'v_invoice_balance.InvoiceMasterID')
    ->whereBetween('Date', array($start, $end))
    ->where('FullName',$user)
     ->get();

    return view ('reports.saleman_invoice_list1',compact('pagetitle','invoice_master'));
    }



  public function SalemanReport()
  {

    Session::put('menu', 'SalemanReport');
    $pagetitle = 'Saleman Report';
    $invoice_type = DB::table('invoice_type')->get();
    $item = DB::table('item')->get();
    // $saleman = DB::table('user')->where('UserType','Saleman')->get();

          $query = DB::table('user')->where('Active', 'Yes');

if (Session::get('UserType') !== 'Admin') {
    $query->where('UserID', Session::get('UserID'));
}

$saleman = $query->get();

    return view('reports.saleman_report', compact('pagetitle', 'invoice_type', 'saleman', 'item'));
  }

  public function SalemanReport1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Sale Man Report', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    $pagetitle = 'Saleman Report';

    if (($request->UserID > 0) && ($request->InvoiceTypeID == 'both')) {

      $invoice_detail = DB::table('v_invoice_detail')
        ->where('UserID', $request->UserID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID > 0) && ($request->InvoiceTypeID == 1)) {

      $invoice_detail = DB::table('v_invoice_detail')
        ->where('UserID', $request->UserID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID > 0) && ($request->InvoiceTypeID == 2)) {

      $invoice_detail = DB::table('v_invoice_detail')
        ->where('UserID', $request->UserID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID == 0) && ($request->InvoiceTypeID == 'both')) {

      $invoice_detail = DB::table('v_invoice_detail')
        // ->where('ItemID',$request->ItemID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->where('InvoiceTypeID',$request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID == 0) && ($request->InvoiceTypeID == 1)) {

      $invoice_detail = DB::table('v_invoice_detail')
        // ->where('ItemID',$request->ItemID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID == 0) && ($request->InvoiceTypeID == 2)) {

      $invoice_detail = DB::table('v_invoice_detail')
        // ->where('ItemID',$request->ItemID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    }

    //       $pdf = PDF::loadView ('saleman_report1',compact('invoice_detail','pagetitle'));
    // //return $pdf->download('pdfview.pdf');
    //   // $pdf->setpaper('A4', 'portiate');
    //       return $pdf->stream();

    return View('reports.saleman_report1', compact('invoice_detail', 'pagetitle'));
  }

  public function SalemanReport1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Sale Man Report', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    $pagetitle = 'Saleman Report';

    $pagetitle = 'Saleman Report';

    if (($request->UserID > 0) && ($request->InvoiceTypeID == 'both')) {

      $invoice_detail = DB::table('v_invoice_detail')
        ->where('UserID', $request->UserID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID > 0) && ($request->InvoiceTypeID == 1)) {

      $invoice_detail = DB::table('v_invoice_detail')
        ->where('UserID', $request->UserID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID > 0) && ($request->InvoiceTypeID == 2)) {

      $invoice_detail = DB::table('v_invoice_detail')
        ->where('UserID', $request->UserID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID == 0) && ($request->InvoiceTypeID == 'both')) {

      $invoice_detail = DB::table('v_invoice_detail')
        // ->where('ItemID',$request->ItemID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->where('InvoiceTypeID',$request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID == 0) && ($request->InvoiceTypeID == 1)) {

      $invoice_detail = DB::table('v_invoice_detail')
        // ->where('ItemID',$request->ItemID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    } elseif (($request->UserID == 0) && ($request->InvoiceTypeID == 2)) {

      $invoice_detail = DB::table('v_invoice_detail')
        // ->where('ItemID',$request->ItemID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->orderBy('InvoiceMasterID')
        ->orderBy('Date')
        ->get();
    }

    $pdf = PDF::loadView('reports.saleman_report1pdf', compact('invoice_detail'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();
  }

  public function AirlineSummary()
  {

    Session::put('menu', 'AirlineSummary');
    $pagetitle = 'Airline Summary';
    $invoice_type = DB::table('invoice_type')->get();
    $supplier = DB::table('supplier')->get();

    return view('reports.airline_summary', compact('pagetitle', 'invoice_type', 'supplier'));
  }

  public function AirlineSummary1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Airline Summary', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    $pagetitle = 'Airline Summary';

    if (($request->SupplierID > 0) && ($request->InvoiceTypeID == 'both')) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        ->where('SupplierID', $request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID > 0) && ($request->InvoiceTypeID == 1)) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        ->where('SupplierID', $request->SupplierID)
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID > 0) && ($request->InvoiceTypeID == 2)) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        ->where('SupplierID', $request->SupplierID)
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID == 0) && ($request->InvoiceTypeID == 'both')) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID == 0) && ($request->InvoiceTypeID == 1)) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        // ->where('SupplierID',$request->SupplierID)
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID == 0) && ($request->InvoiceTypeID == 2)) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        // ->where('SupplierID',$request->SupplierID)
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    }

    // $pdf = PDF::loadView ('airline_summary1',compact('supplier'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    // return $pdf->stream();

    return View('reports.airline_summary1', compact('supplier', 'pagetitle'));
  }

  public function AirlineSummary1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Airline Summary', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Airline Summary';

    if (($request->SupplierID > 0) && ($request->InvoiceTypeID == 'both')) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        ->where('SupplierID', $request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID > 0) && ($request->InvoiceTypeID == 1)) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        ->where('SupplierID', $request->SupplierID)
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID > 0) && ($request->InvoiceTypeID == 2)) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        ->where('SupplierID', $request->SupplierID)
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID == 0) && ($request->InvoiceTypeID == 'both')) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID == 0) && ($request->InvoiceTypeID == 1)) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        // ->where('SupplierID',$request->SupplierID)
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    } elseif (($request->SupplierID == 0) && ($request->InvoiceTypeID == 2)) {

      $supplier = DB::table('v_invoice_detail')
        ->select('SupplierID', 'InvoiceTypeCode', 'SupplierName', DB::raw('sum(Fare) as VHNO'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Service) as Service'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(OPVAT) as OPVAT'), DB::raw('sum(IPVAT) as IPVAT'), DB::raw('sum(Discount) as Discount'), DB::raw('sum(Total) as Total'))
        // ->where('SupplierID',$request->SupplierID)
        ->where('InvoiceTypeID', $request->InvoiceTypeID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->groupBy('SupplierID', 'InvoiceTypeCode', 'SupplierName')
        ->get();
    }

    $pdf = PDF::loadView('reports.airline_summary1pdf', compact('supplier', 'pagetitle'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return View('reports.airline_summary1pdf', compact('supplier', 'pagetitle'));
  }

  public function VoucherReport()
  {
    Session::put('menu', 'VoucherReport');
    $pagetitle = 'Voucher Report';
    $voucher_type = DB::table('voucher_type')->get();
    return view('reports.voucher_report', compact('pagetitle', 'voucher_type'));
  }

  public function VoucherReport1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Voucher Report', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    Session::put('menu', 'VoucherReport');
    $pagetitle = 'Voucher Report';

    // dd($request->all());
    // dd($request->VoucherTypeID);

    if ($request->VoucherTypeID == 0) {

      Session::put('menu', 'VoucherReport');
      $pagetitle = 'Voucher Report';

      return redirect('VoucherReport')->with('error', 'Please select voucher type')->with('class', 'danger');
    }

    $voucher_type = DB::table('voucher_type')
      ->where('VoucherTypeID', $request->VoucherTypeID)
      ->get();

    $voucher_master = DB::table('voucher_master')
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->where('VoucherCodeID', $request->VoucherTypeID)

      ->get();

    return view('reports.voucher_report1', compact('pagetitle', 'voucher_type', 'voucher_master', 'pagetitle'));
  }

  public function VoucherReport1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Voucher Report', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    Session::put('menu', 'VoucherReport');
    $pagetitle = 'Voucher Report';

    // dd($request->all());
    // dd($request->VoucherTypeID);

    $voucher_type = DB::table('voucher_type')
      ->where('VoucherTypeID', $request->VoucherTypeID)
      ->get();

    // dd(  $voucher_type);

    $voucher_master = DB::table('voucher_master')
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->where('VoucherCodeID', $request->VoucherTypeID)

      ->get();

    // dd($voucher_master);

    $pdf = PDF::loadView('reports.voucher_report1pdf', compact('pagetitle', 'voucher_type', 'voucher_master'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return view('reports.voucher_report1pdf', compact('pagetitle', 'voucher_type', 'voucher_master'));
  }

  public function CashbookReport()
  {

    Session::put('menu', 'CashbookReport');
    $pagetitle = 'Cashbook Report';
    $chartofaccount = DB::table('chartofaccount')
      ->whereIn('Category', ['CASH','BANK','CARD'])
      ->get();

    return view('reports.cashbook_report', compact('pagetitle', 'chartofaccount'));
  }

  public function CashbookReport1(request $request)
  {

     ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Cash Book', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());

    Session::put('menu', 'CashbookReport');
    $pagetitle = 'Cashbook Report';

    if ($request->ChartOfAccountID > 0) {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->orderBy('Date', 'asc')
        ->get();
    } else {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->whereIn('ChartOfAccountID', [110101, 110250, 110201, 110101])
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', [110101, 110250, 110201, 110101])
        ->orderBy('Date', 'asc')
        ->get();
    }

    // dd($sql[0]->Balance);
    // $sql= DB::select( DB::raw( 'SET @total := '.$sql[0]->Balance.''));
    // $sql= DB::select( DB::raw( 'select @total as t'));

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    // $a = DB::select(DB::raw('select * from v_journal where PartyID = @total'));
    // $journal = DB::select(DB::raw('SELECT a.JournalID, a.ChartOfAccountID, a.*, IF(ISNULL(a.Dr),0,a.Dr) as Dr, a.Cr,sum(if(ISNULL(b.Dr),0,b.Dr)-if(ISNULL(b.Cr),0,b.Cr))+'.$sql[0]->Balance.' as Balance FROM   v_journal a,  v_journal b WHERE b.JournalID <= a.JournalID and a.PartyID='.$request->PartyID.' and b.PartyID='.$request->PartyID.' and a.ChartOfAccountID=110400 and b.ChartOfAccountID=110400 GROUP BY a.JournalID, a.ChartOfAccountID, a.Dr, a.Cr ORDER BY a.JournalID'));
    // $a = DB::table('v_journal')->where('PartyID',DB::raw( '@total'))->get();

    // $supplier = DB::table('supplier')->where('SupplierID',$request->SupplierID)->get();

    //          $pdf = PDF::loadView ('party_ledger1pdf',compact('journal','pagetitle','sql' ,'party')); 
    // //return $pdf->download('pdfview.pdf');
    //    $pdf->setpaper('A4', 'portiate');
    //       return $pdf->stream();

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();
    return view('reports.cashbook_report1', compact('journal', 'pagetitle', 'sql'));
  }

  public function CashbookReport1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Cash Book', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    // dd($request->all());

    Session::put('menu', 'CashbookReport');
    $pagetitle = 'Cashbook Report';

    if ($request->ChartOfAccountID > 0) {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->orderBy('Date', 'asc')
        ->get();
    } else {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->whereIn('ChartOfAccountID', [110101, 110250, 110201, 110101])
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', [110101, 110250, 110201, 110101])
        ->orderBy('Date', 'asc')
        ->get();
    }

    // dd($sql[0]->Balance);
    // $sql= DB::select( DB::raw( 'SET @total := '.$sql[0]->Balance.''));
    // $sql= DB::select( DB::raw( 'select @total as t'));

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    // $a = DB::select(DB::raw('select * from v_journal where PartyID = @total'));
    // $journal = DB::select(DB::raw('SELECT a.JournalID, a.ChartOfAccountID, a.*, IF(ISNULL(a.Dr),0,a.Dr) as Dr, a.Cr,sum(if(ISNULL(b.Dr),0,b.Dr)-if(ISNULL(b.Cr),0,b.Cr))+'.$sql[0]->Balance.' as Balance FROM   v_journal a,  v_journal b WHERE b.JournalID <= a.JournalID and a.PartyID='.$request->PartyID.' and b.PartyID='.$request->PartyID.' and a.ChartOfAccountID=110400 and b.ChartOfAccountID=110400 GROUP BY a.JournalID, a.ChartOfAccountID, a.Dr, a.Cr ORDER BY a.JournalID'));
    // $a = DB::table('v_journal')->where('PartyID',DB::raw( '@total'))->get();

    // $supplier = DB::table('supplier')->where('SupplierID',$request->SupplierID)->get();

    $pdf = PDF::loadView('reports.cashbook_report1pdf', compact('journal', 'pagetitle', 'sql'));
    //return $pdf->download('pdfview.pdf');
    $pdf->setpaper('A4', 'landscape');
    return $pdf->stream();

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();
    return view('reports.cashbook_report1pdf', compact('journal', 'pagetitle', 'sql'));
  }

  public function DaybookReport()
  {
    Session::put('menu', 'CashbookReport');
    $pagetitle = 'Cashbook Report';
    $chartofaccount = DB::table('chartofaccount')
      ->whereIn('Category', ['BANK','CASH','CARD'])
      ->get();

    return view('reports.daybook_report', compact('pagetitle', 'chartofaccount'));
  }

  public function DaybookReport1(request $request)
  {
     ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Day Book', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());

    Session::put('menu', 'CashbookReport');
    $pagetitle = 'Cashbook Report';

    $invoice_detail = DB::table('v_invoice_detail')
      ->whereBetween('date', array($request->StartDate, $request->EndDate))
      ->get();

    $invoice_detail_summary = DB::table('v_invoice_detail')
      ->select(DB::raw('sum(if(ISNULL(Total),0,Total)) as Total'), DB::raw('sum(if(ISNULL(Fare),0,Fare)) as Fare'), DB::raw('sum(if(ISNULL(Service),0,Service)) as Service'))
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->get();

    if ($request->ChartOfAccountID > 0) {

      $sql = DB::table('v_journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->orderBy('Date', 'asc')
        ->get();

      $journal_summary = DB::table('v_journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->get();
    } else {

      $sql = DB::table('v_journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
      ->whereIn('Category', ['BANK','CASH','CARD'])
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->whereIn('Category', ['BANK','CASH','CARD'])
        ->orderBy('Date', 'asc')
        ->get();

      $journal_summary = DB::table('v_journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->whereIn('Category', ['BANK','CASH','CARD'])
        ->get();
    }

    // dd($sql[0]->Balance);
    // $sql= DB::select( DB::raw( 'SET @total := '.$sql[0]->Balance.''));
    // $sql= DB::select( DB::raw( 'select @total as t'));

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    // $a = DB::select(DB::raw('select * from v_journal where PartyID = @total'));
    // $journal = DB::select(DB::raw('SELECT a.JournalID, a.ChartOfAccountID, a.*, IF(ISNULL(a.Dr),0,a.Dr) as Dr, a.Cr,sum(if(ISNULL(b.Dr),0,b.Dr)-if(ISNULL(b.Cr),0,b.Cr))+'.$sql[0]->Balance.' as Balance FROM   v_journal a,  v_journal b WHERE b.JournalID <= a.JournalID and a.PartyID='.$request->PartyID.' and b.PartyID='.$request->PartyID.' and a.ChartOfAccountID=110400 and b.ChartOfAccountID=110400 GROUP BY a.JournalID, a.ChartOfAccountID, a.Dr, a.Cr ORDER BY a.JournalID'));
    // $a = DB::table('v_journal')->where('PartyID',DB::raw( '@total'))->get();

    // $supplier = DB::table('supplier')->where('SupplierID',$request->SupplierID)->get();

    //          $pdf = PDF::loadView ('party_ledger1pdf',compact('journal','pagetitle','sql' ,'party')); 
    // //return $pdf->download('pdfview.pdf');
    //    $pdf->setpaper('A4', 'portiate');
    //       return $pdf->stream();
    // dd(count($invoice).'-'.count($journal));

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();

    if (count($invoice_detail) > count($journal)) {
      $row = count($invoice_detail);
    } else {
      $row = count($journal);
    }

    return view('reports.daybook_report1', compact('journal', 'pagetitle', 'sql', 'invoice_detail', 'row', 'invoice_detail_summary', 'journal_summary'));
  }

  public function DaybookReport1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Day Book', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());

    Session::put('menu', 'CashbookReport');
    $pagetitle = 'Cashbook Report';

    $invoice_detail = DB::table('v_invoice_detail')
      ->whereBetween('date', array($request->StartDate, $request->EndDate))
      ->get();

    $invoice_detail_summary = DB::table('v_invoice_detail')
      ->select(DB::raw('sum(if(ISNULL(Total),0,Total)) as Total'), DB::raw('sum(if(ISNULL(Fare),0,Fare)) as Fare'), DB::raw('sum(if(ISNULL(Service),0,Service)) as Service'))
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->get();

    if ($request->ChartOfAccountID > 0) {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->orderBy('Date', 'asc')
        ->get();

      $journal_summary = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->where('ChartOfAccountID', $request->ChartOfAccountID)
        ->get();
    } else {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->whereIn('ChartOfAccountID', [110101, 110250, 110201, 110101])
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', [110101, 110250, 110201, 110101])
        ->orderBy('Date', 'asc')
        ->get();

      $journal_summary = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', [110101, 110250, 110201, 110101])
        ->get();
    }

    // dd($sql[0]->Balance);
    // $sql= DB::select( DB::raw( 'SET @total := '.$sql[0]->Balance.''));
    // $sql= DB::select( DB::raw( 'select @total as t'));

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    // $a = DB::select(DB::raw('select * from v_journal where PartyID = @total'));
    // $journal = DB::select(DB::raw('SELECT a.JournalID, a.ChartOfAccountID, a.*, IF(ISNULL(a.Dr),0,a.Dr) as Dr, a.Cr,sum(if(ISNULL(b.Dr),0,b.Dr)-if(ISNULL(b.Cr),0,b.Cr))+'.$sql[0]->Balance.' as Balance FROM   v_journal a,  v_journal b WHERE b.JournalID <= a.JournalID and a.PartyID='.$request->PartyID.' and b.PartyID='.$request->PartyID.' and a.ChartOfAccountID=110400 and b.ChartOfAccountID=110400 GROUP BY a.JournalID, a.ChartOfAccountID, a.Dr, a.Cr ORDER BY a.JournalID'));
    // $a = DB::table('v_journal')->where('PartyID',DB::raw( '@total'))->get();

    // $supplier = DB::table('supplier')->where('SupplierID',$request->SupplierID)->get();

    if (count($invoice_detail) > count($journal)) {
      $row = count($invoice_detail);
    } else {
      $row = count($journal);
    }

    $pdf = PDF::loadView('reports.daybook_report1pdf', compact('journal', 'pagetitle', 'sql', 'invoice_detail', 'row', 'invoice_detail_summary', 'journal_summary'));
    // //return $pdf->download('pdfview.pdf');
    $pdf->setpaper('A4', 'landscape');
    return $pdf->stream();
    // dd(count($invoice).'-'.count($journal));

    // $journal = DB::table('v_journal')->where('PartyID',1002)->where('ChartOfAccountID',110400)->get();

    return view('reports.daybook_report1', compact('journal', 'pagetitle', 'sql', 'invoice_detail', 'row', 'invoice_detail_summary', 'journal_summary'));
  }

  public function GeneralLedger()
  {

    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'General Ledger';
    $chartofaccount = DB::table('chartofaccount')
      // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
      ->get();

    return view('reports.general_ledger', compact('pagetitle', 'chartofaccount'));
  }

  public function GeneralLedger1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'General Ledger', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());

    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'General Ledger';

    if ($request->ChartOfAccountID > 0) {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->orderBy('Date', 'asc')
        ->get();

      $journal_summary = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->get();
    } else {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->orderBy('Date', 'asc')
        ->get();

      $journal_summary = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->get();
    }

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    return view('reports.general_ledger1', compact('journal', 'pagetitle', 'sql', 'journal_summary'));
  }

  public function GeneralLedger1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'General Ledger', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());

    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'General Ledger';

    if ($request->ChartOfAccountID > 0) {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->orderBy('Date', 'asc')
        ->get();

      $journal_summary = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->get();
    } else {

      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))

        ->get();

      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->orderBy('Date', 'asc')
        ->get();

      $journal_summary = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->get();
    }

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    $pdf = PDF::loadView('reports.general_ledger1pdf', compact('journal', 'pagetitle', 'sql', 'journal_summary'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return view('reports.general_ledger1pdf', compact('journal', 'pagetitle', 'sql', 'journal_summary'));
  }

  public function TrialBalance()
  {
    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'General Ledger';
    $chartofaccount = DB::table('v_chartofaccount')

      ->get();

    return view('reports.trial_balance', compact('pagetitle', 'chartofaccount'));
  }

  public function TrialBalance1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Trial Balance', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());
    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'Trial Balance';

    if ($request->ChartOfAccountID > 0) {

      $trial = DB::table('v_journal')
        ->select('ChartOfAccountID', 'ChartOfAccountName',  DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)) as Balance'), DB::raw('if(sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))>=0,sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)),0) as Debit'), DB::raw('if(sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))<0,sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)),0) as Credit'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))

        ->where('ChartOfAccountID', 'LIKE', substr($request->ChartOfAccountID, 0, 1) . '%')
        ->groupBy('ChartOfAccountID', 'ChartOfAccountName')
        ->get();

      // dd(substr($request->ChartOfAccountID,0,1));

    } else {
      $trial = DB::table('v_journal')
        ->select('ChartOfAccountID', 'ChartOfAccountName',  DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)) as Balance'), DB::raw('if(sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))>=0,sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)),0) as Debit'), DB::raw('if(sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))<0,sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)),0) as Credit'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->where('ChartOfAccountID',$request->ChartOfAccountID)
        ->groupBy('ChartOfAccountID', 'ChartOfAccountName')
        ->get();
    }

    return view('reports.trial_balance1', compact('trial', 'pagetitle'));
  }

  public function TrialBalance1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Trial Balance', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());
    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'Trial Balance';

    if ($request->ChartOfAccountID > 0) {

      $trial = DB::table('v_journal')
        ->select('ChartOfAccountID', 'ChartOfAccountName',  DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)) as Balance'), DB::raw('if(sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))>=0,sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)),0) as Debit'), DB::raw('if(sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))<0,sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)),0) as Credit'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))

        ->where('ChartOfAccountID', 'LIKE', substr($request->ChartOfAccountID, 0, 1) . '%')
        ->groupBy('ChartOfAccountID', 'ChartOfAccountName')
        ->get();

      // dd(substr($request->ChartOfAccountID,0,1));

    } else {
      $trial = DB::table('v_journal')
        ->select('ChartOfAccountID', 'ChartOfAccountName',  DB::raw('sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)) as Balance'), DB::raw('if(sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))>=0,sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)),0) as Debit'), DB::raw('if(sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr))<0,sum(if(ISNULL(Dr),0,Dr)) - sum(if(ISNULL(Cr),0,Cr)),0) as Credit'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->where('ChartOfAccountID',$request->ChartOfAccountID)
        ->groupBy('ChartOfAccountID', 'ChartOfAccountName')
        ->get();
    }

    $pdf = PDF::loadView('reports.trial_balance1pdf', compact('trial', 'pagetitle'));

    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return view('reports.trial_balance1pdf', compact('trial', 'pagetitle'));
  }

  public function TicketRegister()
  {

    Session::put('menu', 'AirlineSummary');
    $pagetitle = 'Airline Summary';
    $invoice_type = DB::table('invoice_type')->get();
    $item = DB::table('item')->get();
    $saleman = DB::table('user')->where('UserType','Saleman')->get();
    $supplier = DB::table('supplier')->get();

    return view('reports.ticket_register', compact('pagetitle', 'invoice_type', 'supplier', 'item', 'saleman'));
  }

  public function TicketRegister1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Ticket Register', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Ticket Register';
    $where = array();

    if (($request->InvoiceTypeID == 1) || ($request->InvoiceTypeID == 2)) {

      $where = array('InvoiceTypeID' => $request->InvoiceTypeID);
    }

    if ($request->SupplierID > 0) {

      $where = Arr::add($where, 'SupplierID', $request->SupplierID);
    }

    if ($request->ItemID > 0) {

      $where = Arr::add($where, 'ItemID', $request->ItemID);
    }

    if ($request->UserID > 0) {

      $where = Arr::add($where, 'UserID', $request->UserID);
    }

    $invoice_detail = DB::table('v_invoice_detail')
      ->where($where)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->orderBy('InvoiceMasterID')
      ->orderBy('Date')
      ->get();

    $invoice_summary = DB::table('v_invoice_detail1')
      ->select(DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->where($where)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->orderBy('InvoiceMasterID')
      ->orderBy('Date')
      ->get();

    // $pdf = PDF::loadView ('airline_summary1',compact('supplier'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    // return $pdf->stream();

    return View('reports.ticket_register1', compact('invoice_detail', 'invoice_summary', 'pagetitle'));
  }

  public function TicketRegister1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Ticket Register', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $where = array();

    if ($request->InvoiceTypeID > 0) {

      $where = array('InvoiceTypeID' => $request->InvoiceTypeID);
    }

    if ($request->SupplierID > 0) {

      $where = Arr::add($where, 'SupplierID', $request->SupplierID);
    }

    if ($request->ItemID > 0) {

      $where = Arr::add($where, 'ItemID', $request->ItemID);
    }

    if ($request->UserID > 0) {

      $where = Arr::add($where, 'UserID', $request->UserID);
    }

    $invoice_detail = DB::table('v_invoice_detail')
      ->where($where)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->orderBy('InvoiceMasterID')
      ->orderBy('Date')
      ->get();

    $invoice_summary = DB::table('v_invoice_detail')
      ->select(DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Service)-sum(Taxable) as Profit'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->where($where)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->orderBy('InvoiceMasterID')
      ->orderBy('Date')
      ->get();

    $pdf = PDF::loadView('reports.ticket_register1pdf', compact('invoice_detail', 'invoice_summary'));
    //return $pdf->download('pdfview.pdf');
    $pdf->setpaper('A4', 'landscape');
    return $pdf->stream();

    return View('reports.ticket_register1pdf', compact('invoice_detail', 'invoice_summary'));
  }

  public function TrialBalanceActivity()
  {
    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'General Ledger';
    $chartofaccount = DB::table('v_chartofaccount')

      ->get();

    return view('reports.trial_balance_activity', compact('pagetitle', 'chartofaccount'));
  }

  public function TrialBalanceActivity1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Trial with Activity', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());
    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'Trial Balance';

    $chartofaccount = DB::select('SELECT ChartOfAccountID,ChartOfAccountName from chartofaccount where ChartOfAccountID in (select ChartOfAccountID from journal where Date between "' . $request->StartDate . '" and "' . $request->EndDate . '") union SELECT ChartOfAccountID,ChartOfAccountName from chartofaccount where ChartOfAccountID in (select ChartOfAccountID from journal where Date < "' . $request->StartDate . '"   )');

    return view('reports.trial_balance_activity1', compact('chartofaccount', 'pagetitle'));
  }

  public function TrialBalanceActivity1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Trial with Activity', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    // dd($request->all());
    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'Trial Balance';

    $chartofaccount = DB::select('SELECT ChartOfAccountID,ChartOfAccountName from chartofaccount where ChartOfAccountID in (select ChartOfAccountID from journal where Date between "' . $request->StartDate . '" and "' . $request->EndDate . '") union SELECT ChartOfAccountID,ChartOfAccountName from chartofaccount where ChartOfAccountID in (select ChartOfAccountID from journal where Date < "' . $request->StartDate . '"   )');

    $pdf = PDF::loadView('reports.trial_balance_activity1pdf', compact('chartofaccount', 'pagetitle'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return view('reports.trial_balance_activity1pdf', compact('chartofaccount', 'pagetitle'));
  }

  public function InvoiceSummary()
  {

    Session::put('menu', 'AirlineSummary');
    $pagetitle = 'Airline Summary';
    $invoice_type = DB::table('invoice_type')->get();
    $user = DB::table('user')->get();

    return view('reports.invoice_summary', compact('pagetitle', 'invoice_type', 'user'));
  }

  public function InvoiceSummary1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice Summary', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Invoice Summary';

    $where = array();

  


      $where = array();
      if ($request->UserID > 0) 
      {
         $where = Arr::add($where, 'UserID', $request->UserID);


      }


       if ($request->InvoiceTypeID > 0) 
      {
         $where = Arr::add($where, 'InvoiceTypeID', $request->InvoiceTypeID);


      }






    $invoice_summary = DB::table('v_invoice_detail')
      ->select('SalemanName', 'UserID', DB::raw('count(InvoiceDetailID) as Qty'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Service)-sum(Taxable) as Profit'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->where($where)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->groupBy('SalemanName', 'UserID')
      ->orderBy('Date')
      ->get();

    $invoice_total = DB::table('v_invoice_detail')
      ->select(DB::raw('count(InvoiceDetailID) as Qty'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Service)-sum(Taxable) as Profit'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->where($where)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))

      ->orderBy('Date')
      ->get();

    // $pdf = PDF::loadView ('airline_summary1',compact('supplier'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    // return $pdf->stream();

    return View('reports.invoice_summary1', compact('invoice_summary', 'invoice_total', 'pagetitle'));
  }

  public function InvoiceSummary1PDF(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Invoice Summary', 'PDF');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////

    $pagetitle = 'Invoice Summary';

    $where = array();

    if ($request->InvoiceTypeID > 0) {

      $where = array('InvoiceTypeID' => $request->InvoiceTypeID);
    }

    if ($request->UserID > 0) {

      $where = Arr::add($where, 'UserID', $request->UserID);
    }

    $invoice_summary = DB::table('v_invoice_detail')
      ->select('SalemanName', 'UserID', DB::raw('count(InvoiceDetailID) as Qty'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Service)-sum(Taxable) as Profit'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->where($where)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->groupBy('SalemanName', 'UserID')
      ->orderBy('Date')
      ->get();

    $invoice_total = DB::table('v_invoice_detail')
      ->select(DB::raw('count(InvoiceDetailID) as Qty'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Service)-sum(Taxable) as Profit'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->where($where)
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))

      ->orderBy('Date')
      ->get();

    $pdf = PDF::loadView('reports.invoice_summary1pdf', compact('invoice_summary', 'invoice_total', 'pagetitle'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();

    return View('reports.invoice_summary1', compact('invoice_summary', 'invoice_total', 'pagetitle'));
  }

  public  function tmp()
  {

    return view('tmp');
  }

  public  function ProfitAndLoss()
  {

    $pagetitle = 'Proft & Loss';

    return view('reports.profit_loss', compact('pagetitle'));
  }

  public  function ProfitAndLoss1(request $request)
  {

    $pagetitle = 'Proft & Loss';

    $chartofaccountr = DB::select('SELECT CODE,ChartOfAccountID,ChartOfAccountName from chartofaccount where  CODE = "R"  and right(L2,4)=0000 and right(L2,5)!=00000 and  ChartOfAccountID in (select L2 from v_journal )  ');

    $chartofaccounte = DB::select('SELECT CODE,ChartOfAccountID,ChartOfAccountName from chartofaccount where  CODE = "E"  and right(L2,4)=0000 and right(L2,5)!=00000 and  ChartOfAccountID in (select L2 from v_journal )  ');

    //where Date between "'.$request->StartDate.'" and "'.$request->EndDate.'"
    return view('reports.profit_loss11', compact('chartofaccountr', 'chartofaccounte', 'pagetitle'));
  }

  public  function BalanceSheet()
  {

    $pagetitle = 'Proft & Loss';

    return view('reports.balance_sheet', compact('pagetitle'));
  }

  public  function BalanceSheet1(request $request)
  {
    $pagetitle = 'Proft & Loss';

    //profit and loss total will be used in balance sheet
    $activityr = DB::table('v_journal')
      ->select(DB::raw('sum(if(ISNULL(Cr),0,Cr))-sum(if(ISNULL(Dr),0,Dr)) as Balance'))
      ->whereBetween('Date', array(request()->StartDate, request()->EndDate))
      ->where('CODE', 'R')
      ->get();
    $activitye = DB::table('v_journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr))-sum(if(ISNULL(Cr),0,Cr)) as Balance'))
      ->whereBetween('Date', array(request()->StartDate, request()->EndDate))
      ->where('CODE', 'E')
      ->get();
    $profit_loss = $activityr[0]->Balance - $activitye[0]->Balance;
    // end of profit of loss balance

    $chartofaccounta = DB::select('SELECT CODE,ChartOfAccountID,ChartOfAccountName from chartofaccount where  CODE = "A"  and right(L2,4)=0000 and right(L2,5)!=00000 and  ChartOfAccountID in (select L2 from v_journal )  ');

    $chartofaccountl = DB::select('SELECT CODE,ChartOfAccountID,ChartOfAccountName from chartofaccount where  CODE = "L"  and right(L2,4)=0000 and right(L2,5)!=00000 and  ChartOfAccountID in (select L2 from v_journal )  ');
    $chartofaccountc = DB::select('SELECT CODE,ChartOfAccountID,ChartOfAccountName from chartofaccount where  CODE = "C"  and right(L2,4)=0000 and right(L2,5)!=00000 and  ChartOfAccountID in (select L2 from v_journal )  ');
    $chartofaccounts = DB::select('SELECT CODE,ChartOfAccountID,ChartOfAccountName from chartofaccount where  CODE = "S"  and right(L2,4)=0000 and right(L2,5)!=00000 and  ChartOfAccountID in (select L2 from v_journal )  ');

    //where Date between "'.$request->StartDate.'" and "'.$request->EndDate.'"
    return view('reports.balance_sheet11', compact('chartofaccounta', 'chartofaccountl', 'chartofaccountc', 'chartofaccounts', 'pagetitle', 'profit_loss'));
  }

  public function checkUserRole($UserID)
  {

    $role = DB::table('user_role')->where('UserID', $UserID)->get();

    if (count($role) > 0) {

      return redirect('RoleView/' . $UserID)->with('error', '$2 updated Successfully')->with('class', 'success');
    } else {

      return redirect('Role/' . $UserID)->with('error', '$2 updated Successfully')->with('class', 'success');
    }
  }

  public  function Logout()
  {
    Session::flush(); // removes all Session data
    return redirect('/')->with('error', 'Logout Successfully.')->with('class', 'success');
  }

  public  function BalanceSheetDetail($ChartOfAccountID, $StartDate, $EndDate)
  {

    $pagetitle = 'Journal Detail';
    $company = DB::table('company')->get();
    $journal = DB::table('v_journal')
      ->where('ChartOfAccountID', $ChartOfAccountID)
      ->whereBetween('Date', array($StartDate, $EndDate))
      ->orderBy('Date', 'asc')
      ->get();

    return view('reports.balancesheet_detail', compact('company', 'journal', 'pagetitle', 'StartDate', 'EndDate'));
  }

  public  function JournalEntries($ChartOfAccountID, $StartDate, $EndDate)
  {

    $pagetitle = 'Journal Detail';
    $company = DB::table('company')->get();
    $journal = DB::table('v_journal')
      ->where('ChartOfAccountID', $ChartOfAccountID)
      ->whereBetween('Date', array($StartDate, $EndDate))
      ->orderBy('Date', 'asc')
      ->get();

    return view('reports.balancesheet_detail', compact('company', 'journal', 'pagetitle', 'StartDate', 'EndDate'));
  }

  public function ReconcileReport()
  {
    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'General Ledger';
    $chartofaccount = DB::table('chartofaccount')
      ->whereIn('Category', ['BANK', 'CARD', 'CASH'])
      ->orderBy('ChartOfAccountName', 'ASC')
      ->get();
    return view('reports.reconcile_report', compact('pagetitle', 'chartofaccount'));
  }

  public function ReconcileReport1(request $request)
  {
    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    

    ////////////////////////////END SCRIPT ////////////////////////////////////////////////
    // dd($request->all());

    Session::put('menu', 'GeneralLedger');
    $pagetitle = 'General Ledger';

    if ($request->ChartOfAccountID > 0) {
      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))
        ->get();
      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->orderBy('Date', 'asc')
        ->get();
      $journal_summary = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        ->whereIn('ChartOfAccountID', array($request->ChartOfAccountID, $request->ChartOfAccountID1))
        ->get();
    } else {
      $sql = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
        // ->where('SupplierID',$request->SupplierID)
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->where('Date', '<', $request->StartDate)
        // ->whereBetween('date',array($request->StartDate,$request->EndDate))
        ->get();
      $journal = DB::table('v_journal')
        // ->where('SupplierID',$request->SupplierID)
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->orderBy('Date', 'asc')
        ->get();
      $journal_summary = DB::table('journal')
        ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)) as Dr'), DB::raw('sum(if(ISNULL(Cr),0,Cr)) as Cr'))
        ->whereBetween('Date', array($request->StartDate, $request->EndDate))
        // ->whereIn('ChartOfAccountID',[110101,110250,110201,110101])
        ->get();
    }

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    return view('reports.reconcile_report1', compact('journal', 'pagetitle', 'sql', 'journal_summary'));
  }

  public function ReconcileUpdate($status, $id)
  {

    $data = array('BankReconcile' => $status);

    $id = DB::table('journal')->where('JournalID', $id)->update($data);

    return 'Update Done';
  }

  public  function Ajax_ReconcileStatus(request $request)
  {

    $ids =  $request->input('ids');
    $data = array(
      'BankReconcile' => $request->input('status'),
    );

    $id = DB::table('journal')->whereIn('JournalID', $ids)->update($data);

    $status = 'Success';
    $message = 'Update Done';

    return response()->json(['success' => $status, 'message' => $message]);
  }

  // ===============Expense Section function==================
  public  function ExpenseCreate()
  {
    $pagetitle = 'Expense';

    Session::put('menu', 'Expense');
    $chartofaccont = DB::table('chartofaccount')->get();
    $items = DB::table('chartofaccount')->where('Level', '3')->get();
    // $items = DB::table('chartofaccount')->where(DB::raw('right(L3,3)'),'<>',000)->get();

    $item = json_encode($items);
    // dd($item);
    $supplier = DB::table('supplier')->get();
    $tax = DB::table('tax')->get();

    // $tax = DB::table('tax')->get();
    $user = DB::table('user')->get();
    $invoice_type = DB::table('invoice_type')->get();

    $vhno = DB::table('expense_master')
      ->select(DB::raw('LPAD(IFNULL(MAX(right(ExpenseNo,5)),0)+1,5,0) as VHNO '))->whereIn(DB::raw('left(ExpenseNo,3)'), ['EXP'])->get();

    Session::put('VHNO', 'EXP-' . $vhno[0]->VHNO);

    return view('expense.expensecreate', compact('invoice_type', 'chartofaccont', 'tax', 'items', 'vhno', 'supplier', 'pagetitle', 'item', 'user'));
  }

  public function ExpenseSave(Request $request)

  {

    Session::put('menu', 'Expense');
    $pagetitle = 'Invoice';

    $expense_mst = array(
      'ExpenseNo' => $request->ExpenseNo,
      'Date' => $request->Date,
      'ChartOfAccountID' => $request->ChartOfAccountID_From,
      'SupplierID' => $request->SupplierID,
      'ReferenceNo' => $request->ReferenceNo,
      'Tax' => $request->grandtotaltax,
      'GrantTotal' => $request->Grandtotal,
      'SubTotal' => $request->SubTotal,

      'Paid' => $request->amountPaid,
    );
    // dd($challan_mst);
    // $id= DB::table('')->insertGetId($data);

    $ExpenseMasterID = DB::table('expense_master')->insertGetId($expense_mst);
    // dd($ExpenseMasterID);

    // JOURNAL ENTRY 
    //bank debit
    $bank_cash = array(
      'VHNO' => $request->ExpenseNo,
      'ChartOfAccountID' => $request->ChartOfAccountID_From,   // Cash / bank / credit card
      'SupplierID' => $request->input('SupplierID'),
      'ExpenseMasterID' => $ExpenseMasterID,

      'Date' => $request->input('Date'),

      'Cr' => $request->Grandtotal,
      'Narration' => $request->ReferenceNo,
      'Trace' => 614
    );
    $journal_entry = DB::table('journal')->insertGetId($bank_cash);

    //  start for item array from invoice
    for ($i = 0; $i < count($request->ItemID0); $i++) {
      $expense_detail = array(
        'ExpenseMasterID' =>  $ExpenseMasterID,
        'ChartOfAccountID' => $request->ChartOfAccountID[$i],
        'Notes' => $request->Description[$i],
        'TaxPer' => $request->Tax[$i],
        'Tax' => $request->TaxVal[$i],
        'Amount' => $request->Amount[$i],
        'AmountAfterTax' => $request->AmountAfterTax[$i],

      );

      $id = DB::table('expense_detail')->insertGetId($expense_detail);

      if ($request->Tax[$i] > 0) {

        // A/P debit
        $ar_payment = array(
          'VHNO' => $request->ExpenseNo,
          'ChartOfAccountID' => $request->ChartOfAccountID[$i],  // chart of account direct debit
          'SupplierID' => $request->input('SupplierID'),
          'ExpenseMasterID' => $request->ExpenseMasterID,
          'Date' => $request->input('Date'),
          'Dr' => $request->Amount[$i],
          'Narration' => $request->Description[$i],
          'Trace' => 615
        );

        $journal_entry1 = DB::table('journal')->insertGetId($ar_payment);

        //tax grandtotaltax

        // Tax Payable debit
        $ar_payment = array(
          'VHNO' => $request->ExpenseNo,
          'ChartOfAccountID' => 110606,  // VAT 
          'SupplierID' => $request->input('SupplierID'),
          'ExpenseMasterID' => $request->ExpenseMasterID,
          'Date' => $request->input('Date'),
          'Dr' => $request->TaxVal[$i],
          'Narration' => $request->Description[$i],
          'Trace' => 617
        );

        $journal_entry11 = DB::table('journal')->insertGetId($ar_payment);
      } else {

        // debit entry
        $ar_payment = array(
          'VHNO' => $request->ExpenseNo,
          'ChartOfAccountID' => $request->ChartOfAccountID[$i],
          'SupplierID' => $request->input('SupplierID'),
          'ExpenseMasterID' => $ExpenseMasterID,
          'Date' => $request->input('Date'),
          'Dr' => $request->AmountAfterTax[$i],
          'Narration' => $request->Description[$i],
          'Trace' => 615
        );

        $journal_entry1 = DB::table('journal')->insertGetId($ar_payment);
      }
    }

    // end payment received

    // END OF JOURNAL ENTRY

    return view('expense.expense', compact('pagetitle'));
  }

  public  function Expense()
  {
    Session::put('menu', 'Expense');
    $pagetitle = 'Invoice';

    return view('expense.expense', compact('pagetitle'));
  }

  public function ajax_Expense(Request $request)

  {
    Session::put('menu', 'Expense');
    $pagetitle = 'Expense';
    if ($request->ajax()) {
 
       $query = DB::table('v_expense_detail');

      // Apply filters if they are present in the request
      // if ($request->has('item_name') && !empty($request->item_name)) {
      //     $query->where('ItemName', 'like', '%' . $request->item_name . '%');
      // }

      if ($request->has('ReferenceNo') && !empty($request->ReferenceNo)) {
        $query->where('ReferenceNo', 'like', '%' . $request->ReferenceNo . '%');
      }
      if ($request->has('ChartOfAccountID') && !empty($request->ChartOfAccountID)) {
        $query->where('ChartOfAccountID', 'like', '%' . $request->ChartOfAccountID . '%');
      }

      if ($request->has('startdate') && !empty($request->startdate)) {
        $query->whereDate('Date', '>=', $request->startdate);
      }
      if ($request->has('enddate') && !empty($request->enddate)) {
        $query->whereDate('Date', '<=', $request->enddate);
      }

      $data = $query->orderBy('Date','desc')->get();

        

      return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          // if you want to use direct link instead of dropdown use this line below
          // <a href="javascript:void(0)"  onclick="edit_data('.$row->customer_id.')" >Edit</a> | <a href="javascript:void(0)"  onclick="del_data('.$row->customer_id.')"  >Delete</a>

          $btn = '

            <div class="d-flex align-items-center col-actions">

            
            <a href="' . URL('/ExpenseViewPDF/' . $row->ExpenseMasterID) . '"><i class="font-size-18 me-1 mdi mdi-file-pdf-outline align-middle me-1 text-secondary"></i></a>
            <a href="' . URL('/ExpenseEdit/' . $row->ExpenseMasterID) . '"><i class="font-size-18 bx bx-pencil align-middle me-1 text-secondary"></i></a>

            <a href="javascript:void(0)" onclick="delete_invoice(' . $row->ExpenseMasterID . ')" ><i class="font-size-18 bx bx-trash align-middle me-1 text-secondary"></i></a>

            </div>
            ';

          //class="edit btn btn-primary btn-sm"
          // <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    return view('expense.expense', 'pagetitle');
  }

  public function  ExpenseEdit($id)
  {

    $pagetitle = 'Expense';

    Session::put('menu', 'Expense');

    $chartofaccount = DB::table('chartofaccount')->get();
    // $chartofaccount = DB::table('chartofaccount')->where(DB::raw('right(L3,3)'),'<>',000)->get();

    $supplier = DB::table('supplier')->get();
    $tax = DB::table('tax')->get();

    // $tax = DB::table('tax')->get();
    $user = DB::table('user')->get();

    $items = DB::table('chartofaccount')->where('Level', '3')->get();

    $expense_master = DB::table('expense_master')->where('ExpenseMasterID', $id)->get();

    Session::put('VHNO', $expense_master[0]->ExpenseNo);

    $expense_detail = DB::table('expense_detail')->where('ExpenseMasterID', $id)->get();

    return view('expense.expense_edit', compact('tax', 'supplier', 'pagetitle', 'expense_master', 'chartofaccount', 'expense_detail', 'items'));
  }

  public function ExpenseUpdate(request $request)
  {

    Session::put('menu', 'Expense');
    $pagetitle = 'Invoice';

    $expense_mst = array(
      'ExpenseNo' => $request->ExpenseNo,
      'Date' => $request->Date,
      'ChartOfAccountID' => $request->ChartOfAccountID_From,
      'SupplierID' => $request->SupplierID,
      'ReferenceNo' => $request->ReferenceNo,
      'Tax' => $request->grandtotaltax,
      'SubTotal' => $request->SubTotal,
      'GrantTotal' => $request->Grandtotal,
      'Paid' => $request->amountPaid,
    );
    // dd($challan_mst);
    // $id= DB::table('')->insertGetId($data);

    $ExpenseMasterID = DB::table('expense_master')->where('ExpenseMasterID', $request->ExpenseMasterID)->update($expense_mst);
    // dd($InvoiceMasterID);

    $idd = DB::table('expense_detail')->where('ExpenseMasterID', $request->ExpenseMasterID)->delete();

    $id2 = DB::table('journal')->where('ExpenseMasterID', $request->ExpenseMasterID)->delete();

    // JOURNAL ENTRY 
    //bank debit
    $bank_cash = array(
      'VHNO' => $request->ExpenseNo,
      'ChartOfAccountID' => $request->ChartOfAccountID_From,   // Cash / bank / credit card
      'SupplierID' => $request->input('SupplierID'),
      'ExpenseMasterID' => $request->ExpenseMasterID,

      'Date' => $request->input('Date'),

      'Cr' => $request->Grandtotal,
      'Narration' => $request->ReferenceNo,
      'Trace' => 614
    );
    $journal_entry = DB::table('journal')->insertGetId($bank_cash);

    //  start for item array from invoice
    for ($i = 0; $i < count($request->ItemID0); $i++) {
      $expense_detail = array(
        'ExpenseMasterID' =>  $request->ExpenseMasterID,
        'ChartOfAccountID' => $request->ChartOfAccountID[$i],
        'Notes' => $request->Description[$i],
        'TaxPer' => $request->Tax[$i],
        'Tax' => $request->TaxVal[$i],
        'Amount' => $request->Amount[$i],
        'AmountAfterTax' => $request->AmountAfterTax[$i],

      );

      $id = DB::table('expense_detail')->insertGetId($expense_detail);

      if ($request->Tax[$i] > 0) {

        // A/P debit
        $ar_payment = array(
          'VHNO' => $request->ExpenseNo,
          'ChartOfAccountID' => $request->ChartOfAccountID[$i],  // chart of account direct debit
          'SupplierID' => $request->input('SupplierID'),
          'ExpenseMasterID' => $request->ExpenseMasterID,
          'Date' => $request->input('Date'),
          'Dr' => $request->Amount[$i],
          'Narration' => $request->Description[$i],
          'Trace' => 615
        );

        $journal_entry1 = DB::table('journal')->insertGetId($ar_payment);

        //tax grandtotaltax

        // Tax Payable debit
        $ar_payment = array(
          'VHNO' => $request->ExpenseNo,
          'ChartOfAccountID' => 110606,  // VAT 
          'SupplierID' => $request->input('SupplierID'),
          'ExpenseMasterID' => $request->ExpenseMasterID,
          'Date' => $request->input('Date'),
          'Dr' => $request->TaxVal[$i],
          'Narration' => $request->Description[$i],
          'Trace' => 617
        );

        $journal_entry11 = DB::table('journal')->insertGetId($ar_payment);
      } else {

        // debit entry
        $ar_payment = array(
          'VHNO' => $request->ExpenseNo,
          'ChartOfAccountID' => $request->ChartOfAccountID[$i],
          'SupplierID' => $request->input('SupplierID'),
          'ExpenseMasterID' => $request->ExpenseMasterID,
          'Date' => $request->input('Date'),
          'Dr' => $request->AmountAfterTax[$i],
          'Narration' => $request->Description[$i],
          'Trace' => 615
        );

        $journal_entry1 = DB::table('journal')->insertGetId($ar_payment);
      }
    }

    // end payment received

    // END OF JOURNAL ENTRY

    return view('expense.expense', compact('pagetitle'));
  }

  public function ExpenseView($id)
  {

    $pagetitle = 'Expense View ';
    $company = DB::table('company')->get();
    $expense_master = DB::table('v_expense')->where('ExpenseMasterID', $id)->get();
    $expense_detail = DB::table('v_expense_detail')->where('ExpenseMasterID', $id)->get();
    $journal = DB::table('journal')->where('ExpenseMasterID', $id)->get();

    $pdf = PDF::loadView('expense.expense_view_pdf', compact('expense_master', 'expense_detail', 'pagetitle', 'company'));
    $pdf->set_option('isPhpEnabled', true);
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();
  }

  public function ExpenseViewPDF($id)
  {

    $pagetitle = 'Expense View ';
    $company = DB::table('company')->get();
    $expense_master = DB::table('v_expense')->where('ExpenseMasterID', $id)->get();
    $expense_detail = DB::table('v_expense_detail')->where('ExpenseMasterID', $id)->get();
    $journal = DB::table('journal')->where('ExpenseMasterID', $id)->get();

    $pdf = PDF::loadView('expense.expense_view_pdf', compact('expense_master', 'expense_detail', 'pagetitle', 'company'));
    $pdf->set_option('isPhpEnabled', true);
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    return $pdf->stream();
  }

  public function ExpenseDelete($id)
  {

    $id = DB::table('expense_master')->where('ExpenseMasterID', $id)->delete();
    $id2 = DB::table('expense_detail')->where('ExpenseMasterID', $id)->delete();
    $id3 = DB::table('journal')->where('ExpenseMasterID', $id)->delete();

    return redirect('Expense')->with('error', 'Deleted Successfully')->with('class', 'success');
  }

  public function Salesman()
  {

    $pagetitle = 'Salesman';

    $salesman = DB::table('saleman')->get();

    return view('salesman.salesman', compact('salesman', 'pagetitle'));
  }

  public function SalesmanSave(request $request)
  {

    $data = array(

      'SalemanName' => $request->SalemanName,
      'Mobile' => $request->Mobile,
      'Address' => $request->Address,

    );

    $id = DB::table('saleman')->insertGetId($data);

    return redirect('Salesman')->with('error', 'DATA SAVED')->with('class', 'success');
  }

  public function SalesmanEdit($id)
  {

    $pagetitle = 'Salesman';

    $salesman = DB::table('saleman')->where('SalemanID', $id)->get();

    return view('salesman.salesman_edit', compact('salesman', 'pagetitle'));
  }

  public function SalesmanUpdate(request $request)
  {

    $data = array(

      'SalemanName' => $request->SalemanName,
      'Mobile' => $request->Mobile,
      'Address' => $request->Address,

    );

    $id = DB::table('saleman')->where('SalemanID', '=', $request->SalemanID)->update($data);

    return redirect('Salesman')->with('error', 'DATA UPDATED')->with('class', 'success');
  }

  public  function SalesmanDelete($id)
  {

    $id = DB::table('saleman')->where('SalemanID', $id)->delete();

    return redirect('Salesman')->with('error', 'Deleted Successfully.')->with('class', 'success');
  }

  public function JVSave(request $request)
  {

    // dd($request->all());
    $voucher_mst = array(
      'VoucherCodeID' => $request->input('VoucherType'),
      'Voucher' => $request->input('Voucher'),
      'Narration' => $request->input('Narration_mst'),
      'Date' => $request->input('VHDate'),

    );

    // dd($invoice_mst);

    // $id= DB::table('')->insertGetId($data);

    $id = DB::table('voucher_master')->insertGetId($voucher_mst);

    for ($i = 0; $i < count($request->ChOfAcc); $i++) {

      $voucher_det_dr = array(
        'VoucherMstID' => $id,
        'Voucher' => $request->input('Voucher'),
        'Date' =>  $request->input('VHDate'),
        'ChOfAcc' => $request->ChOfAcc[$i],
        'SupplierID' => $request->SupplierID[$i],
        'PartyID' => $request->PartyID[$i],
        'Narration' => $request->Narration[$i],
        'InvoiceNo' => $request->Invoice[$i],
        'RefNo' => $request->RefNo[$i],
        'Debit' => $request->Debit[$i],
        'Credit' => $request->Credit[$i],

      );

      $id2 = DB::table('voucher_detail')->insert($voucher_det_dr);


    // log input
    $logdata = array(
      'UserName' => Session::get('FullName'), 
      'Amount' => ($request->Debit[$i]) ? $request->Debit[$i] : $request->Credit[$i],
      'Date' => date('Y-m-d H:i:s'), 
      'Section' => 'JV Created', 
      'VHNO' => $request->input('Voucher'), 
      'Narration' => $request->input('Narration_mst'), 
      'Trace' => 3301,
      'UserID' => Session::get('UserID'),
    );

    $log= DB::table('log')->insertGetId($logdata);

    // log input 


    }
    // end for each

    return redirect('Voucher')->with('error', 'Record Saved')->with('class', 'success');
  }

  public function PartySalesLedger3($PartyID)
  {

     $pagetitle = 'Party Sale Ledger';

    $sql = DB::table('journal')
      ->select(DB::raw('sum(if(ISNULL(Dr),0,Dr)-if(ISNULL(Cr),0,Cr)) as Balance'))
      ->where('PartyID', $PartyID)
      ->where('ChartOfAccountID', 110400)
      // ->where('Date', '<', $StartDate)
      // ->whereBetween('date', array($StartDate, $EndDate))
      ->get();

    $journal = DB::table('v_journal')
      ->where('PartyID', $PartyID)
      // ->whereBetween('Date', array($StartDate, $EndDate))
      ->where('ChartOfAccountID', 110400)
      ->orderBy('Date')
      ->get();

    $company = DB::table('company')->get();

    $party = DB::table('party')->where('PartyID', $PartyID)->get();

    $sql[0]->Balance = ($sql[0]->Balance == null) ? '0' :  $sql[0]->Balance;

    return View('reports.party_sales_ledger2pdf', compact('journal', 'pagetitle', 'sql', 'party', 'company'));

    //   $pdf->setpaper('A4', 'portiate');
    // return $pdf->stream();

  }

  function ExpenseReport()
  {
    $pagetitle = 'Expense';
    return view('expense.expense_report', compact('pagetitle'));
  }

  public function ExpenseReport1(request $request)
  {

    $pagetitle = 'Expense Report';

    $company = DB::table('company')->get();

    $expense_detail = DB::table('v_expense_detail')

      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->orderBy('Date')
      ->get();

    return View('expense.expense_report1', compact('expense_detail', 'pagetitle', 'company'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
  }

  // dummy test function
  public function upload1(Request $request)
  {
    $request->validate([
      'file' => 'required|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($request->file('file')->isValid()) {
      $path = $request->file('file')->store('uploads');
      return response()->json(['path' => $path], 200);
    }

    return response()->json(['error' => 'Invalid file upload.'], 400);
  }

  function Attachment($vhno = null)
  {

    if ($vhno != '') {
      Session::put('vhno', $vhno);
    }

    $attachment = DB::table('attachment')->where('InvoiceNo', Session::get('vhno'))->get();

    return view('attachment', compact('attachment'));
  }

  function AttachmentSave(Request $request)
  {

    if ($request->hasfile('filenames')) {
      foreach ($request->file('filenames') as $file) {
        $name = rand(0, 999999) . time() . '.' . $file->extension();
        $file->move(public_path() . '/documents/', $name);
        $data[] = $name;

        $fileData = array(
          'InvoiceNo' => $request->InvoiceNo,
          'FileName' =>  $name

        );
        // dd($fileData);
        $fileid = DB::table('attachment')->insertGetId($fileData);
      }
    }

    return back()->with('success', 'Data Your files has been successfully added');
  }

  public function AttachmentRead()
  {
    $directory = 'documents';
    $files_info = [];

    $file_name = Session::get('VHNO');;

    $image = DB::table('attachment')->where('InvoiceNo', $file_name)->get();

    // Read files
    foreach ($image as $file) {

      //  $filename = $file->getFilename();
      //  $size = $file->getSize(); // Bytes
      //  $sizeinMB = round($size / (1000 * 1024), 2);// MB

      //  if($sizeinMB <= 2){ // Check file size is <= 2 MB
      $files_info[] = array(
        "name" => $file->FileName,
        "size" => 12,
        "path" => url($directory . '/' . $file->FileName)
      );
      //  }
      //   }
    }
    return response()->json($files_info);
  }

  public function AttachmentDelete($id, $filename)
  {
    $id =  $id;
    $filename =  $filename;
    DB::table('attachment')->where('AttachmentID', $id)->delete();
    $path = public_path() . '/documents/' . $filename;
    if (file_exists($path)) {
      unlink($path);
    }
    return back()->with('error', 'File Deleted')->with('class', 'success');
  }

  public function SalemanTicketRegister()
  {

    $pagetitle = 'Ticket Register';
    return view('reports.saleman_ticketregister', compact('pagetitle'));
  }

   public function SalemanTicketRegister1(request $request)
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Ticket Register', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////


    try {
         
         

         


    $pagetitle = 'Ticket Register';

    $invoice_detail = DB::table('v_invoice_detail')
      ->select('SalemanName', DB::raw('count(DISTINCT InvoiceMasterID) as TotalInvoices'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      ->groupBy('SalemanName')
      // ->orderBy('Date')
      ->get();

    $invoice_summary = DB::table('v_invoice_detail')
      ->select(DB::raw('count(*) as TotalInvoices'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
      // ->orderBy('Date')
      ->get();

 
      $startDate = Carbon::parse($request->StartDate);
    $endDate = Carbon::parse($request->EndDate);

 
        // Generate all dates between start and end
    $dates = [];
    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
        $dates[] = $date->format('Y-m-d');
    }

    
  // Fetch invoice data grouped by UserID and Date
    $rawData = DB::table('v_invoice_detail')
        ->select(
            'UserID',
            DB::raw('DATE(Date) as date'),
            DB::raw('COUNT(*) as invoice_count'),
            DB::raw('SUM(Service) as service_total')
        )
        ->whereBetween('Date', [$startDate, $endDate])
        ->groupBy('UserID', DB::raw('DATE(Date)'))
        ->get();

   // Get list of users
    $users = DB::table('user')->select('UserID', 'FullName')->where('Active','Yes')->whereNotIn('UserID', [7, 17, 12])->get(); 
    //7, 17, 12

           // Organize raw data into associative array: $data[user_id][date]
    $data = [];
    foreach ($rawData as $row) {
        $data[$row->UserID][$row->date] = [
            'count' => $row->invoice_count,
            'total' => $row->service_total,
        ];
    }



      if(count($invoice_detail)<=0)
      {
        return back()->with('error', 'No invocies found for these dates')->with('class', 'danger');
      }

    // $pdf = PDF::loadView ('airline_summary1',compact('supplier'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    // return $pdf->stream();

    return View('reports.saleman_ticketregister1', compact('invoice_detail', 'invoice_summary', 'pagetitle','users','dates', 'data'));

        
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->with('class', 'danger');
        }



  }
  
  
  public function SalemanTicketShowAll()
  {

    ///////////////////////USER RIGHT & CONTROL ///////////////////////////////////////////    
    $allow = check_role(Session::get('UserID'), 'Ticket Register', 'View');
    if ($allow[0]->Allow == 'N') {
      return redirect()->back()->with('error', 'You access is limited')->with('class', 'danger');
    }
    ////////////////////////////END SCRIPT ////////////////////////////////////////////////


    try {
         

    $pagetitle = 'Ticket Register';

    $invoice_detail = DB::table('v_invoice_detail')
      ->select('SalemanName', DB::raw('count(DISTINCT InvoiceMasterID) as TotalInvoices'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->whereBetween('Date', array(date('Y-m-d'),date('Y-m-d')))
      ->groupBy('SalemanName')
      
      ->get();

    $invoice_summary = DB::table('v_invoice_detail')
      ->select(DB::raw('count(*) as TotalInvoices'), DB::raw('sum(Fare) as Fare'), DB::raw('sum(Service) as Service'), DB::raw('sum(Total) as Total'), DB::raw('sum(Taxable) as Taxable'), DB::raw('sum(Discount) as Discount'))
      ->whereBetween('Date', array(date('Y-m-d'),date('Y-m-d')))
      // ->orderBy('Date')
      ->get();

 
      $startDate = Carbon::parse(date('Y-m-d'));
    $endDate = Carbon::parse(date('Y-m-d'));

 
        // Generate all dates between start and end
    $dates = [];
    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
        $dates[] = $date->format('Y-m-d');
    }

    
  // Fetch invoice data grouped by UserID and Date
    $rawData = DB::table('v_invoice_detail')
        ->select(
            'UserID',
            DB::raw('DATE(Date) as date'),
            DB::raw('COUNT(*) as invoice_count'),
            DB::raw('SUM(Service) as service_total')
        )
      ->whereBetween('Date', array(date('Y-m-d'),date('Y-m-d')))
        ->groupBy('UserID', DB::raw('DATE(Date)'))
        ->get();

   // Get list of users
    $users = DB::table('user')->select('UserID', 'FullName')->where('Active','Yes')->get();

           // Organize raw data into associative array: $data[user_id][date]
    $data = [];
    foreach ($rawData as $row) {
        $data[$row->UserID][$row->date] = [
            'count' => $row->invoice_count,
            'total' => $row->service_total,
        ];
    }



      if(count($invoice_detail)<=0)
      {
        return back()->with('error', 'No invocies found for these dates')->with('class', 'danger');
      }

    // $pdf = PDF::loadView ('airline_summary1',compact('supplier'));
    //return $pdf->download('pdfview.pdf');
    // $pdf->setpaper('A4', 'portiate');
    // return $pdf->stream();

    return View('reports.saleman_ticketregister_showall', compact('invoice_detail', 'invoice_summary', 'pagetitle','users','dates', 'data'));

        
        } catch (\Exception $e) {
            DB::rollBack();

            dd($e->getMessage());
            return back()->with('error', $e->getMessage())->with('class', 'danger');
        }



  }
  public  function ajax_party_list()
  {
    $party = DB::table('party')->get();

    return response()->json(['party' => $party]);
  }




public function query()
{


  $voucher_detail = DB::table('voucher_detail')->whereNotNull('InvoiceNo')->get();

  return view ('query',compact('voucher_detail'));

  // end of controller
}



  public  function ItemwiseSale()

    {


      $pagetitle='Itemwise Sale';
      $item = DB::table('item')->get();


                
    return view ('reports.itemwise_sale',compact('pagetitle','item'));
    }


  public  function ItemwiseSale2(request $request)

    {

       $pagetitle='Itemwise Sale';

         $today_sale = DB::table('v_invoice_detail22')
    ->select(
        'ItemName','ItemID',
        DB::raw('count(*) as Total'),
        DB::raw('sum(Total) as Invoice'),
        DB::raw('sum(Service) as Profit'),
        DB::raw('sum(Service)/sum(Total) as Percentage')
    )
      ->whereBetween('Date', array($request->StartDate, $request->EndDate))
           ->orderBy('Total', 'desc') // ✅ order by alias

     ->groupBy('ItemName','ItemID')
    ->get();

 

                
    return view ('reports.itemwise_sale2',compact('pagetitle','today_sale'));
    }
    
    
    
    public  function ItemWiseSale2Showall()

    {

       $pagetitle='Itemwise Sale';

         $today_sale = DB::table('v_invoice_detail22')
    ->select(
        'ItemName','ItemID',
        DB::raw('count(*) as Total'),
        DB::raw('sum(Total) as Invoice'),
        DB::raw('sum(Service) as Profit'),
        DB::raw('sum(Service)/sum(Total) as Percentage')
    )
      ->whereBetween('Date', array(date('Y-m-1'), date('Y-m-d')))
     ->orderBy('Total', 'desc') // ✅ order by alias
     ->groupBy('ItemName','ItemID')
    ->get();

 

                
    return view ('reports.itemwise_sale2_showall',compact('pagetitle','today_sale'));
    }



public function InvoiceDetailList($itemid, $startdate,$enddate)
{



  $pagetitle='Item Detail Invoice Detail';

  
  
  $paymentmode = DB::table('v_invoice_detail')
    ->whereBetween('Date', [$startdate, $enddate])
    ->where('ItemID', $itemid)
    ->distinct()
    ->get('PaymentMode');

 
 

  return view ('reports.invoice_detail_item_wise',compact('pagetitle','paymentmode'));




}


function Log()
{



// $expense_master = DB::table('expense_master')->get();

// foreach($expense_master as $value)
// {
//   $sql = "update journal set ExpenseMasterID =".$value->ExpenseMasterID." where VHNO = ".$value->ExpenseNo." ";
//   echo $sql.";<br>";
// }




  $pagetitle='Log';
  $user = DB::table('user')->get();

  return view ('log.log',compact('pagetitle','user'));

}

function Log1(request $request)
{
  $pagetitle='Log';



  $log = DB::table('log')
 ->whereBetween(DB::raw('DATE_FORMAT(Date,"%Y-%m-%d")'), [$request->StartDate,$request->EndDate])
 ->when($request->has('UserID') && $request->UserID != null, function ($query) use ($request) {
                        $query->where('UserID', $request->UserID);
                    })
  ->get();
  
  return view('log.log1',compact('pagetitle','log'));

}
  
// public function getParties(Request $request)
// {
//     $search = $request->get('search');

//     if ($search) {
//         // Normalize input: remove common formatting and prefixes
//         $search = preg_replace('/[^0-9]/', '', $search); // keep only numbers
//         $search = preg_replace('/^(00971|971|0)/', '', $search); // remove UAE prefixes and leading zero

//         $query = DB::table('party');

//         $query->where(function ($q) use ($search) {
//             $q->whereRaw("
//                 REPLACE(
//                     REPLACE(
//                         REPLACE(
//                             REPLACE(TRIM(Phone), ' ', ''),
//                         '-', ''),
//                     '+', ''),
//                 '00971', '') LIKE ?", ["%$search%"])
//               ->orWhere('PartyName', 'like', "%$search%");
//         });

//         $parties = $query->select('PartyID', 'PartyName', 'Phone')
//                          ->limit(20)
//                          ->get();

//         return response()->json($parties);
//     }

//     return response()->json([]);
// }


public function getParties(Request $request)
{
    $search = $request->get('search');

    if ($search) {
        $query = DB::table('party');

        // Check if search is numeric (i.e., user is likely searching for a phone number)
        if (is_numeric(preg_replace('/\s+/', '', $search))) {
            // Normalize phone number
            $searchPhone = preg_replace('/[^0-9]/', '', $search);
            $searchPhone = preg_replace('/^(00971|971|0)/', '', $searchPhone);

            $query->whereRaw("
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(TRIM(Phone), ' ', ''),
                        '-', ''),
                    '+', ''),
                '00971', '') LIKE ?", ["%$searchPhone%"]);
        } else {
            // Search by party name
            $query->where('PartyName', 'like', "%$search%");
        }

        $parties = $query->select('PartyID', 'PartyName', 'Phone')
                         ->limit(20)
                         ->get();

        return response()->json($parties);
    }

    return response()->json([]);
}



}
