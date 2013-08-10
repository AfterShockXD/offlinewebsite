<div id="lz_chat_overlay_main" style="background-image:url('<!--server-->templates/overlays/chat/images/lz_chat_<!--shadow-->bg.png');">
	<div id="lz_chat_content">
		<table cellpadding="0" cellspacing="0" class="lz_chat_content_table">
			<tr>
				<td>
					<table cellpadding="0" cellspacing="0" style="height:25px;width:100%;background:<!--bgc-->;color:<!--tc-->;">
						<tr>
							<td style="width:235px;">
								<table class="lz_overlay_chat_gradient" cellpadding="0" cellspacing="0">
									<tr>
										<td id="lz_chat_overlay_text" style="color:<!--tc-->;" onclick="lz_chat_change_state(true,false);"></td>
									</tr>
								</table>
							</td>
							<td>
								<table class="lz_overlay_chat_gradient" cellpadding="0" cellspacing="0" style="cursor:pointer;width:100%;" onclick="lz_chat_change_state(true,true);">
									<tr>
										<td align="center" style="vertical-align:middle;text-align:left;">
										&nbsp;
											<div id="lz_chat_state_change" style="background:<!--tc-->;display:none;"></div>
											<div id="lz_chat_waiting_messages" style="display:none;"></div>
											
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="unmovable" style="height:252px;vertical-align:top;">
					<div id="lz_chat_loading"><br><br><br><br><br><!--lang_client_loading--> ...</div>
					<div id="lz_chat_content_box" style="display:none;" class="unmovable" onScroll="lz_chat_scroll();"><div id="lz_chat_content_inlay" class="unmovable"></div></div>
					<div id="lz_chat_overlay_options_box_bg" style="display:none;"></div>
					<div id="lz_chat_overlay_options_box" style="display:none;border-spacing:0px;">
                        <div class="lz_chat_overlay_options_box_base lz_chat_overlay_options_box_header" style="top:11px;left:15px;"><!--lang_client_options--></div>
                        <div class="lz_chat_overlay_options_box_base lz_chat_overlay_options_box_header" style="cursor:pointer;top:11px;right:22px;" onclick="lz_chat_switch_options(true);this.blur();"><strong>X</strong></div>
                        <div style="top:37px;left:15px;" class="lz_chat_overlay_options_box_base"><!--lang_client_your_name-->:</div>
                        <div style="top:53px;left:15px;" class="lz_chat_overlay_options_box_base"><input id="lz_chat_overlay_options_name" type="text" class="lz_chat_overlay_text lz_chat_overlay_options_text" value=""></div>
                        <div style="top:86px;left:15px;" class="lz_chat_overlay_options_box_base"><!--lang_client_your_email-->:</div>
                        <div style="top:102px;left:15px;" class="lz_chat_overlay_options_box_base"><input type="text" id="lz_chat_overlay_options_transcript" class="lz_chat_overlay_text lz_chat_overlay_options_text"></div>
                        <div style="top:145px;left:11px;width:185px;" class="lz_chat_overlay_options_box_base">
                            <div style="width:18px;top:5px;" class="lz_chat_overlay_options_box_base"><input type="checkbox" id="lz_chat_overlay_options_sound" value=""></div>
                            <div style="left:21px;top:8px;" class="lz_chat_overlay_options_box_base"><!--lang_client_sounds--></div>
                            <div style="right:0px;" class="lz_chat_overlay_options_box_base"><div class="lz_overlay_chat_button"><input type="button" value="<!--lang_client_save-->" onclick="lz_chat_switch_options(false);this.blur();"></div></div>
                        </div>
					</div>
					<div id="lz_chat_overlay_ticket" style="display:none;">
						<table id="lz_chat_ticket_received" cellpadding="0" cellspacing="0" align="center" style="width:90%;display:none;margin: 0 auto;">
							<tr>
								<td>
									<br><br>
									<table cellpadding="0" cellspacing="0" id="lz_ticket_received" align="center" class="lz_overlay_chat_ticket_response">
										<tr>
											<td>
												<!--lang_client_message_received-->
											</td>
										</tr>
									</table>
									<table cellpadding="0" cellspacing="0" id="lz_ticket_flood" align="center" class="lz_overlay_chat_ticket_response">
										<tr>
											<td style="color:#cc3333;">
												<strong><!--lang_client_message_flood--></strong>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<br>
									<div class="lz_overlay_chat_button"><input type="button" value="<!--lang_client_back-->" onclick="lz_chat_ticket_display(true);this.blur();"></div>
								</td>
							</tr>
						</table>
						<div id="lz_chat_ticket_form">

                            <div style="top:0px;left:15px;" class="lz_chat_overlay_options_box_base lz_overlay_chat_ticket_header">
                                <div><b><!--lang_client_ticket_header--></b><br><!--ticket_information--></div>
                            </div>

                            <div style="top:100px;left:15px;" class="lz_chat_overlay_options_box_base">
                                <span class="lz_overlay_chat_caption"><!--lang_client_your_name-->:</span><span id="lz_chat_overlay_ticket_required_name" class="lz_overlay_chat_required"><!--lang_client_required_field--></span>
                                <input id="lz_chat_overlay_ticket_name" type="text" onkeydown="lz_leave_message_required=true;" class="lz_chat_overlay_text">
                            </div>

                            <div style="top:144px;left:15px;" class="lz_chat_overlay_options_box_base">
                                <span class="lz_overlay_chat_caption"><!--lang_client_your_email-->:</span><span id="lz_chat_overlay_ticket_required_email" class="lz_overlay_chat_required"><!--lang_client_required_field--></span>
                                <input id="lz_chat_overlay_ticket_email" type="text" onkeydown="lz_leave_message_required=true;" class="lz_chat_overlay_text">
                            </div>

                            <div style="top:188px;left:15px;" class="lz_chat_overlay_options_box_base">
                                <span class="lz_overlay_chat_caption"><!--lang_client_your_question-->:</span><span id="lz_chat_overlay_ticket_required_message" class="lz_overlay_chat_required"><!--lang_client_required_field--></span>
                                <textarea id="lz_chat_overlay_ticket_message" type="text" onchange="lz_overlay_chat_impose_max_length(this, 1500);" onkeyup="lz_overlay_chat_impose_max_length(this, 1500);" onkeydown="lz_leave_message_required=true;" class="lz_chat_overlay_text" style="height:60px;"></textarea>
                            </div>


                            <div style="top:278px;left:15px;" class="lz_chat_overlay_options_box_base lz_overlay_chat_button"><input type="button" value="<!--lang_client_back-->" id="lz_chat_overlay_ticket_back_button" style="display:none;" onclick="lz_chat_message_return();this.blur();"></div>
                            <div style="top:278px;right:12px;" class="lz_chat_overlay_options_box_base lz_overlay_chat_button"><input type="button" value="<!--lang_client_send_message-->" id="lz_chat_overlay_ticket_button" onclick="lz_chat_send_ticket();this.blur();"></div>

                        </div>
					</div>
				</td>
			</tr>
			<tr>
				<td style="height:22px;text-align:center;vertical-align:middle;"><div id="lz_chat_overlay_info"></div></td>
			</tr>
			<tr>
				<td style="height:42px;text-align:center;vertical-align:top;">
                    <img src="<!--server-->images/chat_loading.gif" id="lz_bot_reply_loading" style="margin-top:5px;display:none;">
					<textarea onkeyup="if(event.keyCode==13){this.value='';return true;}else{lz_overlay_chat_impose_max_length(this, 1500);}"; onkeydown="if(event.keyCode==13){return lz_chat_message();}else{lz_chat_switch_extern_typing(true);return true;lz_overlay_chat_impose_max_length(this, 1500);}" id="lz_chat_text" class="lz_chat_overlay_text" style="height:33px;resize: none;"></textarea>
				</td>
			</tr>
			<tr>
				<td class="lz_overlay_chat_footer">
					<table cellpadding="0" style="width:96%;border-spacing:0px;" align="center">
						<tr>
							<td width="10" nowrap onclick="javascript:lz_chat_switch_options(false);" id="lz_overlay_chat_options_button" class="lz_overlay_chat_options_link"><!--lang_client_options--></td>
							<td width="10"></td>
							<td width="10" id="lz_chat_apo" onclick="javascript:lz_chat_pop_out();" class="lz_overlay_chat_options_link" style="font-weight:normal;<!--apo-->">PopOut</td>
							<td style="width:auto;text-align:right;">&nbsp;&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>
<a href="http://www.livezilla.net/" id="lz_chat_cl" style="<!--bcl-->display:none;background-image:url('<!--server-->templates/overlays/chat/images/lz_chat_cl_0.png');width:52px;" target="blank" onmouseover="this.style.backgroundImage='url(<!--server-->templates/overlays/chat/images/lz_chat_cl_1.png)';" onmouseout="this.style.backgroundImage='url(<!--server-->templates/overlays/chat/images/lz_chat_cl_0.png)';" title="LiveZilla Live Help Software"><div>&nbsp;</div></a>
	