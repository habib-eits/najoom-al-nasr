<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Voucher</title>

<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        color: #000;
        margin: 0;
        padding: 0;
    }

    .voucher {
        width: 800px;
        margin: auto;
        padding: 20px;
    }

    .company-name {
        font-size: 22px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 5px;
    }

    .company-info {
        text-align: center;
        font-size: 12px;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .voucher-title {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin: 20px 0;
        text-transform: uppercase;
        border-bottom: 2px solid #000;
        display: inline-block;
        padding-bottom: 5px;
    }

    .voucher-meta {
        width: 100%;
        margin-bottom: 15px;
    }

    .voucher-meta td {
        font-size: 12px;
        padding: 4px 0;
    }

    .voucher-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .voucher-table th {
        background: #f2f2f2;
        border: 1px solid #999;
        padding: 6px;
        font-size: 12px;
        text-align: left;
    }

    .voucher-table td {
        border: 1px solid #ccc;
        padding: 6px;
        font-size: 12px;
    }

    .text-right {
        text-align: right;
    }

    .totals-row td {
        font-weight: bold;
        border-top: 2px solid #000;
    }

    .signatures {
        width: 100%;
        margin-top: 40px;
    }

    .signatures td {
        width: 33%;
        text-align: center;
        font-size: 12px;
        padding-top: 40px;
    }

    .signature-line {
        border-top: 1px solid #000;
        margin-top: 30px;
        padding-top: 5px;
    }

    @media print {
        body {
            margin: 0;
        }
        .voucher {
            page-break-after: always;
        }
    }
</style>

@php
    $company = DB::table('company')->first();
@endphp
</head>

<body>

@foreach($voucher_master as $value)
<div class="voucher">

    <!-- Company Header -->
    <div class="company-name">{{ $company->Name }}</div>
    <div class="company-info">
        {{ $company->Address }}<br>
        Phone: {{ $company->Contact }}
    </div>

    <!-- Voucher Title -->
    <div style="text-align:center;">
        <div class="voucher-title">{{ $voucher_master[0]->VoucherTypeName }}</div>
                <img align="right" width="80px" src="{{ asset('assets/qr-code.png') }}" alt="">

        
    </div>

    <!-- Voucher Meta -->
    <table class="voucher-meta">
        <tr>
            <td><strong>Voucher #:</strong> {{ $voucher_master[0]->Voucher }}</td>
            <td class="text-right"><strong>Date:</strong> {{ $value->Date }}</td>
        </tr>
    </table>

    <!-- Voucher Details -->
    <table class="voucher-table">
        <thead>
            <tr>
                <th>Chart of A/C</th>
                <th>Description</th>
                <th>Ref #</th>
                <th>Party</th>
                <th>Supplier</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Credit</th>
            </tr>
        </thead>
        <tbody>

        @php
            $voucher = DB::table('v_voucher_detail')
                        ->where('VoucherMstID',$voucher_master[0]->VoucherMstID)
                        ->get();

            $DebitTotal = 0;
            $CreditTotal = 0;
        @endphp

        @foreach($voucher as $row)
            @php
                $DebitTotal += $row->Debit ?? 0;
                $CreditTotal += $row->Credit ?? 0;
            @endphp
            <tr>
                <td>{{ $row->ChartOfAccountName }}</td>
                <td>{{ $row->Narration }}</td>
                <td>{{ $row->RefNo }}</td>
                <td>{{ $row->PartyName }}</td>
                <td>{{ $row->SupplierName }}</td>
                <td class="text-right">{{ $row->Debit ? number_format($row->Debit,2) : '' }}</td>
                <td class="text-right">{{ $row->Credit ? number_format($row->Credit,2) : '' }}</td>
            </tr>
        @endforeach

        <!-- Totals -->
        <tr class="totals-row">
            <td colspan="5">{{ env('APP_CURRENCY') }}</td>
            <td class="text-right">{{ number_format($DebitTotal,2) }}</td>
            <td class="text-right">{{ number_format($CreditTotal,2) }}</td>
        </tr>

        </tbody>
    </table>

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Paid / Checked By</div>
                <small>Operator: Administrator</small>
            </td>
            <td>
                <div class="signature-line">Authorized By</div>
            </td>
            <td>
                <div class="signature-line">Received By</div>
            </td>
        </tr>
    </table>

</div>
@endforeach

</body>
</html>
