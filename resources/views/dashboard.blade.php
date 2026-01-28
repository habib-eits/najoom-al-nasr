@extends('template.tmp')

@section('title', $pagetitle)


@section('content')
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />

    <style id="compiled-css" type="text/css">
        .highcharts-figure,
        .highcharts-data-table table {
            min-width: 360px;
            max-width: 800px;
            margin: 1em auto;
        }

        .highcharts-data-table table {
            font-family: Verdana, sans-serif;
            border-collapse: collapse;
            border: 1px solid #ebebeb;
            margin: 10px auto;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .highcharts-data-table caption {
            padding: 1em 0;
            font-size: 1.2em;
            color: #555;
        }

        .highcharts-data-table th {
            font-weight: 600;
            padding: 0.5em;
        }

        .highcharts-data-table td,
        .highcharts-data-table th,
        .highcharts-data-table caption {
            padding: 0.5em;
        }

        .highcharts-data-table thead tr,
        .highcharts-data-table tr:nth-child(even) {
            background: #f8f8f8;
        }

        .highcharts-data-table tr:hover {
            background: #f1f7ff;
        }


        .page-content {
            background: #E9E8F9 !important;
        }

        /* EOS */



        .bg-primary {
            --bs-bg-opacity: 1;
            background-color: rgb(25 57 209) !important;
        }


        .bg-primary2 {
            --bs-bg-opacity: 1;
            background-color: #008476 !important;
        }


        .bg-primary3 {
            --bs-bg-opacity: 1;
            background-color: #805475 !important;
        }


        .bg-primary4 {
            --bs-bg-opacity: 1;
            background-color: #2a3042 !important;
        }


        .card-body {
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1.0rem 1.0rem !important;
        }




        .order-card {
            color: #fff;
        }

        .bg-c-blue {
    background: linear-gradient(45deg, #4099ff, #73b4ff);
}

.bg-c-green {
    background: linear-gradient(45deg, #2ed8b6, #59e0c5);
}

.bg-c-yellow {
    background: linear-gradient(45deg, #FFB64D, #ffcb80);
}

.bg-c-pink {
    background: linear-gradient(45deg, #FF5370, #ff869a);
}

/* Additional cool gradient colors */
.bg-c-purple {
    background: linear-gradient(45deg, #8E2DE2, #4A00E0);
}

.bg-c-orange {
    background: linear-gradient(45deg, #FF5F6D, #FFC371);
}

.bg-c-teal {
    background: linear-gradient(45deg, #00c6ff, #0072ff);
}

.bg-c-indigo {
    background: linear-gradient(45deg, #667eea, #764ba2);
}

.bg-c-red {
    background: linear-gradient(45deg, #f093fb, #f5576c);
}

.bg-c-cyan {
    background: linear-gradient(45deg, #4facfe, #00f2fe);
}


        .card {
            border-radius: 5px;
            -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
            box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
            border: none;
            margin-bottom: 30px;
            -webkit-transition: all 0.3s ease-in-out;
            transition: all 0.3s ease-in-out;
        }

        .card .card-block {
            padding: 25px;
        }

        .order-card i {
            font-size: 26px;
        }

        .f-left {
            float: left;
        }

        .f-right {
            float: right;
        }

        .media-body {

            margin-left: 25 !important;
        }
    </style>





    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Dashboard</h4>

                            <div class="page-title-right ">
                                <strong class="text-danger">{{ session::get('Email') }}</strong>

                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->



                @if (session('error'))
                    <div class="alert alert-{{ Session::get('class') }} p-3" id="success-alert">

                        {{ Session::get('error') }}
                    </div>
                @endif

                @if (count($errors) > 0)

                    <div>
                        <div class="alert alert-danger pt-3 pl-0   border-3 bg-danger text-white">
                            <p class="font-weight-bold"> There were some problems with your input.</p>
                            <ul>

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                @endif

                    <div class="row">
                        @foreach ($cards as $card)
                            <div class="col-md-4 col-sm-6 col-lg-3 col-xl-2 mb-3">
                                <div class="card order-card" style="background-color: {{ $card['color'] }}">
                                    <div class="card-block">

                                        <h6 class="m-b-20 text-white">
                                            {{ $card['title'] }}
                                        </h6>

                                        <h2 class="text-white mt-3 d-flex align-items-center justify-content-between">
                                            <i class="{{ $card['icon'] }}" style="font-size:18px"></i>
                                            <span style="font-size:18px">
                                                {{ number_format($card['amount'], 2) }}
                                            </span>
                                        </h2>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if (Session::get('UserType') === 'Admin')
                            @foreach ($adminCards as $card)
                                <div class="col-md-4 col-sm-6 col-lg-3 col-xl-2 mb-3">
                                    <div class="card order-card" style="background-color: {{ $card['color'] }}">
                                        <div class="card-block">

                                            <h6 class="m-b-20 text-white">
                                                {{ $card['title'] }}
                                            </h6>

                                            <h2 class="text-white mt-3 d-flex align-items-center justify-content-between">
                                                <i class="{{ $card['icon'] }}" style="font-size:18px"></i>
                                                <span style="font-size:18px">
                                                    <a href="{{ $card['link'] }}" class="text-white">
                                                        {{ number_format($card['amount'], 2) }}
                                                    </a>
                                                </span>
                                            </h2>

                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        @endif
                        
                    </div>












                </div>
                @if (Session::get('UserType') == 'Admin')
                  

                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body border-secondary border-top border-3 rounded-top">

                                        <div class="text-muted mt-4">
                                            <div id="sale_register"></div>
                                            <div class="d-flex">
                                                <span class="ms-2 text-truncate mt-3"> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body border-secondary border-top border-3 rounded-top">

                                        <div class="text-muted mt-4">
                                            <div id="container2"></div>
                                            <div class="d-flex">
                                                <span class="ms-2 text-truncate mt-3"> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body border-secondary border-top border-3 rounded-top">

                                        <div class="text-muted mt-4">
                                            <div id="container4"></div>
                                            <div class="d-flex">
                                                <span class="ms-2 text-truncate mt-3"> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body border-secondary border-top border-3 rounded-top">

                                        <div class="text-muted mt-4">
                                            <div id="sale_report"></div>
                                            <div class="d-flex">
                                                <span class="ms-2 text-truncate mt-3"> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body border-secondary border-top border-3 rounded-top">

                                        <div class="text-muted mt-4">
                                            <div id="container3"></div>
                                            <div class="d-flex">
                                                <span class="ms-2 text-truncate mt-3"> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body border-secondary border-top border-3 rounded-top">

                                        <div class="text-muted mt-4">
                                            <div id="container"></div>
                                            <div class="d-flex">
                                                <span class="ms-2 text-truncate mt-3"> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif



                <script src="https://code.highcharts.com/highcharts.js"></script>
                <script src="https://code.highcharts.com/modules/series-label.js"></script>
                <script src="https://code.highcharts.com/modules/exporting.js"></script>
                <script src="https://code.highcharts.com/modules/export-data.js"></script>
                <script src="https://code.highcharts.com/modules/accessibility.js"></script>


                <script>
                    Highcharts.chart('container2', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Monthly Income & Expense'
                        },

                        xAxis: {
                            categories: [


                                @foreach ($cash1 as $value)



                                    '{{ $value->Date }}',
                                @endforeach




                            ],
                            crosshair: true
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Amount'
                            }
                        },

                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            }
                        },
                        series: [

                            {
                                name: 'Income',
                                data: [

                                    @foreach ($cash1 as $value)



                                        {{ $value->Rev }},
                                    @endforeach

                                ]

                            }, {
                                name: 'Expense',
                                data: [

                                    @foreach ($cash1 as $value)



                                        {{ $value->Exp }},
                                    @endforeach

                                ]

                            }
                        ],
                        credits: {
                            enabled: false
                        },
                    });
                </script>


                <script>
                    Highcharts.chart('sale_register', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            useHTML: true,
                            text: 'Saleman Ticket Register <span style="color:red; font-weight:bold; margin-right:6px;">( Daily Avg : {{ number_format($avg, 2) }})</span>'
                        },

                        subtitle: {
                            text: '<a href="{{ URL('/SalemanTicketShowAll') }}" >DETAIL REPORT</a>'
                        },

                        xAxis: {
                            categories: {!! json_encode($ticket_register->pluck('SalemanName')) !!},
                            crosshair: true
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Amount'
                            },

                            plotLines: [{
                                color: 'red', // Line color
                                value: {{ $avg }}, // Target value
                                width: 2, // Line width
                                label: {
                                    text: '', // Label text
                                    align: 'right',
                                    style: {
                                        color: 'red'
                                    }
                                }
                            }]

                        },

                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            }
                        },


                        series: [

                           
                            {
                                showInLegend: false,
                                data: {!! json_encode($ticket_register->pluck('Service')) !!}
                            }
                        ],
                        credits: {
                            enabled: false
                        },
                    });
                </script>



                <script type="text/javascript">
                    //<![CDATA[


                    Highcharts.chart('container', {

                        title: {
                            text: 'Cash Flow'
                        },


                        yAxis: {
                            title: {
                                text: 'Amount'
                            }
                        },

                        xAxis: {
                            categories: [
                                @foreach ($v_cashflow as $value)
                                    '{{ $value->MonthName }}',
                                @endforeach
                            ],
                            // crosshair: true
                        },





                        series: [{
                            // name: 'CashFlow',
                            showInLegend: false,
                            name: ' ',
                            data: [
                                @foreach ($v_cashflow as $value)
                                    {{ $value->Balance }},
                                @endforeach
                            ]
                        }],

                        responsive: {
                            rules: [{
                                condition: {
                                    maxWidth: 500
                                },
                                chartOptions: {
                                    legend: {
                                        layout: 'horizontal',
                                        align: 'center',
                                        verticalAlign: 'bottom'
                                    }
                                }
                            }]
                        },
                        credits: {
                            enabled: false
                        },

                    });


                    //]]>
                </script>






                <script>
                    // Create the chart
                    Highcharts.chart('container3', {
                        chart: {
                            type: 'pie'
                        },
                        title: {
                            text: 'Expenses'
                        },


                        accessibility: {
                            announceNewData: {
                                enabled: true
                            },
                            point: {
                                valueSuffix: ''
                            }
                        },

                        plotOptions: {
                            series: {
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.name}: {point.y:.1f}'
                                }
                            }
                        },

                        tooltip: {
                            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> <br/>'
                        },

                        series: [{
                            // name: "Browsers",
                            colorByPoint: true,
                            data: [


                                @foreach ($exp_chart as $value)


                                    {

                                        name: '{{ $value->ChartOfAccountName }}',
                                        y: {{ $value->Balance }},
                                    },
                                @endforeach








                            ]
                        }],

                    });
                </script>




                <script>
                    // Create the chart
                    Highcharts.chart('container4', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Cash Summary'
                        },

                        accessibility: {
                            announceNewData: {
                                enabled: true
                            }
                        },
                        xAxis: {
                            type: 'category'
                        },
                        yAxis: {
                            title: {
                                text: 'Amount'
                            }

                        },
                        legend: {
                            enabled: false
                        },
                        plotOptions: {
                            series: {
                                borderWidth: 0,
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.y:.1f}'
                                }
                            }
                        },



                        series: [{
                            name: "",
                            colorByPoint: true,
                            data: [




                                @foreach ($cash as $value)

                                    {
                                        name: "{{ $value->ChartOfAccountName }}",
                                        y: {{ round($value->Balance, 2) }},
                                    },
                                @endforeach







                            ]
                        }],
                        drilldown: {
                            breadcrumbs: {
                                position: {
                                    align: 'right'
                                }
                            },
                            series: [{
                                    name: "Chrome",
                                    id: "Chrome",
                                    data: [
                                        [
                                            "v65.0",
                                            0.1
                                        ],
                                        [
                                            "v64.0",
                                            1.3
                                        ],
                                        [
                                            "v63.0",
                                            53.02
                                        ],
                                        [
                                            "v62.0",
                                            1.4
                                        ],
                                        [
                                            "v61.0",
                                            0.88
                                        ],
                                        [
                                            "v60.0",
                                            0.56
                                        ],
                                        [
                                            "v59.0",
                                            0.45
                                        ],
                                        [
                                            "v58.0",
                                            0.49
                                        ],
                                        [
                                            "v57.0",
                                            0.32
                                        ],
                                        [
                                            "v56.0",
                                            0.29
                                        ],
                                        [
                                            "v55.0",
                                            0.79
                                        ],
                                        [
                                            "v54.0",
                                            0.18
                                        ],
                                        [
                                            "v51.0",
                                            0.13
                                        ],
                                        [
                                            "v49.0",
                                            2.16
                                        ],
                                        [
                                            "v48.0",
                                            0.13
                                        ],
                                        [
                                            "v47.0",
                                            0.11
                                        ],
                                        [
                                            "v43.0",
                                            0.17
                                        ],
                                        [
                                            "v29.0",
                                            0.26
                                        ]
                                    ]
                                },
                                {
                                    name: "Firefox",
                                    id: "Firefox",
                                    data: [
                                        [
                                            "v58.0",
                                            1.02
                                        ],
                                        [
                                            "v57.0",
                                            7.36
                                        ],
                                        [
                                            "v56.0",
                                            0.35
                                        ],
                                        [
                                            "v55.0",
                                            0.11
                                        ],
                                        [
                                            "v54.0",
                                            0.1
                                        ],
                                        [
                                            "v52.0",
                                            0.95
                                        ],
                                        [
                                            "v51.0",
                                            0.15
                                        ],
                                        [
                                            "v50.0",
                                            0.1
                                        ],
                                        [
                                            "v48.0",
                                            0.31
                                        ],
                                        [
                                            "v47.0",
                                            0.12
                                        ]
                                    ]
                                },
                                {
                                    name: "Internet Explorer",
                                    id: "Internet Explorer",
                                    data: [
                                        [
                                            "v11.0",
                                            6.2
                                        ],
                                        [
                                            "v10.0",
                                            0.29
                                        ],
                                        [
                                            "v9.0",
                                            0.27
                                        ],
                                        [
                                            "v8.0",
                                            0.47
                                        ]
                                    ]
                                },
                                {
                                    name: "Safari",
                                    id: "Safari",
                                    data: [
                                        [
                                            "v11.0",
                                            3.39
                                        ],
                                        [
                                            "v10.1",
                                            0.96
                                        ],
                                        [
                                            "v10.0",
                                            0.36
                                        ],
                                        [
                                            "v9.1",
                                            0.54
                                        ],
                                        [
                                            "v9.0",
                                            0.13
                                        ],
                                        [
                                            "v5.1",
                                            0.2
                                        ]
                                    ]
                                },


                            ]
                        }
                    });



                    // sale report chart



                    Highcharts.chart('sale_report', {
                        chart: {
                            type: 'pie'
                        },
                        title: {
                            text: 'Item Wise Sale'
                        },
                        tooltip: {
                            valueSuffix: ''
                        },

                        subtitle: {
                            text: '<a href="{{ URL('/ItemWiseSale2Showall') }}" >DETAIL REPORT</a>'
                        },



                        plotOptions: {
                            series: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: [{
                                    enabled: true,
                                    distance: 20
                                }, {
                                    enabled: true,
                                    distance: -40,
                                    format: '{point.percentage:.1f}%',
                                    style: {
                                        fontSize: '1.2em',
                                        textOutline: 'none',
                                        opacity: 0.7
                                    },
                                    filter: {
                                        operator: '>',
                                        property: 'percentage',
                                        value: 10
                                    }
                                }]
                            }
                        },
                        series: [{
                            name: 'No of sale ',
                            colorByPoint: true,
                            data: [



                                @foreach ($sale_report as $value)
                                    {
                                        name: "{{ $value->ItemName }}",
                                        y: {{ $value->Total }}
                                    },
                                @endforeach



                            ]
                        }]
                    });




                    // end of sale report chart
                </script>



            </div>
            <!-- end row -->
        </div>
    </div>



    </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->


    </div>

@endsection
