<input type="hidden" id="lz_form_active_<!--name-->" value="<!--active-->">
<table cellpadding="0" cellspacing="0" id="lz_form_<!--name-->" class="lz_input">
	<tr>
		<td id="lz_form_caption_<!--name-->" class="lz_form_field" valign="top"><!--caption--></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><textarea name="form_<!--name-->" class="lz_form_area" onchange="imposeMaxLength(this, <!--maxlength-->);parent.parent.lz_save_input_value('<!--name-->',this.value);" onkeyup="imposeMaxLength(this, <!--maxlength-->);parent.parent.lz_save_input_value('<!--name-->',this.value);" onkeydown="return parent.parent.lz_save_input_value('<!--name-->',this.value);"></textarea></td>
                    <td align="right"><div id="lz_form_mandatory_<!--name-->" style="display:none;"></div></td>
                </tr>
            </table>
            <div class="lz_form_info_box" id="lz_form_info_<!--name-->"><!--info_text--></div>
		</td>
	</tr>
</table>
<script type="text/javascript">
function imposeMaxLength(_object, _max)
{
	if(_object.value.length > _max)
		_object.value = _object.value.substring(0,_max);
}
</script>