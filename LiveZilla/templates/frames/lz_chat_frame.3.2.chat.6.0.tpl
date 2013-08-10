<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--html-->
<head>
	<META NAME="robots" CONTENT="noindex,follow">
	<title><!--config_gl_site_name--></title>
	<link rel="stylesheet" type="text/css" href="<!--server-->templates/style_chat.css">
</head>
<body>
	<div id="lz_chat_floor" style="display:none;">
	<div id="lz_chat_floor_contents">
		<form onSubmit="return false;" style="margin:0px;padding:0px;">
			<table width="98%" cellspacing="2" cellpadding="2" align="center">
				<tr>
					<td valign="top" width="100%">
						<textarea id="lz_chat_text" onkeydown="parent.parent.lz_switch_title_mode(false);if(event.keyCode==13){return parent.parent.lz_chat_message('','');}else{parent.parent.setTimeout('lz_chat_switch_extern_typing(true);',3000);return true;}"></textarea>
						<table cellspacing="0" cellpadding="0" id="lz_chat_subline" style="display:none;">
							<tr>
								<td width="20" height="11" style="background-image:url('./images/icon_talk.gif');background-repeat:no-repeat;background-position:0px 2px;"></td>
								<td id="lz_chat_operator_typing_info"></td>
							</tr>
						</table>
					</td>
					<td align="left" valign="top"><input type="button" id="lz_chat_submit" onclick="return parent.parent.lz_chat_message('','');" name="lz_send_button" value="" title="<!--lang_client_send-->"></td>
					<td width="12" valign="top">	
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td width="12" height="20"><img src="./images/button_rsfplus.gif" alt="" width="12" height="20" border="0" class="lz_chat_clickable_image" onClick="parent.parent.lz_chat_chat_resize_input(1);"></td>
							</tr>
							<tr>
								<td width="12" height="20"><img src="./images/button_rsfminus.gif" alt="" width="12" height="20" border="0" class="lz_chat_clickable_image" onClick="parent.parent.lz_chat_chat_resize_input(-1);"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</form>
	</div>
	</div><br><br><br><br><br>
</body>
</html>
