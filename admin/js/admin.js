// JavaScript Document

$(document).ready(function(){
	
	
  $("#btn_login").click(function(e){
    e.preventDefault();
	//console.log('hello');
	
	  var mydata = $("#frmlogin").serialize();
	 // console.log(mydata); 
		if ( $('#frmlogin').validate() )
        {
			$.ajax({
					url : 'ajax/ajax.checklogin.php',
					dataType:"json",
					data : mydata,
					type: "post",
					success : function(response) {
							//console.log(response.stats.type);
							if (response.type == "ok") {
									//$('#responsetxt').html("<div class='alert alert-success'>"+response.msg+"</div>");
									document.location="adminmain.php";
							} else {
							
									
									$('#responsetxt').html("<div class='alert alert-danger'>"+response.msg+"</div>");
							}
					}
			})
		}
  });
  
  
  
  
  
  
  
});