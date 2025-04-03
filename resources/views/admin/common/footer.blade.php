     <footer class="footer">


     </footer>

     </div>


     <!-- ============================================================== -->
     <!-- End Right content here -->
     <!-- ============================================================== -->


     </div>
     <!-- END wrapper -->
     <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<!-- DataTables Buttons JavaScript -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#userTable').DataTable({
      responsive: true,
      dom: 'Bfrtip',
      buttons: [
        'copyHtml5',
        'excelHtml5',
        'csvHtml5',
        'pdfHtml5'
      ]
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

     <!-- jQuery  -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
     <!-- <script src="{{asset('admin/assets/js/jquery.min.js')}}"></script> -->
     <script src="{{asset('admin/assets/js/bootstrap.bundle.min.js')}}"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.3/js/bootstrap-colorpicker.min.js"></script>
     <script src="{{asset('admin/assets/js/metisMenu.min.js')}}"></script>
     <script src="{{asset('admin/assets/js/jquery.slimscroll.js')}}"></script>
     <script src="{{asset('admin/assets/js/waves.min.js')}}"></script>

     <script src="../plugins/jquery-sparkline/jquery.sparkline.min.js"></script>

     <!-- Peity JS -->
     <script src="../plugins/peity/jquery.peity.min.js"></script>

     <script src="../plugins/morris/morris.min.js"></script>
     <script src="../plugins/raphael/raphael-min.js"></script>

     <script src="{{asset('admin/assets/pages/dashboard.js')}}"></script>

     <!-- App js -->
     <script src="{{asset('admin/assets/js/app.js')}}"></script>
     <!-- <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script> -->

     <script>
       document.addEventListener('DOMContentLoaded', function() {
         // Get the input element and menu items
         var searchInput = document.getElementById('searchInput');
         var menuItems = document.querySelectorAll('#side-menu li');

         // Add an event listener to the search input
         searchInput.addEventListener('input', function() {
           var searchTerm = searchInput.value.toLowerCase();

           // Loop through menu items and show/hide based on the search term
           menuItems.forEach(function(menuItem) {
             var menuItemText = menuItem.innerText.toLowerCase();
             // Check if the menu item or its descendants contain the search term
             if (menuItemText.includes(searchTerm) || hasDescendantWithText(menuItem, searchTerm)) {
               menuItem.style.display = 'block';
             } else {
               menuItem.style.display = 'none';
             }
           });
         });

         // Function to check if an element or its descendants contain the search term
         function hasDescendantWithText(element, searchTerm) {
           var descendants = element.querySelectorAll('*');
           for (var i = 0; i < descendants.length; i++) {
             var descendantText = descendants[i].innerText.toLowerCase();
             if (descendantText.includes(searchTerm)) {
               return true;
             }
           }
           return false;
         }

       });
     </script>
     <script>
       function isNumberKey(evt) {
         var charCode = (evt.which) ? evt.which : evt.keyCode
         if (charCode > 31 && (charCode < 48 || charCode > 57)) {
           if (charCode == 46) {
             return true;
           } else {
             return false;
           }
         }
         return true;
       }
     </script>
     <script type="text/javascript">
       $('.color').colorpicker({});

       // const pickr1 = new Pickr({
       //   el: '#color-picker-1',
       //   default: "303030",
       //   components: {
       //     preview: true,
       //     opacity: true,
       //     hue: true,
       //
       //     interaction: {
       //       hex: true,
       //       rgba: true,
       //       hsla: true,
       //       hsva: true,
       //       cmyk: true,
       //       input: true,
       //       clear: true,
       //       save: true
       //     }
       //   }
       // });
     </script>

     </body>

     </html>