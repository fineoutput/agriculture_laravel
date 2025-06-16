<!DOCTYPE html>
<html lang="en">
<head>
    <title>25 Cows Excel</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
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
        .C_label {
            background-color: #2cc16a;
            font-weight: bold;
            color: #fff;
        }
        .label tr td label {
            display: block;
        }
        [data-toggle="toggle"] {
            display: none;
        }
        .tr1 {
            color: #20b9aa;
        }
        .secondary {
            background-color: #6c757d;
            color: white;
        }
        .secondary1 {
            color: #6c757d;
        }
        .primary {
            background-color: #0d6efd;
            color: white;
        }
        .primary1 {
            color: #0d6efd;
        }
        .success {
            background-color: #198754;
            color: white;
        }
        .success1 {
            color: #198754;
        }
        .warning {
            background-color: #ffc107;
            color: white;
        }
        .warning1 {
            color: #ffc107;
        }
        .info {
            background-color: #0dcaf0;
            color: white;
        }
        .info1 {
            color: #0dcaf0;
        }
        .dark {
            background-color: #212529;
            color: white;
        }
        .dark1 {
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <table class="table table-bordered">
                <table>
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td colspan="6" style="border-right:none">
                                <h5>Agristar Animal Solution Private Limited</h5>
                                <h6>Dream City, Suratgarh, Ganganagar, Rajasthan, 335804</h6>
                            </td>
                            <td colspan="1" style="border-left:none">
                                <h6>Contact:</h6>
                                <p style="font-size:15px">Call & Whatsapp- 7891029090</p>
                                <h6>Email:</h6>
                                <p style="font-size:15px">info@dairymuneem.in, dairymuneem@gmail.com</p>
                                <h6>Website:</h6>
                                <p style="font-size:15px">www.dairymuneem.com/</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-center">
                                <h3><span class="two">A</span><span class="three">NIMAL </span><span class="two">R</span><span class="four">EQUIREMENTS</span></h3>
                                <h5>Input (Cows): {{ $number_of_cows ?? 'N/A' }}</h5>
                            </td>
                        </tr>
                        <tr></tr>
                    </tbody>
                    <tbody class="labels">
                        <tr>
                            <td colspan="7">
                                <label>RESULTS AT GLANCE:</label>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <th>Year-1</th>
                            <th>Year-2</th>
                            <th>Year-3</th>
                            <th>Year-4</th>
                            <th>Year-5</th>
                            <th>Av.</th>
                        </tr>
                    </tbody>
                    <tbody class="hide">
                        <tr>
                            <td>1) ESTIMATED CAPITAL REQUIRED (Rs)</td>
                            <td class="tr1">{{ number_format($worksheet->getCell('C12')->getOldCalculatedValue() ?? 0) }}</td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                        </tr>
                        <tr>
                            <td>a) Owners Capital (Rs)</td>
                            <td class="tr1">{{ $worksheet->getCell('C13')->getFormattedValue() ?? 'N/A' }}</td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                        </tr>
                        <tr>
                            <td>b) Loan Amount (Rs)</td>
                            <td class="tr1">{{ number_format($worksheet->getCell('C14')->getOldCalculatedValue() ?? 0) }}</td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                            <td class="tr1"></td>
                        </tr>
                        <tr>
                            <td>2a) RETURN ON CAPITAL INVEST. (%) (Excluding gain in animals)</td>
                            @foreach (['C15', 'D15', 'E15', 'F15', 'G15', 'H15'] as $cell)
                                <td class="tr1">{{ round($worksheet->getCell($cell)->getOldCalculatedValue() ?? 0, 2) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>2b) RETURN ON CAPITAL INVEST. (%) (considering animal gain)</td>
                            @foreach (['C16', 'D16', 'E16', 'F16', 'G16', 'H16'] as $cell)
                                <td class="tr1">{{ round($worksheet->getCell($cell)->getOldCalculatedValue() ?? 0, 2) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>3) BC RATIO</td>
                            @foreach (['C17', 'D17', 'E17', 'F17', 'G17', 'H17'] as $cell)
                                <td class="tr1">{{ round($worksheet->getCell($cell)->getOldCalculatedValue() ?? 0, 2) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>9) COST OF MILK PRODUCTION (Rs) with gained animal Nos</td>
                            @foreach (['C18', 'D18', 'E18', 'F18', 'G18', 'H18'] as $cell)
                                <td class="tr1">{{ round($worksheet->getCell($cell)->getOldCalculatedValue() ?? 0, 2) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>10) COST OF MILK PRODUCTION (Rs)</td>
                            @foreach (['C19', 'D19', 'E19', 'F19', 'G19', 'H19'] as $cell)
                                <td class="tr1">{{ round($worksheet->getCell($cell)->getOldCalculatedValue() ?? 0, 2) }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                    <tbody class="secondary">
                        <tr>
                            <td colspan="7">
                                <label for="accounting">A) Production Parameters Considered And Livestock Strength</label>
                                <input type="checkbox" name="accounting" id="accounting" data-toggle="toggle">
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <th>Year-1</th>
                            <th>Year-2</th>
                            <th>Year-3</th>
                            <th>Year-4</th>
                            <th>Year-5</th>
                            <th>Av.</th>
                        </tr>
                    </tbody>
                    <tbody class="hide">
                        <tr>
                            <td>Average Daily Milk Yield Of Cow Purchased</td>
                            <td class="secondary1">{{ $worksheet->getCell('C22')->getValue() ?? 'N/A' }}</td>
                            <td class="secondary1"></td>
                            <td class="secondary1"></td>
                            <td class="secondary1"></td>
                            <td class="secondary1"></td>
                            <td class="secondary1"></td>
                        </tr>
                        <tr>
                            <td>Increase In Milk Production Over Previous Year in %</td>
                            <td class="secondary1"></td>
                            @foreach (['D23', 'E23', 'F23', 'G23', 'H23'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Conception Rate%</td>
                            @foreach (['C24', 'D24', 'E24', 'F24', 'G24', 'H24'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Inter Calving Period Months</td>
                            @foreach (['C25', 'D25', 'E25', 'F25', 'G25', 'H25'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr><td colspan="7"></td></tr>
                        <tr>
                            <td>Mortality Adult</td>
                            @foreach (['C27', 'D27', 'E27', 'F27', 'G27', 'H27'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Mortality Heifer%</td>
                            @foreach (['C28', 'D28', 'E28', 'F28', 'G28', 'H28'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Mortality Growing Calf%(Above 1 Year)</td>
                            @foreach (['C29', 'D29', 'E29', 'F29', 'G29', 'H29'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Mortality Young Cows%</td>
                            @foreach (['C30', 'D30', 'E30', 'F30', 'G30', 'H30'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr><td colspan="7"></td></tr>
                        <tr>
                            <td>Culling Rate Adult Cow</td>
                            @foreach (['C32', 'D32', 'E32', 'F32', 'G32', 'H32'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Culling Rate Heifer</td>
                            @foreach (['C33', 'D33', 'E33', 'F33', 'G33', 'H33'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Culling Rate Growing Calf (Above 1 Year)</td>
                            @foreach (['C34', 'D34', 'E34', 'F34', 'G34', 'H34'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Culling Rate Calf</td>
                            @foreach (['C35', 'D35', 'E35', 'F35', 'G35', 'H35'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr><td colspan="7"></td></tr>
                        <tr>
                            <td>One Young Calf Equal To Adult</td>
                            @foreach (['C37', 'D37', 'E37', 'F37', 'G37'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                            <td></td>
                        </tr>
                        <tr>
                            <td>One Growing Calf(Above 12 Month)</td>
                            @foreach (['C38', 'D38', 'E38', 'F38', 'G38'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                            <td></td>
                        </tr>
                        <tr>
                            <td>One Heifer/Cow</td>
                            @foreach (['C39', 'D39', 'E39', 'F39', 'G39'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                            <td></td>
                        </tr>
                        <tr>
                            <th class="primary" colspan="7">Initial Livestock</th>
                        </tr>
                        <tr>
                            <td>Total Lactating Cows Units at The Start</td>
                            @foreach (['C41', 'D41', 'E41', 'F41', 'G41', 'H41'] as $cell)
                                <td class="primary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Heifer At The Start Of Year</td>
                            @foreach (['C42', 'D42', 'E42', 'F42', 'G42', 'H42'] as $cell)
                                <td class="primary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Growing Calf(Above 1 Year)</td>
                            @foreach (['C43', 'D43', 'E43', 'F43', 'G43', 'H43'] as $cell)
                                <td class="primary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Female Young Cows Brought/Born Of Year</td>
                            @foreach (['C44', 'D44', 'E44', 'F44', 'G44', 'H44'] as $cell)
                                <td class="primary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Initial Total Livestock Units At Start (Including Cows)</td>
                            @foreach (['C45', 'D45', 'E45', 'F45', 'G45', 'H45'] as $cell)
                                <td class="primary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="success" colspan="7">Mortality Detail</th>
                        </tr>
                        <tr>
                            <td>Total Lactating Cows Mortality</td>
                            @foreach (['C48', 'D48', 'E48', 'F48', 'G48', 'H48'] as $cell)
                                <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Heifer Mortality</td>
                            @foreach (['C49', 'D49', 'E49', 'F49', 'G49', 'H49'] as $cell)
                                <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Growing Calf Mortality</td>
                            @foreach (['C50', 'D50', 'E50', 'F50', 'G50', 'H50'] as $cell)
                                <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Young Cows Mortality</td>
                            @foreach (['C51', 'D51', 'E51', 'F51', 'G51', 'H51'] as $cell)
                                <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Mortality(Adult Livestock)</td>
                            @foreach (['C52', 'D52', 'E52', 'F52', 'G52', 'H52'] as $cell)
                                <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="warning" colspan="7">Stock After Mortality</th>
                        </tr>
                        <tr>
                            <td>Total Lactating Cows Less Mortality</td>
                            @foreach (['C55', 'D55', 'E55', 'F55', 'G55', 'H55'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Lactating Heifers Less Mortality</td>
                            @foreach (['C56', 'D56', 'E56', 'F56', 'G56', 'H56'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Growing Calves(Above 1 Year) Mortality</td>
                            @foreach (['C57', 'D57', 'E57', 'F57', 'G57', 'H57'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Calves Less Mortality</td>
                            @foreach (['C58', 'D58', 'E58', 'F58', 'G58', 'H58'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Stock Less Mortality</td>
                            @foreach (['C59', 'D59', 'E59', 'F59', 'G59', 'H59'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="info" colspan="7">Stock Culled & Sold</th>
                        </tr>
                        <tr>
                            <td>Lactating Cow</td>
                            @foreach (['C62', 'D62', 'E62', 'F62', 'G62', 'H62'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Heifer</td>
                            @foreach (['C63', 'D63', 'E63', 'F63', 'G63', 'H63'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Growing Calves(Above 1 Year)</td>
                            @foreach (['C64', 'D64', 'E64', 'F64', 'G64', 'H64'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Calf</td>
                            @foreach (['C65', 'D65', 'E65', 'F65', 'G65', 'H65'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Unit Culled</td>
                            @foreach (['C66', 'D66', 'E66', 'F66', 'G66', 'H66'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="dark" colspan="7">Final Stock At The End Of Year</th>
                        </tr>
                        <tr>
                            <td>Lactating Cow</td>
                            @foreach (['C69', 'D69', 'E69', 'F69', 'G69', 'H69'] as $cell)
                                <td class="dark1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Heifer</td>
                            @foreach (['C70', 'D70', 'E70', 'F70', 'G70', 'H70'] as $cell)
                                <td class="dark1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Growing Calf (Above 1 Year)</td>
                            @foreach (['C71', 'D71', 'E71', 'F71', 'G71', 'H71'] as $cell)
                                <td class="dark1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Calf</td>
                            @foreach (['C72', 'D72', 'E72', 'F72', 'G72', 'H72'] as $cell)
                                <td class="dark1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Livestock Units At The End Of Year</td>
                            @foreach (['C73', 'D73', 'E73', 'F73', 'G73', 'H73'] as $cell)
                                <td class="dark1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="primary" colspan="7">Gain In Livestock Unit</th>
                        </tr>
                        <tr>
                            <td>Livestock Unit At The End Of Year</td>
                            @foreach (['C76', 'D76', 'E76', 'F76', 'G76', 'H76'] as $cell)
                                <td class="primary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Gain In Livestock Unit</td>
                            @foreach (['C77', 'D77', 'E77', 'F77', 'G77', 'H77'] as $cell)
                                <td class="primary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Value Of Gain Livestock Unit</td>
                            @foreach (['C78', 'D78', 'E78', 'F78', 'G78', 'H78'] as $cell)
                                <td class="primary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                    <tbody class="secondary">
                        <tr>
                            <td>
                                <label>B) MILK PRODUCTION PROJECTIONS</label>
                            </td>
                            <td>Year-1</td>
                            <td>Year-2</td>
                            <td>Year-3</td>
                            <td>Year-4</td>
                            <td>Year-5</td>
                            <td>Av.</td>
                        </tr>
                    </tbody>
                    <tbody class="hide">
                        <tr>
                            <td>Total Number Of Expected Lactations/Year*</td>
                            @foreach (['C81', 'D81', 'E81', 'F81', 'G81', 'H81'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Expected Milk Yield/Lactation</td>
                            @foreach (['C82', 'D82', 'E82', 'F82', 'G82', 'H82'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Milk Production (lit)</td>
                            @foreach (['C83', 'D83', 'E83', 'F83', 'G83', 'H83'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Minus Milk For Feeding Calves (lit)(300Lt/Calf)</td>
                            @foreach (['C84', 'D84', 'E84', 'F84', 'G84', 'H84'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Milk Available For Sale (lit)</td>
                            @foreach (['C85', 'D85', 'E85', 'F85', 'G85', 'H85'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Daily Availability of Milk For Sale</td>
                            @foreach (['C86', 'D86', 'E86', 'F86', 'G86', 'H86'] as $cell)
                                <td class="secondary1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                    <tbody>
                        <tr>
                            <td class="success" colspan="7" class="C_label">
                                <label>C) Technical Parameters And Cost Of Purchased Material & Sale Prices Considered:</label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">Market Price Of Cow Considered On Per Liter Average Daily Yield (Rs)</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('D89')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Estimated Cost/Cow (Rs)</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('D90')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Estimated Housing + Equipments Cost (Rs), Detail Below At "L"</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('D91')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Estimated Capital Investment /Cow Unit (Rs)</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('D92')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Estimated Total Capital (Rs) Detail Given Below At L</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('D93')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Rate Of Interest</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('B94')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Margin Money (%)</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('B95')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Owners Capital</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('E96')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Loan (Rs)</td>
                            <td class="success1" colspan="3">{{ $worksheet->getCell('E97')->getFormattedValue() ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="warning"></th>
                            <th class="warning">Year-1</th>
                            <th class="warning">Year-2</th>
                            <th class="warning">Year-3</th>
                            <th class="warning">Year-4</th>
                            <th class="warning">Year-5</th>
                            <th class="warning">Av.</th>
                        </tr>
                    </tbody>
                    <tbody class="hide">
                        <tr>
                            <td>Annual Increase In Feed Cost, Milk Selling Prices & Wages %</td>
                            <td></td>
                            @foreach (['D99', 'E99', 'F99', 'G99', 'H99'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Milk Selling Price (Rs)/Lit. (av):</td>
                            @foreach (['C100', 'D100', 'E100', 'F100', 'G100', 'H100'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Purchase Price Of Green Fodder (Rs)/KG:</td>
                            @foreach (['C101', 'D101', 'E101', 'F101', 'G101', 'H101'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Purchase Price Of Straw (Rs)/KG:</td>
                            @foreach (['C102', 'D102', 'E102', 'F102', 'G102', 'H102'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Purchase Price Of Concentrate (Rs)/KG:</td>
                            @foreach (['C103', 'D103', 'E103', 'F103', 'G103', 'H103'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Contractual Labor Wages /Cow Unit/Year</td>
                            @foreach (['C104', 'D104', 'E104', 'F104', 'G104', 'H104'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Number Of Manager/Supervisor Hired @1 per/100A.Unit</td>
                            @foreach (['C105', 'D105', 'E105', 'F105', 'G105', 'H105'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Supervisors Salary / Annum (10% Annual Increase)</td>
                            @foreach (['C106', 'D106', 'E106', 'F106', 'G106', 'H106'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Supervisors Salary / Annum</td>
                            @foreach (['C107', 'D107', 'E107', 'F107', 'G107', 'H107'] as $cell)
                                <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                    <tbody class="info">
                        <tr>
                            <td>
                                <label>D) Executed Sale Proceeds</label>
                            </td>
                            <td>Unit Cost (Rs.)</td>
                            <td>Year-1</td>
                            <td>Year-2</td>
                            <td>Year-3</td>
                            <td>Year-4</td>
                            <td>Year-5</td>
                        </tr>
                    </tbody>
                    <tbody class="hide">
                        <tr>
                            <td>I) Milk Sale(Rs)</td>
                            <td class="info1"></td>
                            @foreach (['C111', 'D111', 'E111', 'F111', 'G111'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>ii) Misc. Sales</td>
                            <td class="info1"></td>
                            <td class="info1"></td>
                            <td class="info1"></td>
                            <td class="info1"></td>
                            <td class="info1"></td>
                            <td class="info1"></td>
                        </tr>
                        <tr>
                            <td>Livestock Unit Sold (Culled)</td>
                            <td class="info1">{{ $worksheet->getCell('B113')->getFormattedValue() ?? 'N/A' }}</td>
                            @foreach (['C113', 'D113', 'E113', 'F113', 'G113'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Male Calf (Disposed Of Within 1-2 Months)</td>
                            <td class="info1">{{ $worksheet->getCell('B114')->getFormattedValue() ?? 'N/A' }}</td>
                            @foreach (['C114', 'D114', 'E114', 'F114', 'G114'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Insurance Claim Of Mortality</td>
                            <td class="info1">{{ $worksheet->getCell('B115')->getFormattedValue() ?? 'N/A' }}</td>
                            @foreach (['C115', 'D115', 'E115', 'F115', 'G115'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Cow Dung/Livestock Unit</td>
                            <td class="info1">{{ $worksheet->getCell('B116')->getFormattedValue() ?? 'N/A' }}</td>
                            @foreach (['C116', 'D116', 'E116', 'F116', 'G116'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Total Sales</td>
                            <td class="info1"></td>
                            @foreach (['C117', 'D117', 'E117', 'F117', 'G117'] as $cell)
                                <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center">
                                <label>E) Expected Operational Expenditure</label>
                            </td>
                        </tr>
                         <tr>
                            <th></th>
                            <th>Ist year Unit Cost (Rs.)</th>
                            <th>Year-1</th>
                            <th>Year-2</th>
                            <th>Year-3</th>
                            <th>Year-4</th>
                            <th>Year-5</th>
                        </tr>
                    </tbody>
                    <tbody class="hide">
    <tr>
        <td>G. Fodder Cost @ 40kg/Animal Unit</td>
        <td>{{ $worksheet->getCell('B121')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C121', 'D121', 'E121', 'F121', 'G121'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Straw @ 3Kg/Animal Unit</td>
        <td>{{ $worksheet->getCell('B122')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C122', 'D122', 'E122', 'F122', 'G122'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Concentrate For Milk Production @ 2.5Kg/Lit</td>
        <td>{{ $worksheet->getCell('B123')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C123', 'D123', 'E123', 'F123', 'G123'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Concentrate Maintenance @1.5Kg/Ani. Unit</td>
        <td>{{ $worksheet->getCell('B124')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C124', 'D124', 'E124', 'F124', 'G124'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Medicines & AI etc.</td>
        <td>{{ $worksheet->getCell('B125')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C125', 'D125', 'E125', 'F125', 'G125'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Rent/Leasing Cost For Land For Shed etc /A.Unit.</td>
        <td>{{ $worksheet->getCell('B126')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C126', 'D126', 'E126', 'F126', 'G126'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Contractual Labor Wages /Cow Unit/Year</td>
        <td>{{ $worksheet->getCell('B127')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C127', 'D127', 'E127', 'F127', 'G127'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Salary Of Supervisor/Annum</td>
        <td></td>
        @foreach (['C128', 'D128', 'E128', 'F128', 'G128'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Insurance Premium Cows Only</td>
        <td>{{ $worksheet->getCell('B129')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C129', 'D129', 'E129', 'F129', 'G129'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Electricity Charges@ 1200 /Animal Unit/Year</td>
        <td>{{ $worksheet->getCell('B130')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C130', 'D130', 'E130', 'F130', 'G130'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Other misc. charges@1200/animal unit</td>
        <td>{{ $worksheet->getCell('B131')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C131', 'D131', 'E131', 'F131', 'G131'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td><b>(a) Total Operating Cost</b></td>
        <td></td>
        @foreach (['C132', 'D132', 'E132', 'F132', 'G132'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Operating Surplus (Total Sale - Operational Cost)</td>
        <td></td>
        @foreach (['C133', 'D133', 'E133', 'F133', 'G133'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>(b) Dep. On Shed Machinery & Equipments</td>
        <td>{{ $worksheet->getCell('B134')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C134', 'D134', 'E134', 'F134', 'G134'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Total Exp. (a+b)</td>
        <td></td>
        @foreach (['C135', 'D135', 'E135', 'F135', 'G135'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td colspan="7"></td>
    </tr>
    <tr>
        <td>F) Net Profit</td>
        <td></td>
        @foreach (['C136', 'D136', 'E136', 'F136', 'G136'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>G) A)Return On Capital Invest.(%)</td>
        <td></td>
        @foreach (['C137', 'D137', 'E137', 'F137', 'G137'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>G) B)Return On Capital Invest.( %)INCL ANI></td>
        <td></td>
        @foreach (['C138', 'D138', 'E138', 'F138', 'G138'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>H) BC Ratio</td>
        <td></td>
        @foreach (['C139', 'D139', 'E139', 'F139', 'G139'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Ia) Cost Of Milk Production (Rs) With Animal Gain</td>
        <td></td>
        @foreach (['C140', 'D140', 'E140', 'F140', 'G140'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
    <tr>
        <td>Ib) Cost Of Milk Production (Rs)</td>
        <td></td>
        @foreach (['C141', 'D141', 'E141', 'F141', 'G141'] as $cell)
            <td class="info1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
    </tr>
</tbody>
<tbody class="labels">
    <tr>
        <td colspan="7">
            <label>J) Loan Disbursement And Payment Schedule</label>
        </td>
    </tr>
    <tr>
        <th>Year</th>
        <th>Loan</th>
        <th>Interest</th>
        <th colspan="2">Installment</th>
        <th colspan="2">Total</th>
    </tr>
</tbody>
<tbody class="hide">
    <tr>
        <td>1</td>
        <td class="primary1">{{ $worksheet->getCell('B144')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1">{{ $worksheet->getCell('C144')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D144')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('E144')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>2</td>
        <td class="primary1">{{ $worksheet->getCell('B145')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1">{{ $worksheet->getCell('C145')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D145')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('E145')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>3</td>
        <td class="primary1">{{ $worksheet->getCell('B146')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1">{{ $worksheet->getCell('C146')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D146')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('E146')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>4</td>
        <td class="primary1">{{ $worksheet->getCell('B147')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1">{{ $worksheet->getCell('C147')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D147')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('E147')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>5</td>
        <td class="primary1">{{ $worksheet->getCell('B148')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1">{{ $worksheet->getCell('C148')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D148')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('E148')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
</tbody>
<tbody>
    <tr>
        <td class="secondary" colspan="7">
            <label>K) Cash Balance After Debt Service</label>
        </td>
    </tr>
    <tr>
        <th>Year</th>
        <th colspan="2">Open.Surplus</th>
        <th colspan="2">Payments</th>
        <th colspan="2">Cash Balance</th>
    </tr>
</tbody>
<tbody class="hide">
    <tr>
        <td>1</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('B152')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('C152')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D152')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>2</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('B153')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('C153')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D153')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>3</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('B154')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('C154')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D154')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>4</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('B155')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('C155')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D155')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>5</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('B156')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('C156')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="primary1" colspan="2">{{ $worksheet->getCell('D156')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="7" style="color:red">*Average no of full lactation = no of calving (No.cows X 12 months / inter-calving period in months )</td>
    </tr>
</tbody>
<tbody>
    <tr>
        <td class="success" colspan="7">
            <label for="accounting">K) Requirements Of Feed Fodder And Land Requirements For Fodder Cultivation</label>
        </td>
    </tr>
</tbody>
<tbody class="hide">
    <tr>
        <td>Concentrate Required Annually (Ton)*</td>
        <td class="success1">{{ $worksheet->getCell('B161')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C161', 'D161', 'E161', 'F161'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Green Fodder Required Annually (Ton)</td>
        <td class="success1">{{ $worksheet->getCell('B162')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C162', 'D162', 'E162', 'F162'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Wheat Straw Required Annually (Tons)</td>
        <td class="success1">{{ $worksheet->getCell('B163')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C163', 'D163', 'E163', 'F163'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td colspan="7"></td>
    </tr>
    <tr>
        <td>Average Concentrate(kg) /Animal Unit</td>
        <td class="success1">{{ $worksheet->getCell('B165')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C165', 'D165', 'E165', 'F165'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Average Green Fodder(kg)/Animal/Unit</td>
        <td class="success1">{{ $worksheet->getCell('B166')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C166', 'D166', 'E166', 'F166'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Average Straw(Kg) /Animal Unit</td>
        <td class="success1">{{ $worksheet->getCell('B167')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C167', 'D167', 'E167', 'F167'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td colspan="7"><b>*Normally concentrate formulae has 1/3 grains,1/3 oil cakes and 1/3 industrial by products</b></td>
    </tr>
    <tr>
        <td>All Oil Cakes(Tons)</td>
        <td class="success1">{{ $worksheet->getCell('B169')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C169', 'D169', 'E169', 'F169'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Mustard Cake(3/4 Of All Cakes)Tons</td>
        <td class="success1">{{ $worksheet->getCell('B170')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C170', 'D170', 'E170', 'F170'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Mustard Cake For 3 Months(Tons)</td>
        <td class="success1">{{ $worksheet->getCell('B171')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C171', 'D171', 'E171', 'F171'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td><b>Finance Required For Feeding</b></td>
        <td><b>Year - 1</b></td>
        <td><b>Year - 2</b></td>
        <td><b>Year - 3</b></td>
        <td><b>Year - 4</b></td>
        <td><b>Year - 5</b></td>
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Finance For Concentrate Required Annually (Rs)</td>
        <td class="success1">{{ $worksheet->getCell('B174')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C174', 'D174', 'E174', 'F174'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Finance For Green Fodder Required Annually(Rs)</td>
        <td class="success1">{{ $worksheet->getCell('B175')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C175', 'D175', 'E175', 'F175'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>Finance For Wheat Straw Required Annually(Rs)</td>
        <td class="success1">{{ $worksheet->getCell('B176')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C176', 'D176', 'E176', 'F176'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td colspan="7"></td>
    </tr>
    <tr>
        <td><b>Total</b></td>
        <td class="success1">{{ $worksheet->getCell('B178')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C178', 'D178', 'E178', 'F178'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
    <tr>
        <td>% Of Total Operational Cost</td>
        <td class="success1">{{ $worksheet->getCell('B179')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C179', 'D179', 'E179', 'F179'] as $cell)
            <td class="success1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="success1"></td>
    </tr>
</tbody>
<tbody>
    <tr>
        <td class="warning" colspan="7">
            <label>Land Required For Fodder Growing(Acre)</label>
        </td>
    </tr>
</tbody>
<tbody class="hide">
    <tr>
        <td>Land Productivity/Annum(qt) Considered</td>
        <td class="warning1">{{ $worksheet->getCell('B181')->getFormattedValue() ?? 'N/A' }}</td>
        <td class="warning1"></td>
        <td class="warning1"></td>
        <td class="warning1"></td>
        <td class="warning1"></td>
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>Accordingly Calculated Land Required For Fodder(Acres)</td>
        <td class="warning1">{{ $worksheet->getCell('B182')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C182', 'D182', 'E182', 'F182'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>% Of Required Fodder Grown</td>
        <td class="warning1">{{ $worksheet->getCell('B183')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C183', 'D183', 'E183', 'F183'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>Accordingly Land Required For Fodder Growing(Acre)</td>
        <td class="warning1">{{ $worksheet->getCell('B184')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C184', 'D184', 'E184', 'F184'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>Total Fodder To Be Purchased (Ton)/Year</td>
        <td class="warning1">{{ $worksheet->getCell('B185')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C185', 'D185', 'E185', 'F185'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>Silage Feeding Of Purchased Fodder</td>
        <td class="warning1">{{ $worksheet->getCell('B186')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C186', 'D186', 'E186', 'F186'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>Green Fodder Replaced For Silage (Tons)</td>
        <td class="warning1">{{ $worksheet->getCell('B187')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C187', 'D187', 'E187', 'F187'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>Additional Fodder For Silage Making Loses (15%)</td>
        <td class="warning1">{{ $worksheet->getCell('B188')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C188', 'D188', 'E188', 'F188'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>Total Fodder To Be Purchased For Silage(Ton)/Year</td>
        <td class="warning1">{{ $worksheet->getCell('B189')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C189', 'D189', 'E189', 'F189'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
    <tr>
        <td>Total Fodder For Feeding (Purchased)(Ton)</td>
        <td class="warning1">{{ $worksheet->getCell('B190')->getFormattedValue() ?? 'N/A' }}</td>
        @foreach (['C190', 'D190', 'E190', 'F190'] as $cell)
            <td class="warning1">{{ $worksheet->getCell($cell)->getFormattedValue() ?? 'N/A' }}</td>
        @endforeach
        <td class="warning1"></td>
    </tr>
</tbody>
<tbody class="labels">
    <tr>
        <td colspan="7">
            <label>L) Project Cost Calculation</label>
        </td>
    </tr>
    <tr>
        <th colspan="7">a) Sheds Area Calculations:</th>
    </tr>
</tbody>
<tbody class="hide">
    <tr>
        <td class="info" colspan="7"><b>Sheds Breadth Calculation</b></td>
    </tr>
    <tr>
        <td colspan="6">Feeding Manger(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E197')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Standing Place(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E198')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Feeding Tractor Trolley Space(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E199')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Backyard Breadth(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E200')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Height(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E201')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Height At Eves(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E202')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Open Space/Side(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E203')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Shed Breadth Excluding Open Space(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E204')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Shed Breadth Including Open Space(ft)</td>
        <td class="info1">{{ $worksheet->getCell('E205')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th class="dark" colspan="7">Length Calculation</th>
    </tr>
</tbody>
<tbody class="hide">
    <tr>
        <td colspan="6">Number Of Animals/Row</td>
        <td class="dark1">{{ $worksheet->getCell('E207')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Space Width/Animal (ft)</td>
        <td class="dark1">{{ $worksheet->getCell('E208')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">No Of Water Troughs/Side</td>
        <td class="dark1">{{ $worksheet->getCell('E209')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">T. Water Trough Length (@5ft)</td>
        <td class="dark1">{{ $worksheet->getCell('E210')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Total Space At Entry And End(ft)</td>
        <td class="dark1">{{ $worksheet->getCell('E211')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Total Length (ft)</td>
        <td class="dark1">{{ $worksheet->getCell('E212')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th class="primary" colspan="7">Area Of One Shed And Number Of Sheds And Their Cost Calculations:</th>
    </tr>
</tbody>
<tbody class="hide">
    <tr>
        <td colspan="6">Covered Area /Shed(sq.ft)</td>
        <td class="primary1">{{ $worksheet->getCell('E214')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Area Open(sq.ft)</td>
        <td class="primary1">{{ $worksheet->getCell('E215')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Rate/Sq Ft Covered Area With Fittings</td>
        <td class="primary1">{{ $worksheet->getCell('E216')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Rate /Sq Feed Open Paved Area</td>
        <td class="primary1">{{ $worksheet->getCell('E217')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Cost Covered Area(Rs)</td>
        <td class="primary1">{{ $worksheet->getCell('E218')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Cost Open Area(Rs)</td>
        <td class="primary1">{{ $worksheet->getCell('E219')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Total Cost/Shed (Crore)</td>
        <td class="primary1">{{ $worksheet->getCell('E220')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Av.Total Number Of Animal Unit</td>
        <td class="primary1">{{ $worksheet->getCell('E221')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6"><b>Number Of Sheds Required</b></td>
        <td class="primary1"><b>{{ $worksheet->getCell('E222')->getFormattedValue() ?? 'N/A' }}</b></td>
    </tr>
    <tr>
        <td colspan="6">Total Cost Of Sheds And Paved Open Area(Crore Rs)</td>
        <td class="primary1">{{ $worksheet->getCell('E223')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Cost Of Other Structures(Stores,Offices etc /Roads) @10%(Crore)</td>
        <td class="primary1">{{ $worksheet->getCell('E224')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Total Cost Of Structure And Roads</td>
        <td class="primary1">{{ $worksheet->getCell('E225')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Cost Of Machinery(Milking,Feeding,Cleaning etc)@20% Of Shed Cost</td>
        <td class="primary1">{{ $worksheet->getCell('E226')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Cost Of Livestock(Crore)</td>
        <td class="primary1">{{ $worksheet->getCell('E227')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Other Costs(Consultancy,One Month Working Capital etc)</td>
        <td class="primary1">{{ $worksheet->getCell('E228')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td colspan="6">Total Costs(Crore)</td>
        <td class="primary1">{{ $worksheet->getCell('E229')->getFormattedValue() ?? 'N/A' }}</td>
    </tr>
</tbody>    
                </table>
            </div>
        </div>
    </body>
</html>