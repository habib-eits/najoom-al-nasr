@extends('tmp')

@section('title', 'Profit Loss')


@section('content')

    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <div class="row justify-content-center">
                    <div class="col-md-10">
                         <form action="{{ url('comparison-reports/profit-loss') }}" method="GET" class="align-items-end">

                            @include('comparison_reports._search')

                        </form>

                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-10 text-center mt-3">
                        {{-- REVENUE TABLE --}}
                        <div class="card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-nowrap">
                                        <thead>
                                            <tr class="bg-dark text-white">
                                                <th class="text-start" style="width: 400px">REVENUE</th>
                                                @foreach ($dates as $date)
                                                    <th style="width: 100px" class="text-center">{{ $date['label'] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $revenueTotals = array_fill(0, count($dates), 0); @endphp

                                            @forelse($revenue as $level2)
                                                @php $totals = array_fill(0, count($dates), 0); @endphp

                                                <tr>
                                                    <th class="text-start">{{ $level2['level2Name'] }}</th>
                                                    @foreach ($dates as $date)
                                                        <th class="text-center"></th>
                                                    @endforeach
                                                </tr>

                                                @foreach ($level2['level3'] as $level3)
                                                    <tr>
                                                        <td class="text-start" style="padding-left: 30px;">
                                                            {{ $level3['name'] }}</td>
                                                        @foreach ($level3['data'] as $i => $data)
                                                            @php
                                                                $amount = $data['cr'] - $data['dr'];
                                                                $totals[$i] += $amount;
                                                            @endphp
                                                            <td class="text-end">{{ number_format($amount, 2) }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach

                                                <tr class="table-light fw-bold">
                                                    <td>{{ $level2['level2Name'] }} Total</td>
                                                    @foreach ($totals as $i => $total)
                                                        @php $revenueTotals[$i] += $total; @endphp
                                                        <td class="text-end">{{ number_format($total, 2) }}</td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($dates) + 1 }}" class="text-center">No data
                                                        available</td>
                                                </tr>
                                            @endforelse

                                            <tr class="table-success fw-bold">
                                                <td>Revenue Grand Total</td>
                                                @foreach ($revenueTotals as $total)
                                                    <td class="text-end">{{ number_format($total, 2) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        {{-- EXPENSES TABLE --}}
                        <div class="card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-nowrap">
                                        <thead>
                                            <tr class="bg-dark text-white">
                                                <th class="text-start" style="width: 400px">EXPENSE</th>
                                                @foreach ($dates as $date)
                                                    <th style="width: 100px" class="text-center">{{ $date['label'] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $expenseTotals = array_fill(0, count($dates), 0);
                                            @endphp

                                            @forelse($expense as $level2)
                                                @php $totals = array_fill(0, count($dates), 0); @endphp

                                                <tr>
                                                    <th class="text-start">{{ $level2['level2Name'] }}</th>
                                                    @foreach ($dates as $date)
                                                        <th class="text-center"></th>
                                                    @endforeach
                                                </tr>

                                                @foreach ($level2['level3'] as $level3)
                                                    <tr>
                                                        <td class="text-start" style="padding-left: 30px;">
                                                            {{ $level3['name'] }}</td>
                                                        @foreach ($level3['data'] as $i => $data)
                                                            @php
                                                                $amount = $data['dr'] - $data['cr'];
                                                                $totals[$i] += $amount;
                                                            @endphp
                                                            <td class="text-end">{{ number_format($amount, 2) }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach

                                                <tr class="table-light fw-bold">
                                                    <td>{{ $level2['level2Name'] }} Total</td>
                                                    @foreach ($totals as $i => $total)
                                                        @php $expenseTotals[$i] += $total; @endphp
                                                        <td class="text-end">{{ number_format($total, 2) }}</td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($dates) + 1 }}" class="text-center">No data
                                                        available</td>
                                                </tr>
                                            @endforelse

                                            <tr class="table-primary fw-bold">
                                                <td>Expenses Grand Total</td>
                                                @foreach ($expenseTotals as $total)
                                                    <td class="text-end">{{ number_format($total, 2) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        

                        {{-- PROFIT / LOSS TABLE --}}
                        <div class="card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-nowrap">
                                        <thead>
                                            <tr class="bg-dark text-white">
                                                <th class="text-start" style="width: 400px">Profit / Loss</th>
                                                @foreach ($dates as $date)
                                                    <th style="width: 100px" class="text-center">{{ $date['label'] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $profitLoss = [];
                                                foreach ($dates as $i => $date) {
                                                    $rev = $revenueTotals[$i] ?? 0;
                                                    $exp = $expenseTotals[$i] ?? 0;
                                                    $profitLoss[$i] = $rev - $exp;
                                                }
                                            @endphp

                                            <tr class="table-warning fw-bold">
                                                <td>Profit / Loss</td>
                                                @foreach ($profitLoss as $pl)
                                                    <td class="text-end">{{ number_format($pl, 2) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
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





@endsection
