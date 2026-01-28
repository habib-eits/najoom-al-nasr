<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Session;
class AjaxController extends Controller
{
    public function ajaxGetAgents($id = null)
    {
        try {



 



            if ($id != null && session::get('Type')!='Admin' )  {
                $agents = DB::table('user')->where('UserType', '!=', 'Admin')
                    ->where('branch_id', $id)->where('UserID',session::get('UserID'))
                    ->get();
            } else {
                $agents = DB::table('user')->where('UserType', '!=', 'Admin')
                    ->get();
            }






            return response()->json(['data' => $agents]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }
    public function ajaxGetServices($id = null)
    {
        try {
            if ($id != null) {
                $services = Service::where('branch_id', $id)
                    ->get();
            } else {
                $services = Service::all();
            }
            return response()->json(['data' => $services]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }
    public function ajaxGetSubservices($id = null)
    {
        try {
            if ($id != null) {
                $subServices = SubService::where('service_id', $id)
                    ->get();
            } else {
                $subServices = SubService::all();
            }
            return response()->json(['data' => $subServices]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }


   public function ajaxGetLeads()
    {
        try {
             
        $leads = DB::table('leads')->whereNull('agent_id')->get();


        if ($leads->isEmpty()) {
            return response()->json(['status' => 'empty' , 'total' =>0]);
        } else {
            return response()->json(['status' => 'not empty','total' => count($leads),'data' =>$leads]);
        }      
            


        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }


   public function ajaxGetBookingPayment()
    {
        try {
             
            $booking = DB::table('v_booking')->where('end','<',Carbon::now())->where('status','Pending')->get();



        if ($booking->isEmpty()) {
            return response()->json(['status' => 'empty' , 'total' =>0]);
        } else {
            return response()->json(['status' => 'not empty','total' => count($booking),'data' =>$booking]);
        }      
            


        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }





}
