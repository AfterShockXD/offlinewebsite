<input type="hidden" id="lz_form_active_<!--name-->" value="<!--active-->">
<table cellpadding="0" cellspacing="0" id="lz_form_<!--name-->" class="lz_input">
	<tr>
		<td id="lz_form_caption_<!--name-->" class="lz_form_field"><!--caption--></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><input name="form_<!--name-->" class="lz_form_box" maxlength="254" onchange="parent.parent.lz_save_input_value('<!--name-->',this.value);" onkeyup="return parent.parent.lz_save_input_value('<!--name-->',this.value);"></td>
					<td align="right"><div id="lz_form_mandatory_<!--name-->" style="display:none;"></div></td>
				</tr>
			</table>
            <div class="lz_form_info_box" id="lz_form_info_<!--name-->"><!--info_text--></div>
		</td>
	</tr>
</table>