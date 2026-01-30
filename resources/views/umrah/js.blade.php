<script>


    $(document).ready(function() {
        $('.select2').select2(); // Initialize Select2 if not already initialized

        $(document).on('change', '.item-select', function() {
            let selectedValue = $(this).val();
            let taxPercentage = $(this).find(':selected').data('tax'); // Get the data-tax attribute
            id_arr = $(this).attr('id');

            id = id_arr.split("_");
            // $('#VAT_' + id[1]).val(taxPercentage);
            $('#TaxPer_' + id[1]).val(taxPercentage);


            console.log("Selected Value:", selectedValue);
            console.log("Tax Percentage:", taxPercentage);
        });
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
        $(document).on('change', '.row-calculation', function() {


            id_arr = $(this).attr('id');

            id = id_arr.split("_");



            Fare = $('#Fare_' + id[1]).val();
            TaxPer = $('#TaxPer_' + id[1]).val();
            Service = $('#Service_' + id[1]).val();
            Paid = $('#Paid_' + id[1]).val();


            TaxPercentage = parseFloat($('#ItemID_' + id[1]).find('option:selected').data('tax')) || 0;
            $('#TaxPer_' + id[1]).val(TaxPercentage);
            
            let Taxable = calculateTaxExclusive(Service, TaxPercentage);
            $('#Taxable_' + id[1]).val(Taxable);

            Total = parseFloat(Fare) + parseFloat(Service) + parseFloat(Taxable);
            $('#Total_' + id[1]).val(Total.toFixed(2));

            PaymentInBus = parseFloat(Total) - parseFloat(Paid);
            $('#PaymentInBus_' + id[1]).val(PaymentInBus.toFixed(2));
            



            // Vat = $('#VAT_' + id[1]).val();




            // Paid = $('#Paid_' + id[1]).val();
            // Total = $('#Total_' + id[1]).val();


            // Service = parseFloat(Total) - parseFloat(Fare);
            // // $('#Service_'+id[1]).val(Service);

            // PaymentInBus = parseFloat(Total) - parseFloat(Paid);


            // $('#PaymentInBus_' + id[1]).val(PaymentInBus);



            // if ($('#Fare_' + id[1]).val() == "") {
            //     Fare = 0;
            // }

            // if ($('#discount_' + id[1]).val() == "") {
            //     Discount = 0;
            // }


            // TaxAmount = ((5 * parseFloat(Service)) / (100 + 5)).toFixed(2);
            // $('#VAT_' + id[1]).val(TaxAmount);

            // Service = parseFloat(Service) - parseFloat(TaxAmount);
            // $('#Service_' + id[1]).val(Service);


            grandtotal();



        });

        function grandtotal() {
            let grandTotal = 0;
            let serviceTotal = 0;
            let vatTotal = 0;

            $('.totalLinePrice').each(function () {
                const value = parseFloat($(this).val());
                if (!isNaN(value)) grandTotal += value;
            });

            $('#totalAftertax').val(grandTotal.toFixed(2));
            $('#GrandTotal').val(grandTotal.toFixed(2));

            $('.service').each(function () {
                const value = parseFloat($(this).val());
                if (!isNaN(value)) serviceTotal += value;
            });

            $('.vat').each(function () {
                const value = parseFloat($(this).val());
                if (!isNaN(value)) vatTotal += value;
            });

            const paid = serviceTotal + vatTotal;
            $('#Paid').val(paid.toFixed(2));
        }
</script>
<script>
    var i = $('table tr').length;

    $(".addmore").on('click', function() {
        var html = `    

        <thead>
            <tr style="height: 40px;" id="t_${i}">
            <th width="2%" colspan="8" /><hr></th>
            
            </tr>
        </thead>



            <thead>
            <tr class="bg-light borde-1 border-light " style="height: 40px;" id="ttttt_${i}">
                <th width="2%" class="p-1"></th>
                <th width="12%">Item</th>
                <th width="10%">PAX Name</th>
                <th width="5%">Pick Point</th>
                <th width="5%">Package</th>
                <th width="5%">Cost</th>
                <th width="5%">selling price</th>
                
                <th width="7%">Amount Paid</th>
            </tr>
            </thead>
            <tr class="p-3" style="vertical-align: top;" id="tt_${i}">
            <td class="p-1 bg-light borde-1 border-light"><input class="case" type="checkbox" id="${i}" /></td>
            <td>

                <select name="ItemID[]" id="ItemID_${i}" class="form-select form-control-sm  select2 item-select">
                    @foreach ($items as $key => $value)
                        <option value="{{ $value->ItemID }}"
                            data-tax="{{ $value->Percentage }}">
                            {{ $value->ItemCode }}-{{ $value->ItemName }}-{{ $value->Percentage }}
                        </option>
                    @endforeach
                </select>
                
            </td>
            <td>
                <input type="text" name="PaxName[]" id="PaxName_${i}" class=" form-control text-uppercase "
                autocomplete="off" placeholder="PaxName" >
                
            </td>
            <td>
                <select name="PickPoint[]" id="PickPoint_1" class="form-select">
                    <option value="Sharja">Sharja</option>
                    <option value="Dubai">Dubai</option>
                    <option value="Abu Dahbi">Abu Dahbi</option>
                    <option value="Jebel Ali">Jebel Ali</option>
                    <option value="Al Ain">Al Ain</option>
                    <option value="Ras Al-Khaimah">Ras Al-Khaimah</option>
                    
                </select>
                
            </td>
            <td>
                    <select name="VisaType[]" id="VisaType_${i}" class="form-select">
                    <option value="Multi">Multi</option>
                    <option value="Umrah">Umrah</option>
                </select>
                
            </td>
            <td>
                <input type="number" name="Fare[]" id="Fare_${i}" class=" form-control row-calculation"                                 autocomplete="off"  step="0.01" placeholder="Fare" value="0">

            
            <td>
                <input readonly type="number" name="ItemTotal[]" id="Total_${i}" class=" form-control"
                autocomplete="off"  step="0.01" placeholder="Total" value="0">

                
                    
            </td>
        
            <td>
                <input type="number" name="Paid[]" id="Paid_${i}" class=" form-control row-calculation totalLinePrice"
                autocomplete="off"  step="0.01" placeholder="Paid" value="0">
            </td>
            </tr>


            <tr class="bg-light borde-1 border-light " style="height: 40px; font-weight: bolder;" id="ttt_${i}">
            <td></td>
            <td>Supplier</td>
            <td><span>Pax Contact</span><span style="float: right; padding-right: 55px;">Pax Passport</span></td>
            <td>Room Type</td>
            <td>VAT</td>
            <td>Net Profit</td>
            <td>Payment in Bus</td>
            <td>Departure Date</td>
            </tr>

        <tr class="bg-light borde-1 border-light " style="height: 40px; font-weight: bolder;" id="tttt_${i}">

            <td></td>
            <td> <select name="SupplierID[]" id="SupplierID_${i}" class="form-select select2"  
                onchange="ajax_balance(this.value);">
                <option value="">Select Supplier</option>
                @foreach ($supplier as $key => $value)
                <option value="{{ $value->SupplierID }}">{{ $value->SupplierName }}</option>
                @endforeach
                </select></td>
            <td>    <div class="input-group">
                            

                            <input type="text" class="form-control"   id="Contact_${i}" name="Contact[]" placeholder="Contact">

                                

                            
                            <input type="text" class="form-control"   id="Passport_${i}" name="Passport[]" placeholder="Passport">

                        </div></td>
            <td>   <select name="RoomType[]" id="RoomType_${i}" class="form-select">
                    <option value="Quad">Quad</option>
                    <option value="Triple">Triple</option>
                    <option value="Double">Double</option>
                    <option value="Sharing">Sharing</option>
                </select></td>
            <td> 
                 <input readonly type="number" name="TaxPer[]" id="TaxPer_${i}"
                class=" form-control vat" autocomplete="off"
                step="0.01" placeholder="VAT" readonly=""
                value="0">
                
                <input type="number" name="Taxable[]" id="Taxable_${i}" class=" form-control vat"  
                autocomplete="off"  step="0.01" placeholder="VAT" readonly="" value="0"></td>
            <td> 
                <input type="number" name="Service[]" id="Service_${i}" class=" form-control service row-calculation"
                autocomplete="off"  step="0.01" placeholder="Service" value="0">
                </td>
            <td> <input readonly type="number" name="PaymentInBus[]" id="PaymentInBus_${i}"  
                class=" form-control" autocomplete="off"
                step="0.01" placeholder="Payment in bus" value="0"></td>
            <td><input type="Date" name="DepartureDate[]" id="DepartureDate_${i}" value="" class="form-control"></td>
            </tr>


            <tr class="bg-light borde-1 border-light " style="height: 40px; font-weight: bolder;" id="file_${i}">
            <td></td>
            <td>


                <div class="mt-2 mb-3">
            <label for="PassportFile_${i}" class="fw-bolder">Passport</label>
            <input type="file" class="form-control"  name="PassportFile[]" id="PassportFile_${i}">
            </div>



            </td>
            

            <td>

                <div class="mt-2 mb-3">
            <label for="PassportFile_${i}" class="fw-bolder">Emirate ID Front</label>
            <input type="file" class="form-control"  name="EmirateIDFileFront[]" id="EmirateIDFileFront_${i}">
            </div>
                


            </td>
                <td colspan="2">
                
                <div class="mt-2 mb-3">
            <label for="PassportFile_${i}" class="fw-bolder">Emirate ID Back</label>
            <input type="file" class="form-control"  name="EmirateIDFileBack[]" id="EmirateIDFileBack_${i}">
            </div>

            </td>
            <td colspan="2">
                
                <div class="mt-2 mb-3">
            <label for="PassportFile_${i}" class="fw-bolder">Picture</label>
            <input type="file" class="form-control"  name="PictureFile[]" id="PictureFile_${i}">
            </div>

            </td>
            
                <td>
                <div class="mt-2 mb-3">
            <label for="PassportFile_1" class="fw-bolder">Nationality</label>
                <select name="Nationality[]" id="Nationality_${i}" class="form-select select2">
                <option value="">choose</option>
                @foreach ($nationality as $row)
                    <option value="{{ $row->country_enNationality }}">{{ $row->country_enNationality }}</option>
                @endforeach
                </select>
                </div>
            </td>
            
            
                <td style="vertical-align: top;" class="d-none">
                <label for="" class="fw-bolder mt-2">Deduction Charges</label>
                <input type="number" name="deduction[]" id="deduction_${i}" class="form-control" onblur="this.readonly=true;">

            </td> 
            </tr>`;
        $('table').append(html);
        $('#ItemID_' + i).select2();
        $('#SupplierID_' + i).select2();
        $('#Nationality_' + i).select2();
        i++;
    });

</script>