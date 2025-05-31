<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dairy Muneem DMI Calculation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table {
            width: 750px;
            border-collapse: collapse;
            margin: 50px auto;
        }
        th {
            background: #3498db;
            color: white;
            font-weight: bold;
        }
        td, th {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
            font-size: 18px;
        }
        .labels tr td {
            background-color: #20b9aa;
            font-weight: bold;
            color: #fff;
        }
        .two, .info {
            color: #20b9aa;
        }
        .success {
            background-color: #198754;
            color: white;
        }
        .success1 {
            color: #198754;
        }
        .primary {
            background-color: #0d6efd;
            color: white;
        }
        .primary1 {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <table>
                <tbody>
                    <tr>
                        <td colspan="3" style="border-right:none">
                            <img src="{{ asset('assets/logo2.png') }}" alt="Logo" style="max-width: 150px;">
                            <h5>Agristar Animal Solution Private Limited</h5>
                            <h6>Dream City, Suratgarh, Ganganagar, Rajasthan, 335804</h6>
                        </td>
                        <td colspan="2" style="border-left:none">
                            <h6>Contact:</h6>
                            <p style="font-size:15px">Call & Whatsapp- 7891029090</p>
                            <h6>Email:</h6>
                            <p style="font-size:15px">info@dairymuneem.in, dairymuneem@gmail.com</p>
                            <h6>Website:</h6>
                            <p style="font-size:15px">www.dairymuneem.com</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-center">
                            <h3><span class="two">D</span>ry <span class="two">M</span>atter <span class="two">I</span>ntake <span class="two">C</span>alculation</h3>
                        </td>
                    </tr>
                </tbody>

                {{-- Input Parameters Section --}}
                <tbody class="primary">
                    <tr>
                        <td colspan="5">Input Parameters</td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <td colspan="2">Lactation</td>
                        <td colspan="3" class="info">{{ $input['lactation'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Feed Percentage (%)</td>
                        <td colspan="3" class="info">{{ $input['feed_percentage'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Milk Yield (kg)</td>
                        <td colspan="3" class="info">{{ $input['milk_yield'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Weight (kg)</td>
                        <td colspan="3" class="info">{{ $input['weight'] }}</td>
                    </tr>
                </tbody>

                {{-- Calculated Results Section --}}
                <tbody class="primary">
                    <tr>
                        <td colspan="5">Calculated Results</td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <td colspan="2">Dry Matter Intake (kg)</td>
                        <td colspan="3" class="success1">{{ $result['dry_matter_intake'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Feed (kg)</td>
                        <td colspan="3" class="success1">{{ $result['feed'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Fodder (kg)</td>
                        <td colspan="3" class="success1">{{ $result['fodder'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Feed Quantity (kg)</td>
                        <td colspan="3" class="success1">{{ $result['feed_qty'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Green Fodder (kg)</td>
                        <td colspan="3" class="success1">{{ $result['green_fodder'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Maize (kg)</td>
                        <td colspan="3" class="success1">{{ $result['maize'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Barseem (kg)</td>
                        <td colspan="3" class="success1">{{ $result['barseem'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Dry Fodder (kg)</td>
                        <td colspan="3" class="success1">{{ $result['dry_fodder'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Hary (kg)</td>
                        <td colspan="3" class="success1">{{ $result['hary'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Silage DM (kg)</td>
                        <td colspan="3" class="success1">{{ $result['silage_dm'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Silage (kg)</td>
                        <td colspan="3" class="success1">{{ $result['silage'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>