<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_inv_paid_refund AS
            SELECT
                invoice_master.InvoiceMasterID AS InvoiceMasterID,
                invoice_master.GrandTotal AS Total,
                voucher_detail.InvoiceNo AS InvoiceNo,
                SUM(IFNULL(voucher_detail.Debit, 0)) AS Paid,
                invoice_master.GrandTotal - SUM(IFNULL(voucher_detail.Debit, 0)) AS Balance
            FROM invoice_master
            LEFT JOIN voucher_detail
                ON invoice_master.InvoiceMasterID = voucher_detail.InvoiceNo
            WHERE
                voucher_detail.InvoiceNo IS NOT NULL
                AND voucher_detail.ChOfAcc = '110400'
            GROUP BY
                invoice_master.InvoiceMasterID,
                invoice_master.GrandTotal,
                voucher_detail.InvoiceNo
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_inv_paid_refund");
    }
};
