<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Forwarding</title>
</head>

<body>

<form name="pa_form" action="https://www.paypal.com/cgi-bin/webscr" method="POST">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="business" value="<!--account-->">
<input type="hidden" name="undefined_quantity" value="0">
<input type="hidden" name="item_name" value="Live Chat Voucher">
<input type="hidden" name="amount" value="<!--price-->">
<input type="hidden" name="custom" value="<!--user_id-->">
<input type="hidden" name="invoice" value="<!--order_id-->">
<input type="hidden" name="charset" value="utf-8">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="tax" value="<!--tax-->">
<input type="hidden" name="currency_code" value="<!--currency-->">
<input type="hidden" name="return" value="<!--server-->chat.php?vc=<!--voucher_id--><!--co-->">
<input type="hidden" name="notify_url" value="<!--server-->checkout.php?confirm=1&amp;vc=<!--voucher_id--><!--co-->">
<input type="hidden" name="cancel_return" value="<!--server-->chat.php?cancel=1<!--co-->">
<input type="hidden" name="no_note" value="0">
</form>
<br><br><br><br><br><br><br>
<table class="box2" style="margin-bottom: 9px;" width="45%" cellspacing="0" cellpadding="0" align="center">
    <tr>
        <td class="box2_corner_top_left"></td>
		<td class="box2_top_middle"></td>
		<td class="box2_corner_top_right"></td>
    </tr>
    <tr>
        <td class="box2_middle_left"></td>
		<td class="box2_content">
			<table align="center">
				<tr>
					<td colspan="2" valign="top" align="center"><br><strong>Forwarding to PayPal</strong><br>Cookies must be activated<br><br></td>
				</tr>
			</table>
		</td>
		<td class="box2_middle_right"></td>
   	</tr>
  	 	<tr>
       	<td class="box2_corner_bottom_left"></td>
		<td class="box2_bottom_bottom"></td>
		<td class="box2_corner_bottom_right"></td>
   	</tr>
</table>
<script language="JavaScript">
setTimeout("document.getElementsByName('pa_form')[0].submit();",50);
var height = screen.height-50;
window.resizeTo(screen.width-(screen.width*0.2),screen.height-(height*0.2));
window.moveTo((screen.width*0.2)/2,(height*0.2)/2);
</script>
</body>
</html>
