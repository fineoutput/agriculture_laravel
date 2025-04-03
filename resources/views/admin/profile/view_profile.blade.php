@extends('admin.base_template')
@section('main')

<style>
  .font .row {
    border-top: 1px solid lightgrey;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }

  .font .col-4 p {
    font-weight: bold;
    color: #000;
  }


  .font button {
    border: none;
    background: lightgrey;
    color: black;
    padding: 10px;
    outline: 0 !important;
    margin-bottom: 0.75rem;
  }
</style>
<section>
  <div class="container-fluid" style="    position: relative;
    top:30px;padding: 4rem;
    margin-bottom:3rem;">
    <div class="row">
      <!-- show success and error messages -->
      @if (session('success'))
      <div class="alert alert-success" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
      </div>
      @endif
      @if (session('error'))
      <div class="alert alert-danger" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
      </div>
      @endif
      <div class="row" style="
      background: #bfbfbf;
width: 100%;
padding: 0;
margin: 0;
height: 44px;
align-items: center;
border-top-left-radius: 5px;
border-top-right-radius: 5px;
color: black; ">
        <div class="col-12">
          <p class="mb-0"><span><i class="fa fa-user-circle"></i></span>&nbsp;&nbsp;Profile</p>
        </div>
      </div>
      <div class=" font p-5" style="    background: #fff;
    border: 1px solid lightgrey;
    border-radius: 5px;
    border-top-left-radius: 0;
    border-top-right-radius: 0;">

        <div class="row">
          <div class="col-4">
            <p>Name</p>
          </div>
          <div class="col-6">
            <p>
              <?php if (!empty($profile_data)) {
                echo $profile_data->name;
              } ?>
            </p>
          </div>
        </div>

        <div class="row">
          <div class="col-4">
            <p>Email</p>
          </div>
          <div class="col-6">
            <p>
              <?php if (!empty($profile_data)) {
                echo $profile_data->email;
              } ?>
            </p>
          </div>
        </div>

        <div class="row">
          <div class="col-4">
            <p>Phone</p>
          </div>
          <div class="col-6">
            <p>
              <?php if (!empty($profile_data)) {
                echo $profile_data->phone;
              } ?>
            </p>
          </div>
        </div>

        <div class="row">
          <div class="col-4">
            <p>Address</p>
          </div>
          <div class="col-6">
            <p>
              <?php if (!empty($profile_data)) {
                echo $profile_data->address;
              } ?>
            </p>
          </div>
        </div>

        <div class="row">
          <div class="col-4">
            <p>Password </p>
          </div>
          <div class="col-6">
            <a href="{{route('view_change_password')}}"><button>Change Password</button>
            </a>
          </div>
        </div>


        <div class="row">
          <div class="col-4">
            <p>Power</p>
          </div>
          <div class="col-6">
            <p>
              <?php if (!empty($profile_data)) {
                if ($profile_data->power == 1) {
                  echo 'Super Admin';
                } else if ($profile_data->power == 2) {
                  echo 'Admin';
                } else if ($profile_data->power == 3) {
                  echo 'Manager';
                }
              } ?>
            </p>
          </div>
        </div>

        <!-- <div class="row">
          <div class="col-4"><p>Services</p></div>
          <div class="col-6">
            <p>
              <?php if (!empty($profile_data)) {
                echo $profile_data->email;
              } ?>
            </p>
          </div>
        </div> -->


      </div>
    </div>
  </div>
</section>
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