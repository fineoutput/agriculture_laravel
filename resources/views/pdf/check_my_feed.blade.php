<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dairy Muneem Feed Protein Energy Ratio</title>
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
        .two {
            color: #20b9aa;
        }
        .three {
            color: #3c5772;
        }
        .four {
            color: #507186;
        }
        .info {
            background-color: #0dcaf0;
        }
        .info2 {
            color: #3498db;
        }
        .info3 {
            color: #009252; /* Default to green */
        }
        .info3.red {
            color: red;
        }
        .success {
            color: #198754;
        }
        .primary {
            color: #0d6efd;
        }
        .warning {
            color: #ffc107;
        }
        .ht {
            margin-left: 30px;
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
                            <p><b>Date</b><span class="ht">{{ now()->format('Y-m-d') }}</span></p>
                            <p><b>Farmer</b><span class="ht">{{ $farmername }}</span></p>
                            <p><b>Cow</b><span class="ht">MILKING</span></p>
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
                            <h3><span class="two">F</span><span class="three">eed </span><span class="two">P</span><span class="three">rotein </span><span class="two">E</span><span class="three">nergy </span><span class="two">R</span><span class="four">atio</span></h3>
                        </td>
                    </tr>
                </tbody>
                <tbody class="labels">
                    <tr>
                        <td colspan="2" style="background-color: #3498db;">
                            <label>Cow Characteristics</label>
                        </td>
                        <td colspan="3" style="background-color: #3498db;">
                            <label>Ration Nutritional Analysis</label>
                        </td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <td>Live Weight (kg)</td>
                        <td class="info2">{{ $input['live_weight'] ?? '-' }}</td>
                        <td></td>
                        <td>Needs</td>
                        <td>Intake</td>
                    </tr>
                    <tr>
                        <td>Pregnancy (mth)</td>
                        <td class="info2">{{ $input['pregnancy'] ?? '-' }}</td>
                        <td>Metabolisable Energy (MJ/d)</td>
                        <td class="info3 {{ $result['metabolisable_energy_needs'] && ($result['metabolisable_energy_needs'] < 173 || $result['metabolisable_energy_needs'] > 193) ? 'red' : '' }}">{{ $result['metabolisable_energy_needs'] ?? '-' }}</td>
                        <td class="info3 {{ $result['metabolisable_energy_intake'] && ($result['metabolisable_energy_intake'] < 173 || $result['metabolisable_energy_intake'] > 193) ? 'red' : '' }}">{{ $result['metabolisable_energy_intake'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Milk Volume (kg)</td>
                        <td class="info2">{{ $input['milk_yield_volume'] ?? '-' }}</td>
                        <td>Crude Protein (kg/d)</td>
                        <td class="info3">{{ $result['crude_protein_needs'] ?? '-' }}</td>
                        <td class="info3">{{ $result['crude_protein_intake'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Milk Fat (%)</td>
                        <td class="info2">{{ $input['milk_yield_fat'] ?? '-' }}</td>
                        <td>Calcium (g/d)</td>
                        <td class="info3">{{ $result['calcium_needs'] ?? '-' }}</td>
                        <td class="info3">{{ $result['calcium_intake'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Milk Protein (%)</td>
                        <td class="info2">{{ $input['milk_yield_protein'] ?? '-' }}</td>
                        <td>Phosphorus (g/d)</td>
                        <td class="info3">{{ $result['phosphorus_needs'] ?? '-' }}</td>
                        <td class="info3">{{ $result['phosphorus_intake'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Live Weight Gain/Loss (kg/d)</td>
                        <td class="info2">{{ $input['live_weight_gain'] ?? '-' }}</td>
                        <td>NDF</td>
                        <td class="info3">{{ $result['ndf_needs'] ?? '-' }}</td>
                        <td class="info3">{{ $result['ndf_intake'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Stage of Lactation</td>
                        <td class="info2">{{ $input['lactation'] ?? '-' }}</td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td>Max</td>
                        <td>Intake</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td class="info2">Dry Matter (kg/d)</td>
                        <td class="info2">{{ $result['dry_matter_max'] ?? '-' }}</td>
                        <td class="info2">{{ $result['dry_matter_intake'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td class="info2">Concentrate</td>
                        <td class="info2">{{ $result['concentrate_max'] ?? '-' }}</td>
                        <td class="info2">{{ $result['concentrate_intake'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th colspan="5">Milk Income Less Feed Cost (MIFC)</th>
                    </tr>
                    <tr>
                        <td colspan="2">Milk Return (Rs./kg)</td>
                        <td class="info2" colspan="3">{{ $result['milk_return_kg'] ? '₹' . number_format($result['milk_return_kg'], 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Milk Return (Rs./d)</td>
                        <td class="info2" colspan="3">{{ $result['milk_return_day'] ? '₹' . number_format($result['milk_return_day'], 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Feed Cost (Rs./d)</td>
                        <td class="info2" colspan="3">{{ $result['feed_cost_day'] ? '₹' . number_format($result['feed_cost_day'], 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">MIFC ROI (Rs./d)</td>
                        <td class="info2" colspan="3">{{ $result['mifc_roi_day'] ? '₹' . number_format($result['mifc_roi_day'], 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <th colspan="5">Composition of the Ration</th>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Ration Ingredients</b></td>
                        <td colspan="3"><b>Fresh Feed Intake (kg/d)</b></td>
                    </tr>
                   @foreach ($input['material'] as $mat)
    @if (isset($mat['fresh']) && $mat['fresh'] !== 0)
        <tr>
            <td colspan="2">{{ $mat['name'] ?? 'Unknown Ingredient' }}</td>
            <td class="info2" colspan="3">{{ number_format($mat['fresh'], 2) }}</td>
        </tr>
    @endif
@endforeach
<tr>
    <td colspan="2">Total Intake per Head:</td>
    <td class="info2" colspan="3">{{ number_format($result['total_intake'], 2) }}</td>
</tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>