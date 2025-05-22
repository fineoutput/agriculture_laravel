@extends('admin.base_template')

@section('main')

<div class="content-wrapper">
    <section class="content-header">
        <h1>{{ $farmer->name ?? 'Unknown Farmer' }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.farmers.index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Records</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
           <div class="col-lg-2 card bg-info mini-stat position-relative mr-2 ">
    <a href="{{ route('admin.farmers.view_health_info', $farmer_id) }}">
        <div class="card-body">
            <div class="mini-stat-desc">
                <h6 class="text-uppercase verti-label text-white-50 text-bold"><b>Health Info</b></h6>
                <div class="text-white">
                    <h6 class="text-uppercase mt-0 text-white fw-bold">Health Records</h6>
                    <h3 class="mb-3 mt-0">{{ $health_info ?? 0 }}</h3>
                    <div class="">
                        <span class="ml-2">Total Entries</span>
                    </div>
                </div>
                <div class="mini-stat-icon">
                    <i class="fa fa-user-md display-2"></i>
                </div>
            </div>
        </div>
    </a>
</div>

           <div class="col-lg-2 card bg-danger mini-stat position-relative mr-2">
    <a href="{{ route('admin.farmers.view_breeding_records', $farmer_id) }}">
        <div class="card-body">
            <div class="mini-stat-desc">
                <h6 class="text-uppercase verti-label text-white-50">Breeding Record</h6>
                <div class="text-white">
                    <h6 class="text-uppercase mt-0 text-white-50">Breeding Logs</h6>
                    <h3 class="mb-3 mt-0">{{ $breeding_record ?? 0 }}</h3>
                    <div class="">
                        <span class="ml-2">Total Records</span>
                    </div>
                </div>
                <div class="mini-stat-icon">
                    <i class="fa fa-medkit display-2"></i>
                </div>
            </div>
        </div>
    </a>
</div>

            
         <div class="col-lg-2 card bg-success mini-stat position-relative mr-2">
    <a href="{{ route('admin.farmers.view_daily_records', $farmer_id) }}">
        <div class="card-body">
            <div class="mini-stat-desc">
                <h6 class="text-uppercase verti-label text-white-50">Daily Records</h6>
                <div class="text-white">
                    <h6 class="text-uppercase mt-0 text-white-50">Farm Activity</h6>
                    <h3 class="mb-3 mt-0">{{ $daily_records ?? 0 }}</h3>
                    <div class="">
                        <span class="ml-2">Total Entries</span>
                    </div>
                </div>
                <div class="mini-stat-icon">
                    <i class="ion ion-ios-people-outline display-2"></i>
                </div>
            </div>
        </div>
    </a>
</div>

            <div class="col-lg-2 card bg-warning mini-stat position-relative mr-2">
    <a href="{{ route('admin.farmers.view_milk_records', $farmer_id) }}">
        <div class="card-body">
            <div class="mini-stat-desc">
                <h6 class="text-uppercase verti-label text-white-50">Milk Record</h6>
                <div class="text-white">
                    <h6 class="text-uppercase mt-0 text-white-50">Milk Production</h6>
                    <h3 class="mb-3 mt-0">{{ $milk_records ?? 0 }}</h3>
                    <div class="">
                        <span class="ml-2">Total Records</span>
                    </div>
                </div>
                <div class="mini-stat-icon">
                    <i class="fa fa-cube display-2"></i>
                </div>
            </div>
        </div>
    </a>
</div>
          <div class="col-lg-2 card bg-dark mini-stat position-relative mr-2">
    <a href="{{ route('admin.farmers.view_medical_expenses', $farmer_id) }}">
        <div class="card-body">
            <div class="mini-stat-desc">
                <h6 class="text-uppercase verti-label text-white-50">Medical Expenses</h6>
                <div class="text-white">
                    <h6 class="text-uppercase mt-0 text-white-50">Vet Costs</h6>
                    <h3 class="mb-3 mt-0">{{ $medical_expenses ?? 0 }}</h3>
                    <div class="">
                        <span class="ml-2">Total Spent</span>
                    </div>
                </div>
                <div class="mini-stat-icon">
                    <i class="fas fa-syringe display-2"></i>
                </div>
            </div>
        </div>
    </a>
</div>

          
        </div>

        <div class="row">


           <div class="col-lg-2 card bg-primary mini-stat position-relative mr-2">
    <a href="{{ route('admin.farmers.view_stock_list', $farmer_id) }}">
        <div class="card-body">
            <div class="mini-stat-desc">
                <h6 class="text-uppercase verti-label text-white-50">Stock List</h6>
                <div class="text-white">
                    <h6 class="text-uppercase mt-0 text-white-50">Inventory</h6>
                    <h3 class="mb-3 mt-0">{{ $stock_handling ?? 0 }}</h3>
                    <div class="">
                        <span class="ml-2">Current Stock</span>
                    </div>
                </div>
                <div class="mini-stat-icon">
                    <i class="fas fa-calculator display-2"></i>
                </div>
            </div>
        </div>
    </a>
</div>

            <div class="col-lg-2 card bg-primary mini-stat position-relative mr-2">
    <a href="{{ route('admin.farmers.view_semen_tank_list', $farmer_id) }}">
        <div class="card-body">
            <div class="mini-stat-desc">
                <h6 class="text-uppercase verti-label text-white-50">Semen Tank</h6>
                <div class="text-white">
                    <h6 class="text-uppercase mt-0 text-white-50">Available Tanks</h6>
                    <h3 class="mb-3 mt-0">{{ $tank ?? 0 }}</h3>
                    <div class="">
                        <span class="ml-2">Total in Stock</span>
                    </div>
                </div>
                <div class="mini-stat-icon">
                    <i class="fas fa-prescription-bottle display-2"></i>
                </div>
            </div>
        </div>
    </a>
</div>

                     <div class="col-lg-2 card bg-primary mini-stat position-relative mr-2">
    <a href="{{ route('admin.farmers.view_sale_purchase', $farmer_id) }}">
        <div class="card-body">
            <div class="mini-stat-desc">
                <h6 class="text-uppercase verti-label text-white-50">Sale / Purchase</h6>
                <div class="text-white">
                    <h6 class="text-uppercase mt-0 text-white-50">Transactions</h6>
                    <h3 class="mb-3 mt-0">{{ $sale_purchase ?? 0 }}</h3>
                    <div class="">
                        <span class="ml-2">Total Deals</span>
                    </div>
                </div>
                <div class="mini-stat-icon">
                    <i class="fa fa-credit-card display-2"></i>
                </div>
            </div>
        </div>
    </a>
</div>
  </div>
             
      
    </section>

    
</div>

<!-- jQuery (required by DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#userTable').DataTable({
            responsive: true,
        });
        $(document.body).on('click', '.dCnf', function() {
            var i = $(this).attr("mydata");
            console.log(i);
            $("#btns" + i).hide();
            $("#cnfbox" + i).show();
        });
        $(document.body).on('click', '.cans', function() {
            var i = $(this).attr("mydatas");
            console.log(i);
            $("#btns" + i).show();
            $("#cnfbox" + i).hide();
        })
    });
</script>
@endsection