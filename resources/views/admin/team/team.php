@extends('admin.base_template')
@section('main')
<style>
  .border
  {
    border: 1px solid #E5E4E2;
    padding: 10px;
    box-shadow: 0 5px 10px #aaaaaa;
  }

 
  hr{
    border-top: 2px solid #2a2a2a;
    
  }
  .para
  {
    text-transform: uppercase;
    color: #2554C7;
  }
  .para2
  {
    color: #6D6968;
  }
  .head
  {
    color: #ed3338;
  }
  .form-check-input {
 
    border: 1px solid black;

}

.form-check-input[type=checkbox] {
    border-radius: .25em;
}

.form-control {
    background-color: transparent;
    border-top: none!important;
    border-left: none!important;
    border-right: none!important;
    border-radius: 0!important;
}



.form-check-input {
    width: 1em;
    height: 1em;
    margin-top: .25em;
    vertical-align: top;
    background-color: #fff;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    border: 1px solid rgba(0,0,0,.25);
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    -webkit-print-color-adjust: exact;
   
}
.ac {
    margin-left: 22px;
}
.Tool_Kit{
    margin-left: 4px;
}
.First_Aid{
    margin-left: 19px;
}
.Wipers {
    margin-left: -6px;
}
.Cleaniness
{
  
    margin-left: 35px;

}
.btn {
    
    border: 1px solid #ed3338;
    background-color: #ed3338;
    color: #fff;
    
}
.form-check-inline {
    
    margin-right: 8.75rem;
}
@media (min-width: 320px) and (max-width: 767px)
{
  .form-check-inline {
    
    margin-right: 1.75rem;
}


.ac {
    margin-left: 0px;
}
.Tool_Kit{
    margin-left: 0px;
}
.First_Aid{
    margin-left: 0px;
}
.Wipers {
    margin-left: 0px;
}
.Cleaniness
{
  
    margin-left: 0px;

}
}
</style>
<!-- Start content -->
<div class="content">
<div class="container-fluid ">

 <section class="border "> 
  <div class="row"> 
    <div class="col-md-6">
      <p class="head"><b>Customer Name</b></p>
      <p class="para">booking holding</p>
    </div>
    <div class="col-md-6">
      <p class="head"><b>Selected Vehicle</b></p>
      <p class="para2">Maruti Swift-2021-Vxi Grey (RJ14 TE 9551)</p>
    </div>
  </div>
  
   <div class="row">
   <div class="col-md-4">
    <p class="head"><b>Booking From</b></p>
    <p class="para2">Malviya Nagar, Jaipur</p>
    </div>
    <div class="col-md-4">
    <p class="head"><b>Start Date & Time</b></p>
    <p class="para2">06-Aug-2022 02:00</p>
    </div>
   <div class="col-md-4">
    <p class="head"><b>End Date & Time</b></p>
    <p class="para2">08-Aug-2022 10:00</p>
    </div>
   </div>

   <div class="row">
  <div class="col-md-4">
    <p class="head"><b>Plan Selected</b></p>
    <p class="para2">Base Plan</p>
    </div>
   <div class="col-md-4">
    <p class="head"><b>Coupon code Applied</b></p>
    <p class="para2">NA</p>
    </div>
   <div class="col-md-4">
    <p class="head"><b>Payment Status</b></p>
    <p class="para2">Advance Paid</p>
    </div>
   </div>

   
    <p class="head"><b>Advance Payment Ref.#</b></p>
    <p class="para2">NA</p>
   
  
 </section>

 <section class="border mt-3">
 <h5><p class="para2">Pricing</p></h5>
 <div class="row"> 
    <div class="col-md-2">
      
    <p class="head"><b>Trip Amount</b></p>
    <p class="para2">₹3860</p>
    </div>
    <div class="col-md-2">
      
      <p class="head"><b>Security Deposit</b></p>
      <p class="para2">₹2000</p>
      </div>
      <div class="col-md-2">
      
      <p class="head"><b>Discount(If any)</b></p>
      <p class="para2">₹0</p>
      </div>
      <div class="col-md-2">
      
      <p class="head"><b>Advance Payment</b></p>
      <p class="para2">₹4124</p>
      </div>

      <div class="col-md-2">
      
      <p class="head"><b>Free Kms</b></p>
      <p class="para2">220</p>
      </div>
      <div class="col-md-2">
      
      <p class="head"><b>Total Time</b></p>
      <p class="para2">44 Hours</p>
      </div>
 </div>
 <hr>
 <div class="row">
 <div class="col-md-4">
    <h5><p class="head">Extented Booking</p></h5>
  <p class="head"><b>Extension Amount Ref #</b></p>
  </div>
 <div class="col-md-4">
      
      <p class="head"><b>Extension Amount</b></p>
      <p class="para2">₹2431</p>
  </div>

<div class="col-md-4">
      
      <p class="head"><b>Extension Amount Status</b></p>
      <p class="para2">Extension Amount Paid</p>
      </div>
 </div>

  <p class="para2"><span class="para">Warning (2):</span> Invalid argument supplied for foreach() [APP/View/Bookingadmin_console_open.ctp, line 133]</p>
 
 </section>
 <section class="border mt-3">
 <form action="<?= base_path ?>add_team_process" method="post" enctype="multipart/form-data">
                                @csrf
 <div class="form-group row">
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                        <input class="form-control" type="text" value="" id="phone" name="phone" placeholder="phone no. (Optional)" required>
                                        <label for="phone">Odometer Reading</label>
                                        </div>
                                       
                                    </div>
                                    <!--</div>-->

                                    <!--<div class="form-group row">-->
                                    
                                    
                                <!--</div>
                                <div class="form-group row">-->
                                   
                                    <div class="col-sm-6">
                                    <div class="form-floating">
                                        <input class="form-control" type="text" value="" id="phone" name="phone" placeholder="phone no. (Optional)" required>
                                        <label for="phone">Admin Notes</label>
                                    </div>
                                    </div>
                                    </div>

 <h5><p class="para2">Checklist</p></h5>
 
 
                                <!-- <div class="form-check">] -->
                                   
                                <!-- <label class="form-label" for="power">Services &nbsp;<span style="color:red;">*</span></label> -->

                                   

                                      <!--  <div  class="form-check">
                                        <div class="form-check-inline">
                                            <label>
                                                <input type="checkbox" name="service" value="999">
                                                <span style="margin-left: 2px;">All</span>
                                            
                                                </label>
                                        </div>-->
                                        <!-- <?
                                        if (!empty($service_data)) {
                                            foreach ($service_data as $service) { ?>

                                                <div  class="form-check">
                                                    <label>
                                                        <input type="checkbox" name="services[]" value="<? echo $service->id; ?>">
                                                        <span style="margin-left: 2px;">
                                                            <? echo $service->name; ?>
                                                        </span>
                                                    </label>
                                                </div>

                                        <? }
                                        } ?> -->
                                         
<form action="/action_page.php">
   
              
            <div class="row">
                <div class="col-6 col-lg-12">

                    
                  <label class="form-check-label form-check-inline " for="check1">
                    <input type="checkbox" class="form-check-input" id="check1" name="vehicle1" value="something" >Documents
                  </label>
              


                  
                  <label class="form-check-label form-check-inline " for="check1">
                    <input type="checkbox" class="form-check-input" id="check1" name="vehicle1" value="something" >Key
                  </label>
                  

                  
                  <label class="form-check-label form-check-inline " for="check1">
                    <input type="checkbox" class="form-check-input" id="check1" name="vehicle1" value="something" >Spare Tyre
                  </label>
                  
                  
                  <label class="form-check-label form-check-inline " for="check1">
                    <input type="checkbox" class="form-check-input" id="check1" name="vehicle1" value="something" >Seat Cover 
                  </label>
                  
                  
                  
                  <label class="form-check-label form-check-inline " for="check1">
                    <input type="checkbox" class="form-check-input" id="check1" name="vehicle1" value="something" >Floor Mating
                  </label>
              
                  
                  <label class="form-check-label form-check-inline " for="check1">
                    <input type="checkbox" class="form-check-input" id="check1" name="vehicle1" value="something" >FasTag 
                  </label>

                </div>


                <div class="col-6  col-lg-12">
                  
                  <label class="form-check-label form-check-inline " for="check2">
                    <input type="checkbox" class="form-check-input" id="check2" name="vehicle2" value="something">Charger
                  </label>
              
                                  
                  
                  <label class="form-check-label form-check-inline ac" for="check2">
                    <input type="checkbox" class="form-check-input" id="check2" name="vehicle2" value="something">AC
                  </label>
        
                  
                  
                  <label class="form-check-label form-check-inline Tool_Kit" for="check2">
                    <input type="checkbox" class="form-check-input" id="check2" name="vehicle2" value="something">Tool Kit
                  </label>
                
                                          
                  
                  <label class="form-check-label form-check-inline First_Aid " for="check2">
                    <input type="checkbox" class="form-check-input" id="check2" name="vehicle2" value="something">First Aid Kit
                  </label>
                
                                    
                  
                  <label class="form-check-label form-check-inline Wipers" for="check2">
                    <input type="checkbox" class="form-check-input" id="check2" name="vehicle2" value="something">Wipers
                  </label>
                
      
                                    
                    
                    <label class="form-check-label form-check-inline Cleaniness" for="check2">
                      <input type="checkbox" class="form-check-input" id="check2" name="vehicle2" value="something">Cleaniness
                    </label>

                </div>

            </div>
            
  </form>

  
  
    <h5><p class="para2">Pre-Booking Image</p></h5>
    <div class="row">
      <div class="col-md-6 ">
      <button type="submit" class="btn" >Odometer Image</button> <hr>
      </div>
      <div class="col-md-6">
      <button type="submit" class="btn">Driver Image</button> <hr>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
      <button type="submit" class="btn">Vehicle Image</button> <hr>
      </div>
    </div>
 </form>
 </section>
</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">

$(document).ready(function(){
// $('#userTable').DataTable({
// responsive: true,
// // bSort: true
// });

$(document.body).on('click', '.dCnf', function() {
var i=$(this).attr("mydata");
console.log(i);

$("#btns"+i).hide();
$("#cnfbox"+i).show();

});

$(document.body).on('click', '.cans', function() {
var i=$(this).attr("mydatas");
console.log(i);

$("#btns"+i).show();
$("#cnfbox"+i).hide();
})

});

</script>
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
