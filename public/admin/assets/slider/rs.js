/**

 Main JavaScript file for slider  
 Date : 15th May 2015 
 
 Author : Rishi Shrivastava
 
 */

$(document).ready(function(){
var btnUploadimage=$('#income_frm_rent_file');
		var namefileimage=$('#income_frm_rent_file').attr('name');
		new AjaxUpload(btnUploadimage, {
		    action: base_url+"admin/slider/image_upload/"+namefileimage,
            name:namefileimage,
            onSubmit: function(file, ext){
                if (! (ext && /^(jpg|png|jpeg|gif|doc|docx|xls|xlsx|ppt|txt|pdf)$/.test(ext))){ 
                    // extension is not allowed 
			status.text('Only JPG, PNG or GIF files are allowed');
                    return false;
                }
				//$("#login_img").show();
            },
            onComplete: function(file, response){
				//$("#login_img").hide();
                if(response!="error"){
        			 var obj = JSON.parse(response);
					//var obj=response;
					console.log(obj);
					console.log(obj['orig_name']);
					$("#slide_img").val(obj['orig_name']);
					$("#slide_img_path").attr("src",base_url+obj['new_name']);
					$("#slide_img_path").attr("height",50);
					$("#slide_img_path").attr("width",100);
					$("#slide_img_path").show();
					//$("#income_frm_rent_file").hide();
                } else{
                    //$('<li></li>').appendTo('#files').text(file).addClass('error');
                }
            }
        });
		
		//slide_frm
	
})
	function frmSbt(){
		var imgs=$("#slide_img").val();
		if(imgs==""){
			alert("Sorry Image Field is Required");
		return false;	
		}else{
		$("#slide_frm").submit();
		}
			
		}