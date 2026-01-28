@extends('tmp')

@section('title', 'Item Wise Sale Report')


@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <div class="row justify-content-center">
                    <div class="col-md-12">
                       <form action="{{ url('comparison-reports/item-wise-sales') }}" method="GET" class=" align-items-end">

                            @include('comparison_reports._search')

                        </form>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                       @include('comparison_reports.item_wise_sales._filter')
                    </div>
                </div>



                {{-- <div class="card mt-2"> --}}
                    <div class="card rounded-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-nowrap">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th rowspan="2">Item Name</th>
                                        @foreach ($dates as $date)
                                            <th colspan="3" class="text-center date-header">
                                                {{ $date['label'] }}
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach ($dates as $date)
                                            <th class="text-end sortable no-of-sales">Sales Count</th>
                                            <th class="text-end sortable total-invoice-amount">Total Inv.</th>
                                            <th class="text-end sortable profit">Profit</th>
                                        @endforeach    
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    @foreach ($finalData as $row)
                                        <tr>
                                            <td> {{ $row['name'] }} </td>
                                            @foreach ($row['sales'] as $sale)
                                                <td class="text-end no-of-sales">{{ $sale['no_of_sales'] }}</td>
                                                <td class="text-end total-invoice-amount">{{ number_format($sale['total_invoice_amount'], 2) }}</td>
                                                <td class="text-end profit">{{ number_format($sale['profit'], 2) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td class="text-end">Total</td>
                                        @foreach ($totals as $total)
                                            <td class="text-end no-of-sales">{{ $total['no_of_sales'] }}</td>
                                            <td class="text-end total-invoice-amount">{{ number_format($total['total_invoice_amount'], 2) }}</td>
                                            <td class="text-end profit">{{ number_format($total['profit'], 2) }}</td>
                                        @endforeach
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>
                {{-- </div> --}}
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll(".sortable").forEach((header, columnIndex) => {
            header.addEventListener("click", () => {
                const table = header.closest("table");
                const tbody = table.querySelector("tbody");
                const rows = Array.from(tbody.querySelectorAll("tr"));
                const asc = header.classList.toggle("asc");
                header.classList.toggle("desc", !asc);

                rows.sort((a, b) => {
                    let A = a.children[columnIndex + 1].innerText.replace(/,/g, '');
                    let B = b.children[columnIndex + 1].innerText.replace(/,/g, '');
                    return asc ? A - B : B - A;
                });

                rows.forEach(row => tbody.appendChild(row));
            });
        });
    </script>

    <script>
        $(document).ready(function () {

            function updateView(type) {
                $('.no-of-sales, .total-invoice-amount, .profit').addClass('d-none');

                if (type === 'all') {
                    $('.no-of-sales, .total-invoice-amount, .profit').removeClass('d-none');
                    $('.date-header').attr('colspan', 3);
                }

                if (type === 'no_sale') {
                    $('.no-of-sales').removeClass('d-none');
                    $('.date-header').attr('colspan', 1);
                }

                if (type === 'invoice') {
                    $('.total-invoice-amount').removeClass('d-none');
                    $('.date-header').attr('colspan', 1);
                }

                if (type === 'profit') {
                    $('.profit').removeClass('d-none');
                    $('.date-header').attr('colspan', 1);
                }
            }

            $('input[name="view_type"]').on('change', function () {
                updateView($(this).val());
            });

            updateView('all');
        });
    </script>
@endsection    