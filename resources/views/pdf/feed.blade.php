<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dairy Muneem Feed Calculation</title>
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
                            <h3><span class="two">F</span>eed <span class="two">C</span>alculation</h3>
                        </td>
                    </tr>
                </tbody>

                {{-- Ingredient Section --}}
                <tbody class="primary">
                    <tr>
                        <td colspan="2">Ingredient</td>
                        <td>Cost</td>
                        <td>Ratio</td>
                    </tr>
                </tbody>
                <tbody>
                    @php $ration = 0; @endphp

                    @foreach (['ProteinData', 'EnergyData', 'ProductData', 'MedicineData'] as $type)
                        @foreach(json_decode($result[$type], true) as $item)
                            @if(!empty($item[3]))
                                <tr>
                                    <td colspan="2">{{ $item[1] }}</td>
                                    <td class="info">{{ $item[2] }}</td>
                                    <td>{{ $item[3] }}</td>
                                </tr>
                                @php $ration += $item[3]; @endphp
                            @endif
                        @endforeach
                    @endforeach

                    <tr>
                        <td colspan="2"></td>
                        <td><strong>Total</strong></td>
                        <td><strong>{{ $ration }}</strong></td>
                    </tr>
                </tbody>

                {{-- Value Section --}}
                <tbody class="labels">
                    <tr>
                        <td colspan="2">Value</td>
                        <td>Fresh</td>
                        <td>DMB</td>
                    </tr>
                </tbody>
                <tbody>
                    @foreach([
                        'CP', 'FAT', 'FIBER', 'TDN', 'ENERGY', 'CA', 'P',
                        'RUDP', 'ADF', 'NDF', 'NEL', 'ENDF'
                    ] as $key)
                        <tr>
                            <td colspan="2">{{ $key }}</td>
                            <td class="info">{{ $result['fresh'][$key] ?? '-' }}</td>
                            <td class="success1">{{ $result['dmb'][$key] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- Raw Cost Section --}}
                <tbody class="primary">
                    <tr>
                        <td colspan="5">Raw Cost</td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <td>TON</td>
                        <td class="primary1">{{ $result['row_ton'] ?? '0' }}</td>
                        <td>Qtl</td>
                        <td class="primary1">{{ $result['row_qtl'] ?? '0' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
