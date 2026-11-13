<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TRIP OPTIMIZER</title>
    <link rel="shotrcut icon" href="{{ public_path('/images/trip-logo-nobg.png')}}" />

<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;

        color: #000;
    }

    .header {
        text-align: center;
        border-bottom: 3px solid #ffffff;

        margin-bottom: 20px;
        background-color: #ffffff;
    }

    .header img {
        height: 35%;
    }

    .header h1 {
        font-size: 24px;
        text-transform: uppercase;
        margin: 0;
        line-height: 45px;
        color: black;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .info-table td {
        padding: 5px 8px;
        vertical-align: top;
        font-size: 14px;
    }

    .info-table .label {
        font-weight: bold;
        text-transform: uppercase;
        width: 25%;
        white-space: nowrap;
    }

    table.data {
        width: 100%;
        border-collapse: collapse;
    }

    table.data th, table.data td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
        font-size: 14px;
    }

    table.data th {
        background-color: #f1f1f1;
        text-transform: uppercase;
        font-weight: bold;
    }

    .total-row td {
        font-weight: bold;
        background-color: #f9f9f9;
    }
    

    .footer-buttons {
        text-align: right;
        margin-top: 20px;
    }

    .btn {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 11px;
        text-transform: uppercase;
        margin-left: 6px;
    }

    .btn-draft {
        border: 1px solid #999;
        background-color: #ddd;
    }

    .btn-review {
        border: none;
        background-color: #007bff;
        color: #fff;
    }

    .container {
        border: 2px solid black;
        margin: 0 auto;
        text-align: center;
    }

    .headers {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 30px;
    }

    .acknowledgment {
        font-size: 14px;
        margin-bottom: 60px;
        color: #333;
    }

    /* The Table Layout for Alignment */
    .signature-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* Forces equal column widths */
        margin-top: 40px;
    }

    .signature-table td {
        text-align: center;
        vertical-align: bottom;
    }

    /* Space between the two columns */
    .spacer {
        width: 10%;
    }

    .sign-box {
        width: 40%;
        text-align: center;
    }

    .line {
        border-top: 1px solid black;
        margin-bottom: 5px;
    }

    .labels {
        font-size: 14px;
        font-weight: normal;
    }

    .recipient-name {
        font-size: 14px;
        margin-bottom: 2px;
        min-height: 18px; /* Keeps spacing if empty */
    }

    @page {
        /* Create 1.5 inch space at the bottom for signatures */
        margin: 20px 30px 120px 30px; 
    }

    .signature-wrapper {
        position: fixed;
        bottom: 50px; /* Adjusted to sit within the margin */
        left: 0;
        right: 0;
        width: 100%;
    }

    .sig-column {
        width: 30%;
        float: left;
        margin: 0 1.5%;
        text-align: center;
    }

    .sig-image {
        display: block;
        margin: 0 auto;
        height: 60px; /* Force a consistent height for signatures */
        width: auto;
    }

    .sig-name {
        margin-top: 5px;
        padding-top: 5px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 14px;
    }

    .sig-line {
        border-top: 1px solid #000;
        margin-top: 5px;
        padding-top: 5px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 11px;
    }
    
    /* Container alignment */
    .badge-container {
        display: inline-flex;
        align-items: center;
        margin: 4px 0;
    }

    /* Redesigned Success Badge */
    .badge-success {
        background-color: #10b981; /* Soft modern green */
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        padding: 0.35em 0.75em;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

</style>
</head>
<body>

<main>
    <table class="info-table">
        <tr>
            <td class="label">
                @if($trip_optimizer->company->id== 1)
                <img src="{{ public_path('/images/bevilogonobg.png')}}" alt="product photo" class="product-img" height="50" width="250">
                @elseif($trip_optimizer->company->id == 2)
                <img src="{{ public_path('/images/bevanobg.png')}}" alt="product photo" class="product-img" height="80" width="120">
                @elseif($trip_optimizer->company->id == 3)
                <img src="{{ public_path('/images/biginobg.png')}}" alt="product photo" class="product-img" height="80" width="150">
                @endif
            </td>
            <td></td>
            <td></td>
            <td>Ref. No.: <b class="label"> {{ $trip_optimizer->control_number }}</b></td>
            <td></td>
        </tr>
    </table>

    <!-- Info Section -->
    <table class="info-table">
        <tr>
            <td class="label">Sales Order/s:</td>
            <td>
                {{ 
                    collect(is_array($trip_optimizer->sales_orders) ? $trip_optimizer->sales_orders : explode(',', $trip_optimizer->sales_orders))
                        ->map(fn($so) => ltrim(trim($so), '0'))
                        ->implode(', ') 
                }}
            </td>
            <td class="label">Ship Date:</td>
            <td>{{ date('F d, Y', strtotime($trip_optimizer->ship_date ?? '')) }}</td>
        </tr>
        <tr>
            <td class="label"></td>
            <td></td>
            @if(!empty($trip_optimizer->date_submitted))
            <td class="label">Date Submitted:</td>
            <td>{{ date('F d, Y', strtotime($trip_optimizer->date_submitted ?? '')) }}</td>
            @endif
        </tr>

    </table>

    <table class="data">
        <thead>
            <tr>
                <th>No.</th>
                <th >Release Item</th>
                <th>UOM</th>
                <th>QTY</th>
                <th style="width: 50px;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
    <p></p>
 
    </div>

</main>

</body>
</html>
