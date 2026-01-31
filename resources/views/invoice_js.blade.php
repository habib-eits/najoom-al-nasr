<script>
     var i = $('table tr').length;

        $(".addmore").on('click', function() {
            var html = '<tr class="bg-light borde-1 border-light " style="vertical-align:top;">';
            html += '<td class="p-1"><input class="case" type="checkbox"/></td>';
            html += '<td><div class=""><select style="width:100%" name="ItemID0[]" id="ItemID0_' + i +
                '" class="form-select select2 item-select" required>';
            html += '<option value="">Select</option>'; // Add the Select option
            html +=
                '@foreach ($items as $key => $value) <option value="{{ $value->ItemID }}" data-tax="{{ $value->Percentage }}">{{ $value->ItemCode }}-{{ $value->ItemName }}-{{ $value->Percentage }}</option>@endforeach</select></div>';
            html += '<select style="width:100%" name="SupplierID[]" id="SupplierID_' + i +
                '" onchange="ajax_balance(this.value);" class="form-select select2" required><option value="">Select</option>@foreach ($supplier as $key => $value) <option value="{{ $value->SupplierID }}">{{ $value->SupplierName }}</option>@endforeach</td>';

            html += '<td class="d-none"><input type="text" name="RefNo[]" id="RefNo_' + i +
                '" class="form-control" placeholder="RefNo"><input type="text" name="VisaType[]" id="VisaType_' +
                i + '" class="form-control" placeholder="Visa"></td>';
            // html += '<td>visa</td>';
            html += '<td><input type="text" name="PaxName[]" id="PaxName_' + i +
                '" class="form-control" placeholder="PaxName"><input type="text" name="TicketNo[]" id="TicketNo_' +
                i + '" class="form-control  " autocomplete="off" placeholder="Ticket No"></td>';
            // html += '<td>pnr</td>';
            html += '<td><input type="text" name="Sector[]" id="Sector_' + i +
                '" class="form-control" placeholder="Sector"><input type="text" name="PNR[]" id="PNR_' + i +
                '" class="form-control" placeholder="PNR"></td>';
            html += '<td><input type="text" required name="Fare[]" id="Fare_' + i +
                '" class="form-control row-calculation" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;" placeholder="Fare"><input type="text" name="Passport[]" id="Passport_' +
                i + '" class="form-control  " autocomplete="off" placeholder="Passport #"></td>';
            html += '<td><input readonly type="text" name="TaxPer[]" id="TaxPer_' + i +
                '" class="form-control" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"  placeholder="VAT%"><input readonly type="text" name="Taxable[]" id="Taxable_' +
                i +
                '" class="form-control" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;" placeholder="VAT Amt"></td>';
            html += '<td><input type="text" name="Service[]" id="Service_' + i +
                '" class="form-control row-calculation" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;" placeholder="Service"><input type="text" name="Discount[]" id="discount_' +
                i +
                '" class="form-control" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;" placeholder="Discount"></td>';
            html += '<td class="d-none"><input type="text" name="OPVAT[]" id="OPVAT_' + i +
                '" class="form-control" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"></td>';
            html += '<td class="d-none"><input type="text" name="IPVAT[]" id="IPVAT_' + i +
                '" class="form-control" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"></td>';
            // html += '<td>tax</td>';
            // html += '<td>service</td>';
            html += '<td><input readonly type="text" required name="ItemTotal[]" id="total_' + i +
                '" class="form-control totalLinePrice" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;" placeholder="Total"><input type="date" name="DepartureDate[]" id="DepartureDate_' +
                i + '" class="form-control"></td>';



            html += '</tr>';
            $('table').append(html);
            $('#ItemID0_' + i).select2();
            $('#SupplierID_' + i).select2();
            i++;




        });


        //STEP 1: on change of item dropdown get the tax percentage
        $(document).ready(function() {
            $('.select2').select2(); // Initialize Select2 if not already initialized

            $(document).on('change', '.item-select', function() {
                let selectedValue = $(this).val();
                let taxPercentage = $(this).find(':selected').data('tax'); // Get the data-tax attribute
                id_arr = $(this).attr('id');

                id = id_arr.split("_");
                $('#TaxPer_' + id[1]).val(taxPercentage);


                console.log("Selected Value:", selectedValue);
                console.log("Tax Percentage:", taxPercentage);
            });
        });



        //to check all checkboxes
        $(document).on('change', '#check_all', function() {
            $('input[class=case]:checkbox').prop("checked", $(this).is(':checked'));
        });

        //deletes the selected table rows
        $(".delete").on('click', function() {
            $('.case:checkbox:checked').parents("tr").remove();
            $('#check_all').prop("checked", false);
            calculateTotal();
            updateBankCharges();
        });


        function calculateTaxExclusive(taxable, taxPercentage) {
            let taxAmount = 0;
            if(taxPercentage > 0){
                taxAmount = taxable * (taxPercentage/100);
            }else{
                taxAmount = 0;
            }
            return taxAmount.toFixed(2);
        }


          //price change
        $(document).on('blur change', '.row-calculation', function() {



            InvoiceTypeID = $("#InvoiceTypeID option:selected").val();

            id_arr = $(this).attr('id');

            id = id_arr.split("_");


            if (InvoiceTypeID == 1) {

                Fare = $('#Fare_' + id[1]).val();
                TaxPer = $('#TaxPer_' + id[1]).val();
                Service = $('#Service_' + id[1]).val();

                TaxPercentage = parseFloat($('#ItemID0_' + id[1]).find('option:selected').data('tax')) || 0;
                $('#TaxPer_' + id[1]).val(TaxPercentage);

                let Taxable = calculateTaxExclusive(Service, TaxPercentage);

                $('#Taxable_' + id[1]).val(Taxable);

                Total = parseFloat(Fare) + parseFloat(Service) + parseFloat(Taxable);
                $('#total_' + id[1]).val(Total.toFixed(2));


                Discount = $('#discount_' + id[1]).val();


            }


            if ($('#Fare_' + id[1]).val() == "") {
                Fare = 0;
            }

            if ($('#discount_' + id[1]).val() == "") {
                Discount = 0;
            }




            // if($('#Service_'+id[1]).val() == "")
            // {
            //     Service=0;
            // }

            // InvoiceTypeID = $('#InvoiceTypeID').val();

            if ($('#OPVAT_' + id[1]).val() == "") {
                OPVAT = 0;
            }

            if ($('#IPVAT_' + id[1]).val() == "") {
                IPVAT = 0;
            }

            // console.log("invoice:"+InvoiceTypeID);
            // console.log(Fare);
            // console.log(Service);
            // console.log(total);

            if (InvoiceTypeID == 2) {

                console.log("invoice if:" + InvoiceTypeID);

                Discount = $('#discount_' + id[1]).val();

                if (Discount != "") {

                    Fare = $('#Fare_' + id[1]).val();
                    Service = $('#Service_' + id[1]).val();
                    Taxable = $('#Taxable_' + id[1]).val();
                    Discount = $('#discount_' + id[1]).val();
                    
                    Total = parseFloat(Fare) + parseFloat(Service) + parseFloat(Taxable) - parseFloat(Discount);
                    $('#total_' + id[1]).val(Total.toFixed(2));

                  
                }

            }




            calculateTotal();


        });

       


        $(document).on('change keyup blur', '#tax', function() {
            calculateTotal();
        });




        //total price calculation 
        function calculateTotal() {
            subTotal = 0;
            total = 0;
            $('.totalLinePrice').each(function() {
                if ($(this).val() != '') subTotal += parseFloat($(this).val());
            });
            $('#subTotal').val(subTotal.toFixed(2));
            tax = $('#tax').val();
            if (tax != '' && typeof(tax) != "undefined") {
                Taxable = subTotal * (parseFloat(tax) / 100);
                $('#Taxable').val(Taxable.toFixed(2));
                total = subTotal + Taxable;
            } else {
                $('#Taxable').val(0);
                total = subTotal;
            }
            $('#totalAftertax').val(total.toFixed(2));
            $('#GrandTotal').val(total.toFixed(2));
            calculateAmountDue();
        }

        $(document).on('change keyup blur', '#amountPaid', function() {
            calculateAmountDue();
        });

        //due amount calculation
        function calculateAmountDue() {
            amountPaid = $('#amountPaid').val();
            total = $('#totalAftertax').val();
            if (amountPaid != '' && typeof(amountPaid) != "undefined") {
                amountDue = parseFloat(total) - parseFloat(amountPaid);
                $('.amountDue').val(amountDue.toFixed(2));
            } else {
                total = parseFloat(total).toFixed(2);
                $('.amountDue').val(total);
            }
        }

        //It restrict the non-numbers
        var specialKeys = new Array();
        specialKeys.push(8, 46); //Backspace
        function IsNumeric(e) {
            var keyCode = e.which ? e.which : e.keyCode;
            console.log(keyCode);
            var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
            return ret;
        }

        //datepicker
        $(function() {
            $.fn.datepicker.defaults.format = "dd-mm-yyyy";
            $('#invoiceDate').datepicker({
                startDate: '-3d',
                autoclose: true,
                clearBtn: true,
                todayHighlight: true
            });
        });



</script>

 <script>
        // Get the elements
        const percentageInput = document.querySelector('input[name="Percentage"]');
        const percentageValueInput = document.getElementById('PercentageValue');
        const totalAfterTaxInput = document.getElementById('totalAftertax');
        const grandTotalInput = document.getElementById('GrandTotal');
        const amountDueInput = document.getElementById('amountDue');

        // Function to calculate and update values
        function updateBankCharges(source = "") {
            const total = parseFloat(totalAfterTaxInput.value) || 0;
            let percentage = parseFloat(percentageInput.value) || 0;
            let bankCharge = parseFloat(percentageValueInput.value) || 0;

            if (source === "percentage") {
                // User entered % → calculate {{ env('APP_CURRENCY') }}
                bankCharge = (percentage / 100) * total;
                percentageValueInput.value = bankCharge.toFixed(2);
            } else if (source === "value") {
                // User entered {{ env('APP_CURRENCY') }} → calculate %
                if (total > 0) {
                    percentage = (bankCharge / total) * 100;
                    percentageInput.value = percentage.toFixed(2);
                }
            } else {
                // Default: recalc from %
                bankCharge = (percentage / 100) * total;
                percentageValueInput.value = bankCharge.toFixed(2);
            }

            // Update Grand Total
            const grandTotal = total + bankCharge;
            grandTotalInput.value = grandTotal.toFixed(2);

            // Update Amount Due
            amountDueInput.value = grandTotal.toFixed(2);
        }

        // Trigger calculation on input changes
        percentageInput.addEventListener('input', () => updateBankCharges("percentage"));
        percentageValueInput.addEventListener('input', () => updateBankCharges("value"));
        totalAfterTaxInput.addEventListener('input', () => updateBankCharges("total"));

        // Run on load
        window.addEventListener('DOMContentLoaded', () => updateBankCharges("init"));
    </script>
<script>
    function ajax_balance(SupplierID) {

            // alert($("#csrf").val());

            $('#result').prepend('')
            $('#result').prepend('<img id="theImg" src="{{ asset('assets/images/ajax.gif') }}" />')

            var SupplierID = SupplierID;

            // alert(SupplierID);
            if (SupplierID != "") {
                /*  $("#butsave").attr("disabled", "disabled"); */
                // alert(SupplierID);
                $.ajax({
                    url: "{{ URL('/Ajax_Balance') }}",
                    type: "POST",
                    data: {
                        _token: $("#csrf").val(),
                        SupplierID: SupplierID,

                    },
                    cache: false,
                    success: function(data) {



                        $('#result').html(data);



                    }
                });
            } else {
                alert('Please Select Branch');
            }




        }
</script>