
<table align="center" width="100%" id="lz_chat_extend_voucher_success" style="display:none;">
	<tr>
		<td align="center">
			<br><br><br><br><br><br><br><br><b><!--lang_client_voucher_extend_success--></b>
		</td>
	</tr>
</table>
<img id="lz_chat_com_voucher_pp" <!--pp_logo_url--> alt="" border="0" style="display:none;">
<div id="lz_chat_buy_voucher" style="display:none;overflow:auto;height:100%;">
	<table align="center" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center" valign="top">
                <table cellpadding="5" cellspacing="3" class="lz_input_header" id="lz_input_header_box">
                    <tr>
                        <td style="width:5px;"></td>
                        <td style="text-align:center;" id="lz_header_type_icon">
                            <img src="./images/chat_header_icon_cout.gif" alt="">
                        </td>
                        <td>
                            <table>
                                <tr>
                                    <td id="lz_header_title"><!--lang_client_voucher_select--></td>
                                </tr>
                                <tr>
                                    <td id="lz_form_info_field" class="lz_input_info_field"><!--lang_client_voucher_select_info--></td>
                                </tr>
                            </table>
                        </td>
                        <td></td>
                    </tr>
                </table>
                <br><br>
				<!--voucher_form-->
				<table width="430">
					<tr>
						<td colspan="3"></td>
					</tr>
					<tr>
						<td><table style="display:<!--show_VAT-->"><tr><td><!--VAT-->&nbsp;</td><td id="vat_amount"></td></tr></table></td>
						<td></td>
						<td align="right"><table cellpadding="0" cellspacing="0"><tr><td id="total_label" style="display:none;"><!--lang_client_voucher_total-->&nbsp;</td><td id="total_amount"></td></tr></table></td>
					</tr>
					<tr>
						<td colspan="3"><br></td>
					</tr>
				</table>
				<br><br><br>
				<table class="lz_chat_voucher_navigation">
					<tr>
						<td style="text-align:center;">
							<table align="center">
								<tr>
									<td><input type="button" id="lz_chat_checkout_cancel" value="<!--lang_client_back-->" onclick="parent.parent.lz_chat_buy_voucher_navigate('cancel',false);" class="lz_form_button"></td>
									<td>&nbsp;&nbsp;</td>
									<td><input type="button" value="<!--lang_client_next-->" onclick="parent.parent.lz_chat_buy_voucher_navigate('accept_tos',false);" id="proceed_to_details_button" class="lz_form_button" DISABLED></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>
<div id="lz_chat_checkout_tos" style="display:none;overflow:auto;height:100%;">
	<table align="center" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center" valign="top">
                <table cellpadding="5" cellspacing="3" class="lz_input_header" id="lz_input_header_box">
                    <tr>
                        <td style="width:5px;"></td>
                        <td style="text-align:center;" id="lz_header_type_icon">
                            <img src="./images/chat_header_icon_cout.gif" alt="">
                        </td>
                        <td>
                            <table>
                                <tr>
                                    <td id="lz_header_title"><!--lang_client_terms_of_service--></td>
                                </tr>
                                <tr>
                                    <td id="lz_form_info_field" class="lz_input_info_field"><!--lang_client_terms_of_service_info--></td>
                                </tr>
                            </table>
                        </td>
                        <td></td>
                    </tr>
                </table>
                <br><br>
				<table class="lz_input">
					<tr>
						<td colspan="2"><textarea class="lz_form_area" style="width:100%;height:265px;" id="lz_chat_buy_voucher_tos" cols="" rows="" READONLY></textarea><br><br></td>
					</tr>
					<tr>
						<td style="vertical-align:middle;width:20px;"><input type="checkbox" class="lz_form_check" value="" onchange="document.getElementById('lz_chat_accept_tos').disabled=!this.checked;"></td>
						<td style="vertical-align:middle;"><!--lang_client_terms_of_service_accept--></td>
					</tr>
				</table>
				<br><br><br>
				<table class="lz_chat_voucher_navigation">
					<tr>
                        <td style="text-align:center;">
							<table align="center">
								<tr>
									<td><input type="button" value="<!--lang_client_back-->" onclick="parent.parent.lz_chat_buy_voucher_navigate('voucher_select',false);" class="lz_form_button"></td>
									<td>&nbsp;&nbsp;</td>
									<td><input type="button" id="lz_chat_accept_tos" value="<!--lang_client_next-->" onclick="parent.parent.lz_chat_buy_voucher_navigate('enter_details',false);" class="lz_form_button" DISABLED></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</tr>
	</table>
</div>
<div id="lz_chat_checkout_details" style="display:none;overflow:auto;height:100%;">
	<form action="<!--server-->checkout.php" id="checkout_form" method="POST" target="_top">
		<input type="hidden" name="form_voucher_type" value="">
		<input type="hidden" name="form_currency" value="">
		<input type="hidden" name="form_total_price" value="0">
		<input type="hidden" name="form_vat" value="0">
		<input type="hidden" name="form_visitor_id" value="">
		<input type="hidden" name="form_extends" value="<!--extends_voucher-->">
		<input type="hidden" name="form_group" value="">
		<table align="center" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td align="center" valign="top">
                    <table cellpadding="5" cellspacing="3" class="lz_input_header" id="lz_input_header_box">
                        <tr>
                            <td style="width:5px;"></td>
                            <td style="text-align:center;" id="lz_header_type_icon">
                                <img src="./images/chat_header_icon_cout.gif" alt="">
                                </td>
                                <td>
                                    <table>
                                        <tr>
                                            <td id="lz_header_title"><!--lang_client_customer_information--></td>
                                        </tr>
                                        <tr>
                                            <td id="lz_form_info_field" class="lz_input_info_field"><!--lang_client_customer_information_info--></td>
                                        </tr>
                                    </table>
                                </td>
                                <td></td>
                            </tr>
                        </table>
                        <br><br>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_email-->:</strong></td>
                                <td align="right"><input type="text" name="form_email" value="<!--lp_email-->" maxlength="255" class="lz_form_box"></td>
                                <td align="right" width="16"><div class="lz_input_icon lz_required" id="lz_form_checkout_mandatory_email" style="display:none;"></div></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_company-->:</strong></td>
                                <td align="right"><input type="text" name="form_company" value="<!--lp_company-->" maxlength="255" class="lz_form_box"></td>
                                <td width="16"></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_first_name-->:</strong></td>
                                <td align="right"><input type="text" name="form_firstname" value="<!--lp_firstname-->" maxlength="255" class="lz_form_box"></td>
                                <td align="right" width="16"><div class="lz_input_icon lz_required" id="lz_form_checkout_mandatory_firstname" style="display:none;"></div></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_last_name-->:</strong></td>
                                <td align="right"><input type="text" name="form_lastname" value="<!--lp_lastname-->" maxlength="255" class="lz_form_box"></td>
                                <td align="right" width="16"><div class="lz_input_icon lz_required" id="lz_form_checkout_mandatory_lastname" style="display:none;"></div></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_phone-->:</strong></td>
                                <td align="right"><input type="input" name="form_phone" value="<!--lp_phone-->" maxlength="64" class="lz_form_box"></td>
                                <td width="16"></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_address_1-->:</strong></td>
                                <td align="right"><input type="input" name="form_address_1" value="<!--lp_address_1-->" maxlength="255" class="lz_form_box"></td>
                                <td align="right" width="16"><div class="lz_input_icon lz_required" id="lz_form_checkout_mandatory_address_1" style="display:none;"></div></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_address_2-->:</strong></td>
                                <td align="right"><input type="input" name="form_address_2" value="<!--lp_address_2-->" maxlength="255" class="lz_form_box"></td>
                                <td width="16"></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_city-->:</strong></td>
                                <td align="right"><input type="input" name="form_city" value="<!--lp_city-->" maxlength="127" class="lz_form_box"></td>
                                <td align="right" width="16"><div class="lz_input_icon lz_required" id="lz_form_checkout_mandatory_city" style="display:none;"></div></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_zip-->:</strong></td>
                                <td align="right"><input type="input" name="form_zip" value="<!--lp_zip-->" maxlength="64" class="lz_form_box"></td>
                                <td align="right" width="16"><div class="lz_input_icon lz_required" id="lz_form_checkout_mandatory_zip" style="display:none;"></div></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_state-->:</strong></td>
                                <td align="right"><input type="input" name="form_state" value="<!--lp_state-->" maxlength="127" class="lz_form_box"></td>
                                <td width="16"></td>
                            </tr>
                        </table>
                        <table class="lz_input" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="lz_form_field"><strong><!--lang_client_your_country-->:</strong></td>
                                <td style="text-align:right;"><select name="form_country" class="lz_form_box"><option value="">-- <!--lang_client_select_country--> --</option><!--countries--></select></td>
                                <td align="right" width="16"><div class="lz_input_icon lz_required" id="lz_form_checkout_mandatory_country" style="display:none;"></div></td>
                            </tr>
                        </table>
                        <br><br><br>
                        <table class="lz_chat_voucher_navigation">
                        <tr>
                            <td style="text-align:center;">
                                <table align="center">
                                    <tr>
                                        <td><input type="button" value="<!--lang_client_back-->" onclick="parent.parent.lz_chat_buy_voucher_navigate('accept_tos',true);" class="lz_form_button"></td>
                                        <td>&nbsp;&nbsp;</td>
                                        <td><input type="button" value="<!--lang_client_voucher_pay_now-->" onclick="parent.parent.lz_chat_buy_voucher_proceed_to_payment();" id="proceed_to_payment_button" class="lz_form_button"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
				</td>
			</tr>
		</table>
	</form>
</div>
