@extends('admin.base_template')

@section('main')

<style>
    input.qtydiscount {
        width: 61px;
    }
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>View All Farmers</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">View All Farmers</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-users"></i> View Farmers</h3>
                    </div>
                    <div class="panel panel-default">
                        <!-- Flash Messages -->
                        @if (session('smessage'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Alert!</h4>
                                {{ session('smessage') }}
                            </div>
                        @endif
                        @if (session('emessage'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                                {{ session('emessage') }}
                            </div>
                        @endif

                        <div class="panel-body">
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-bordered table-hover table-striped" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Village</th>
                                            <th>State</th>
                                            <th>District</th>
                                            <th>City</th>
                                            <th>Pincode</th>
                                            <th>No. of Animals</th>
                                            <th>Phone</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Reward</th>
                                            <th>COD</th>
                                            <th>Qty Discount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($farmers_data as $index => $data)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $data->name }}</td>
                                                <td>{{ $data->village }}</td>
                                                <td>{{ $data->state }}</td>
                                                <td>{{ $data->district }}</td>
                                                <td>{{ $data->city }}</td>
                                                <td>{{ $data->Pincode }}</td>
                                                <td>{{ $data->no_animals ?? '0' }}</td>
                                                <td>{{ $data->phone_number }}</td>
                                                <td>{{ $data->date }}</td>
                                                <td>
                                                    @if ($data->is_active == 1)
                                                        <p class="label bg-green">Unblocked</p>
                                                    @else
                                                        <p class="label bg-yellow">Blocked</p>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($data->giftcard_id)
                                                        @php
                                                            $gift_card = \App\Models\GiftCard::find($data->giftcard_id);
                                                        @endphp
                                                        @if ($gift_card)
                                                            <img id="slide_img_path" height="50" width="100" src="{{ asset('assets/uploads/gift_card/' . $gift_card->image) }}">
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                   <form action="{{ route('admin.farmers.update_cod', $data->id) }}" method="POST">
        @csrf
        <input type="checkbox" name="cod" {{ $data->cod == 1 ? 'checked' : '' }} onchange="this.form.submit()">
    </form>
                                                </td>
                                                <td>
                                                    <input type="number" class="qtydiscount" data-id="{{ $data->id }}" name="qty_discount" value="{{ $data->qty_discount ?? '' }}">
                                                </td>
                                                <td>
                                                    <div class="btn-group" id="btns{{ $index + 1 }}">
                                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                            Action <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            @if ($data->is_active == 1)
                                                                <li><a href="{{ route('admin.farmers.update_status', [base64_encode($data->id), 'inactive']) }}">Block</a></li>
                                                            @else
                                                                <li><a href="{{ route('admin.farmers.update_status', [base64_encode($data->id), 'active']) }}">Unblock</a></li>
                                                            @endif
                                                            <li><a href="javascript:;" class="dCnf" data-mydata="{{ $index + 1 }}">Delete</a></li>
                                                            <li><a href="{{ route('admin.farmers.records', base64_encode($data->id)) }}">View Records</a></li>
                                                        </ul>
                                                    </div>
                                                    <div style="display:none" id="cnfbox{{ $index + 1 }}">
                                                        <p>Are you sure you want to delete this?</p>
                                                        <a href="{{ route('admin.farmers.delete', base64_encode($data->id)) }}" class="btn btn-danger">Yes</a>
                                                        <a href="javascript:;" class="cans btn btn-default" data-mydatas="{{ $index + 1 }}">No</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>

{{-- <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTable').DataTable({
            responsive: true,
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('offersDataTables', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('offersDataTables'));
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copyHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'csvHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'excelHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'pdfHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'print', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } }
            ]
        });

        $(document.body).on('click', '.dCnf', function() {
            var i = $(this).data('mydata');
            $("#btns" + i).hide();
            $("#cnfbox" + i).show();
        });

        $(document.body).on('click', '.cans', function() {
            var i = $(this).data('mydatas');
            $("#btns" + i).show();
            $("#cnfbox" + i).hide();
        });

        // COD Checkbox AJAX
        $('.mycheckbox').on('change', function() {
            var isChecked = $(this).prop('checked');
            var userId = $(this).data('id');

            alert('Successfully updated');

            $.ajax({
                type: 'POST',
                url: '{{ route("admin.farmers.update_cod") }}',
                data: {
                    userId: userId,
                    isChecked: isChecked,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        // Quantity Discount AJAX
        $('.qtydiscount').on('change', function() {
            var qtyDiscount = $(this).val() || '0';
            var userId = $(this).data('id');

            alert('Successfully updated');

            $.ajax({
                type: 'POST',
                url: '{{ route("admin.farmers.qty_update") }}',
                data: {
                    userId: userId,
                    qtyDiscount: qtyDiscount,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script> --}}
<!-- Include DataTables and Export Scripts -->
@push('scripts')

@endpush
@endsection