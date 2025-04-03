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
    margin-top: 20px;
    margin-left: 710px;
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
      <!-- End show success and error messages -->
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
          <p class="mb-0"><span><i class="fa fa-key" aria-hidden="true"></i></span>&nbsp;&nbsp;Change Password</p>
        </div>
      </div>
      <div class="container font p-5" style="    background: #fff;
    border: 1px solid lightgrey;
    border-radius: 5px;
    border-top-left-radius: 0;
    border-top-right-radius: 0;">
        <form action="{{route('admin_change_password')}}" method="POST">
          @csrf
          <div class="row" style="border-top:none;">
            <div class="col-4">
              <p>Old Password<span style="color:red;">*</span></p>
            </div>
            <div class="col-6">
              <p>
                <input type="password" name="old" value="" id="old" class="form-control" placeholder="Old Password" required />
              </p>
            </div>
          </div>

          <div class="row" style="border-top:none;">
            <div class="col-4">
              <p>New Password<span style="color:red;">*</span></p>
            </div>
            <div class="col-6">
              <p>
                <input type="password" name="new" value="" id="new" class="form-control" placeholder="New Password" required />
              </p>
            </div>
          </div>

          <div class="row" style="border-top:none;">
            <div class="col-4">
              <p>Confirm Password<span style="color:red;">*</span></p>
            </div>
            <div class="col-6">
              <p>
                <input type="password" name="confirm" value="" id="confirm" class="form-control" placeholder="Confirm Password" required />
              </p>
            </div>
          </div>


          <div class="row">
            <div class="col-12">
              <button type="submit">Submit Password</button>
            </div>
          </div>


        </form>
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