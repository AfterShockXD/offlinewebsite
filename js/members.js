// JavaScript Document// JavaScript Document

$(document).ready(function(){
	
	
  $("#btn_login").click(function(e){
    e.preventDefault();
	//console.log('hello');
	
	  var mydata = $("#frmloginmem").serialize();
	 // console.log(mydata); 
		if ( $('#frmloginmem').validate() )
        {
			$.ajax({
					url : 'ajax/ajax.checkloginmem.php',
					dataType:"json",
					data : mydata,
					type: "post",
					success : function(response) {
							//console.log(response.stats.type);
							if (response.type == "ok") {
									//$('#responsetxt').html("<div class='alert alert-success'>"+response.msg+"</div>");
									document.location="index.php";
							} else {
							
									
									$('#responsetxt').html("<div class='alert alert-danger'>"+response.msg+"</div>");
							}
					}
			})
		}
  });
  
  
  
  
  
  
  
});