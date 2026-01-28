@extends('tmp')

@section('title', 'Balance Sheet')


@section('content')

    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <div class="row justify-content-center">
                    <div class="col-md-10">
                         <form action="{{ url('comparison-reports/balance-sheet') }}" method="GET" class="align-items-end">

                            @include('comparison_reports._search')

                        </form>

                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-10 text-center mt-3">
                        {{-- ASSETS TABLE --}}
                        <div class="card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-nowrap">
                                        <thead>
                                            <tr class="bg-dark text-white">
                                                <th class="text-start" style="width: 400px">ASSETS</th>
                                                @foreach ($dates as $date)
                                                    <th style="width: 100px" class="text-center">{{ $date['label'] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $assetTotals = array_fill(0, count($dates), 0); @endphp

                                            @forelse($asset as $level2)
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
                                                        @php $assetTotals[$i] += $total; @endphp
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
                                                <td>Assets Grand Total</td>
                                                @foreach ($assetTotals as $total)
                                                    <td class="text-end">{{ number_format($total, 2) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        {{-- LIBALITIY TABLE --}}
                        <div class="card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-nowrap">
                                        <thead>
                                            <tr class="bg-dark text-white">
                                                <th class="text-start" style="width: 400px">LIABILITIES</th>
                                                @foreach ($dates as $date)
                                                    <th style="width: 100px" class="text-center">{{ $date['label'] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $liabilityTotals = array_fill(0, count($dates), 0);
                                            @endphp

                                            @forelse($liability as $level2)
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
                                                        @php $liabilityTotals[$i] += $total; @endphp
                                                        <td class="text-end">{{ number_format($total, 2) }}</td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($dates) + 1 }}" class="text-center">No data
                                                        available</td>
                                                </tr>
                                            @endforelse

                                            <tr class="table-danger fw-bold">
                                                <td>Liability Grand Total</td>
                                                @foreach ($liabilityTotals as $total)
                                                    <td class="text-end">{{ number_format($total, 2) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        {{-- EQUITY TABLE --}}
                        <div class="card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-nowrap">
                                        <thead>
                                            <tr class="bg-dark text-white">
                                                <th class="text-start" style="width: 400px">EQUITY</th>
                                                @foreach ($dates as $date)
                                                    <th style="width: 100px" class="text-center">{{ $date['label'] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $equityTotals = array_fill(0, count($dates), 0);
                                            @endphp

                                            @forelse($equity as $level2)
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
                                                        @php $equityTotals[$i] += $total; @endphp
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
                                                <td>Equity Grand Total</td>
                                                @foreach ($equityTotals as $total)
                                                    <td class="text-end">{{ number_format($total, 2) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- SUSPENSE TABLE --}}
                        <div class="card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-nowrap">
                                        <thead>
                                            <tr class="bg-dark text-white">
                                                <th class="text-start" style="width: 400px">SUSPENSE</th>
                                                @foreach ($dates as $date)
                                                    <th style="width: 100px" class="text-center">{{ $date['label'] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $suspenseTotals = array_fill(0, count($dates), 0);
                                            @endphp

                                            @forelse($suspense as $level2)
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
                                                        @php $suspenseTotals[$i] += $total; @endphp
                                                        <td class="text-end">{{ number_format($total, 2) }}</td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($dates) + 1 }}" class="text-center">No data
                                                        available</td>
                                                </tr>
                                            @endforelse

                                            <tr class="table-warning fw-bold">
                                                <td>Suspense Grand Total</td>
                                                @foreach ($suspenseTotals as $total)
                                                    <td class="text-end">{{ number_format($total, 2) }}</td>
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
