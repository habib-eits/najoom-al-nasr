<div class="card shadow-sm border-0">
    <div class="card-body py-3">
        <!-- Main Filter Row -->
        <div class="row g-3 align-items-end">

            <!-- Date Range Selector -->
            <div class="col-lg-3 col-md-6 col-sm-12">
                <label class="form-label fw-semibold small text-muted">Date Range</label>
                <select id="dateRangeSelector" name="dateRangeSelector" class="form-select form-select-sm">
                    <option value="Today" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Today' ? 'selected' : '' }}>Today</option>
                    <option value="Yesterday" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="This Week" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'This Week' ? 'selected' : '' }}>This Week</option>
                    <option value="Current Month to Date" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Current Month to Date' ? 'selected' : '' }}>Current Month to Date</option>
                    <option value="This Month" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'This Month' ? 'selected' : '' }}>This Month</option>
                    <option value="This Quarter" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'This Quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="This Year" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'This Year' ? 'selected' : '' }}>This Year</option>
                    <option value="Year to Date" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Year to Date' ? 'selected' : '' }}>Year to Date</option>
                    <option value="Previous Week" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Previous Week' ? 'selected' : '' }}>Previous Week</option>
                    <option value="Previous Month" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Previous Month' ? 'selected' : '' }}>Previous Month</option>
                    <option value="Previous Quarter" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Previous Quarter' ? 'selected' : '' }}>Previous Quarter</option>
                    <option value="Previous Year" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Previous Year' ? 'selected' : '' }}>Previous Year</option>
                    <option value="Custom Range" {{ old('dateRangeSelector', $dateRangeSelector ?? '') == 'Custom Range' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <!-- Custom From Date -->
            <div class="col-lg-2 col-md-3 col-sm-6 custom-range">
                <label class="form-label fw-semibold small text-muted">From</label>
                <input type="date" name="fromDate" id="fromDate" class="form-control form-control-sm" value="{{ old('fromDate', $fromDate) }}">
            </div>

            <!-- Custom To Date -->
            <div class="col-lg-2 col-md-3 col-sm-6 custom-range">
                <label class="form-label fw-semibold small text-muted">To</label>
                <input type="date" name="toDate" id="toDate" class="form-control form-control-sm" value="{{ old('toDate', $toDate) }}">
            </div>

            <!-- Compare By -->
            <div class="col-lg-2 col-md-4 col-sm-6">
                <label class="form-label fw-semibold small text-muted">Compare</label>
                <select name="comparedType" class="form-select form-select-sm">
                    <option value="period" {{ old('comparedType', $comparedType) == 'period' ? 'selected' : '' }}>Previous Period</option>
                    <option value="year" {{ old('comparedType', $comparedType) == 'year' ? 'selected' : '' }}>Previous Year</option>
                </select>
            </div>

            <!-- Compare Count -->
            <div class="col-lg-1 col-md-2 col-sm-3">
                <label class="form-label fw-semibold small text-muted">×</label>
                <select name="comparedCount" class="form-select form-select-sm">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ old('comparedCount', $comparedCount) == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <!-- Apply Button -->
            <div class="col-lg-2 col-md-4 col-sm-12 text-md-end">
                <button type="submit" class="btn btn-primary btn-sm w-100 w-md-auto px-4">
                    <i class="bi bi-funnel me-1"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Active Date Range Display -->
<div class="bg-light rounded-3 px-4 py-3 mb-1 d-flex flex-wrap align-items-center justify-content-between gap-3">

    <!-- Left: Selected Period -->
    <div class="d-flex align-items-center">
        <span class="fw-semibold text-secondary me-2">Selected Period:</span>
        <span class="badge bg-primary fs-6 px-3 py-2">
            {{ date('d M Y', strtotime($fromDate)) }} → {{ date('d M Y', strtotime($toDate)) }}
        </span>
    </div>

    @if($comparedType ?? false)
        <!-- Right: Comparing To -->
        <div class="d-flex align-items-center ms-auto">
            <span class="fw-semibold text-secondary me-2">Comparing to:</span>
            <span class="badge bg-secondary fs-6 px-3 py-2">
                {{ ucfirst(str_replace('_', ' ', $comparedType)) }}
                @if(isset($comparedCount) && $comparedCount > 1)
                    ×{{ $comparedCount }}
                @endif
            </span>
        </div>
    @endif

</div>



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(function () {

  function toggleCustomRange() {
        if ($('#dateRangeSelector').val() === 'Custom Range') {
            $('.custom-range').removeClass('d-none');

            let today = new Date();
            let start = new Date(today.getFullYear(), today.getMonth(), 1); // first day of month

            // Format dates as YYYY-MM-DD
            let formatDate = d => d.toISOString().split('T')[0];

            $('#fromDate').val(formatDate(start));
            $('#toDate').val(formatDate(today));
        } else {
            $('.custom-range').addClass('d-none');
        }
    }

    // Run on page load and on dropdown change
    $(document).ready(toggleCustomRange);
    $('#dateRangeSelector').change(toggleCustomRange);
});
</script>

</script>

<script>

    $(document).ready(function () {
        // Handle the date range selection
        $('#dateRangeSelector').on('change', function () {
            let range = $(this).val();
            let fromDate = null;
            let toDate = null;

            switch (range) {
                case "Today":
                    fromDate = moment().format("YYYY-MM-DD");
                    toDate = moment().format("YYYY-MM-DD");
                    break;
                case "Yesterday":
                    fromDate = moment().subtract(1, "days").format("YYYY-MM-DD");
                    toDate = fromDate;
                    break;
                case "This Week":
                    fromDate = moment().startOf("week").format("YYYY-MM-DD");
                    toDate = moment().endOf("week").format("YYYY-MM-DD");
                    break;
                case "Current Month to Date":
                    fromDate = moment().startOf("month").format("YYYY-MM-DD");
                    toDate = moment().format("YYYY-MM-DD");
                    break;
                case "This Month":
                    fromDate = moment().startOf("month").format("YYYY-MM-DD");
                    toDate = moment().endOf("month").format("YYYY-MM-DD");
                    break;
                case "This Quarter":
                    fromDate = moment().startOf("quarter").format("YYYY-MM-DD");
                    toDate = moment().endOf("quarter").format("YYYY-MM-DD");
                    break;
                case "This Year":
                    fromDate = moment().startOf("year").format("YYYY-MM-DD");
                    toDate = moment().endOf("year").format("YYYY-MM-DD");
                    break;
                case "Year to Date":
                    fromDate = moment().startOf("year").format("YYYY-MM-DD");
                    toDate = moment().format("YYYY-MM-DD");
                    break;
                case "Previous Week":
                    fromDate = moment().subtract(1, "week").startOf("week").format("YYYY-MM-DD");
                    toDate = moment().subtract(1, "week").endOf("week").format("YYYY-MM-DD");
                    break;
                case "Previous Month":
                    fromDate = moment().subtract(1, "month").startOf("month").format("YYYY-MM-DD");
                    toDate = moment().subtract(1, "month").endOf("month").format("YYYY-MM-DD");
                    break;
                case "Previous Quarter":
                    fromDate = moment().subtract(1, "quarter").startOf("quarter").format("YYYY-MM-DD");
                    toDate = moment().subtract(1, "quarter").endOf("quarter").format("YYYY-MM-DD");
                    break;
                case "Previous Year":
                    fromDate = moment().subtract(1, "year").startOf("year").format("YYYY-MM-DD");
                    toDate = moment().subtract(1, "year").endOf("year").format("YYYY-MM-DD");
                    break;
                case "Custom Range":
                    fromDate = ""; // Let user manually set dates
                    toDate = "";
                    break;
            }

            // Populate the date fields
            $('#fromDate').val(fromDate);
            $('#toDate').val(toDate);
        });
    });
</script>