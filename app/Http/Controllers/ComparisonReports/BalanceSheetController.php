<?php

namespace App\Http\Controllers\ComparisonReports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BalanceSheetController extends Controller
{
    public function show(Request $request)
    {
        $fromDate = $request->fromDate ?? date('Y-m-01');
        $toDate   = $request->toDate   ?? date('Y-m-t');
        $comparedType = $request->comparedType ?? 'period';
        $comparedCount = $request->comparedCount ?? 1;
        $dateRangeSelector = $request->dateRangeSelector ?? 'This Month';

        // Generate comparison dates
        if ($comparedType === 'year') {
            $dates = $this->comparedYearDates($fromDate, $toDate, $comparedCount,$dateRangeSelector);
        } else {
            $dates = $this->comparedMonthDates($fromDate, $toDate, $comparedCount);
        }


        $asset = $this->generateReport($dates,'A');
        $liability = $this->generateReport($dates,'L');
        $equity = $this->generateReport($dates,'C');
        $suspense = $this->generateReport($dates,'S');

        return view('comparison_reports.balance_sheet.show', compact(
            'fromDate',
            'toDate',
            'comparedType',
            'comparedCount',
            'asset',
            'liability',
            'equity',
            'suspense',
            'dates',
            'dateRangeSelector'
        ));

    }

    
    public function generateReport($dates,$code)
    {
        // Level 2  Accounts
        $level2Accounts = $this->getLevel2Account($code);

        $finalData = [];


        foreach($level2Accounts as $level2)
        {
            // Reset Level 2 container
            $level2Data = [
                'level2Name' => $level2->ChartOfAccountName,
                'level3' => []
            ];

            $level2Balance = 0;

            // Level 3 Accounts
            $level3Accounts = $this->getLevel2ChildAccounts($level2->ChartOfAccountID,$code);


            foreach ($level3Accounts as $level3) {
                
                $level3Data = [];

                 foreach ($dates as $date) {

                    $journal = $this->getAccountDebitAndCredit($level3->ChartOfAccountID,$date['fromDate'],$date['toDate']);

                    $level3Data[] = [
                        'label' => $date['label'],
                        'dr' => $journal->dr ?? 0,
                        'cr' => $journal->cr ?? 0,
                    ];
                }

                //check balance if whole date range is zero then dont add that  
                $level3Balance = $this->getBalanceAmount($level3Data);

                $level2Balance += $level3Balance;

                if($level3Balance != 0)
                {
                    $level2Data['level3'][] = [
                        'name' => $level3->ChartOfAccountName,
                        'data' => $level3Data
                    ];
                }
                
            }

            if($level2Balance != 0)
            {
                $finalData[] = $level2Data;
            }

        }

        return $finalData;


    }

    public function getLevel2Balance(array $level2Data)
    {
        return response()->json(collect($level2Data));
        
    }

    public function getBalanceAmount(array $level3Data)
    {
        return collect($level3Data)->sum(function ($item) {
            return $item['cr'] - $item['dr'];
        });
    }

    public function getLevel2Account($code)
    {
        return  DB::table('chartofaccount')
            ->where('CODE', $code)
            ->where('Level', 2)
            ->get();
    }

    public function getLevel2ChildAccounts($parent_coa_id, $code)
    {
        return DB::table('chartofaccount')
            ->where('CODE', $code)
            ->where('Level', 3)
            ->where('L2', $parent_coa_id)
            ->get();

    }

    public function getAccountDebitAndCredit($coa_id,$startDate,$endDate)
    {
        return DB::table('journal')
            ->where('ChartOfAccountID', $coa_id)
            ->whereBetween('Date', [$startDate, $endDate])
            ->selectRaw('SUM(Dr) as dr, SUM(Cr) as cr')
            ->first();
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
