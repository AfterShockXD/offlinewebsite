<input type="hidden" id="lz_form_active_<!--name-->" value="<!--active-->">
<table cellpadding="0" cellspacing="0" id="lz_form_<!--name-->" class="lz_input">
	<tr>

        <td id="lz_form_caption_<!--name-->" class="lz_form_field" style="background:transparent;text-align:right;">
            <input class="lz_form_check" name="form_<!--name-->" type="checkbox" onchange="return parent.parent.lz_save_input_value('<!--name-->',((this.checked) ? '1' : '0'));">
        </td>
        <td>&nbsp;&nbsp;&nbsp;</td>
        <td style="padding:3px 0px 3px 0px;"><!--caption--></td>
        <td align="right"><div id="lz_form_mandatory_<!--name-->" style="display:none;"></div></td>
            <div class="lz_form_info_box" id="lz_form_info_<!--name-->"><!--info_text--></div>
		</td>
	</tr>
</table>
