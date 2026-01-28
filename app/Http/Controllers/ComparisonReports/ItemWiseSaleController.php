<?php

namespace App\Http\Controllers\ComparisonReports;

use Carbon\Carbon;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ItemWiseSaleController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([

        ]);


        $fromDate = $request->fromDate ?? date('Y-m-01');
        $toDate   = $request->toDate   ?? date('Y-m-t');
        $comparedType = $request->comparedType ?? 'period';
       
        $comparedCount = $request->comparedCount ?? 1;
        $dateRangeSelector = $request->dateRangeSelector ?? 'This Month';

        //Generate comparison dates
        if ($comparedType === 'year') {
            $dates = $this->comparedYearDates($fromDate, $toDate, $comparedCount,$dateRangeSelector);
        } else {
            $dates = $this->comparedMonthDates($fromDate, $toDate, $comparedCount);
        }

        $items = Item::all();
        $finalData = [];
        foreach($items as $item)
        {
            $itemWiseSales = [
                'name' => $item->ItemName,
                'sales' => []
            ];
            foreach($dates as $date)
            {


                $itemRecords = DB::table('invoice_master')
                ->leftJoin('invoice_detail','invoice_detail.InvoiceMasterID','=','invoice_master.InvoiceMasterID')
                ->select(
                    'invoice_master.InvoiceMasterID',
                    'invoice_master.date',
                    'invoice_detail.ItemID',
                    'invoice_detail.Fare',
                    'invoice_detail.Taxable',
                    'invoice_detail.Service',
                    'invoice_detail.Total',
                )
                ->whereBetween('invoice_master.Date',[ $date['fromDate'], $date['toDate']])
                ->where('invoice_detail.ItemID',$item->ItemID)
                ->get();

                    
                
                $itemWiseSales['sales'][] = [
                    'no_of_sales' => $itemRecords->count(),
                    'total_invoice_amount' => $itemRecords->sum('Total'),
                    'profit' =>  $itemRecords->sum('Service'),
                ];
                
                
            }

            $finalData[] = $itemWiseSales;
        }



        $totals = [];

        foreach ($dates as $i => $date) {
            $totals[$i] = [
                'no_of_sales' => 0,
                'total_invoice_amount' => 0,
                'profit' => 0,
            ];

            foreach ($finalData as $row) {
                $totals[$i]['no_of_sales'] += $row['sales'][$i]['no_of_sales'];
                $totals[$i]['total_invoice_amount'] += $row['sales'][$i]['total_invoice_amount'];
                $totals[$i]['profit'] += $row['sales'][$i]['profit'];
            }
        }


      
        return view('comparison_reports.item_wise_sales.show', compact(
            'fromDate',
            'toDate',
            'comparedType',
            'comparedCount',
            'dates',
            'finalData',
            'dateRangeSelector',
            'totals',
        ));

    }

    


    public function comparedMonthDates($fromDate, $toDate, $comparedCount)
    {
        $dates = [];

        for ($i = 0; $i < $comparedCount; $i++) {

            $from = Carbon::parse($fromDate)->subMonthsNoOverflow($i);
            $to   = Carbon::parse($toDate)->subMonthsNoOverflow($i);

            $dates[] = [
                'label'    => $from->format('M Y'), // Month Name + Year
                'fromDate' => $from->format('Y-m-d'),
                'toDate'   => $to->format('Y-m-d'),
            ];
        }

        return $dates;
    }

    public function comparedYearDates($fromDate, $toDate, $comparedCount,$dateRangeSelector)
    {
        $dates = [];

        for ($i = 0; $i < $comparedCount; $i++) {

            $from = Carbon::parse($fromDate)->subYearsNoOverflow($i);
            $to   = Carbon::parse($toDate)->subYearsNoOverflow($i);

            $dates[] = [
                // 'label'    => $from->format('Y'), // Year label
                'label'    => $this->getDateRangeLabel($from, $to,$dateRangeSelector), // Year label
                'fromDate' => $from->format('Y-m-d'),
                'toDate'   => $to->format('Y-m-d'),
            ];
        }

        return $dates;
    }


    public function getDateRangeLabel($from,$to,$dateRangeSelector)
    {
        return match ($dateRangeSelector){

            "Today" => $from->format('d M Y'),
            "Yesterday" => $from->format('d M Y'),

            "This Year" => $from->format('Y'),
            "Previous Year" => $from->format('Y'),
           
            default => $from->format('M Y')
        };
    }
}
