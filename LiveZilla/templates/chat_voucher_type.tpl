<div name="voucher_item" id="<!--id-->">
	<br>
	<table align="center" class="lz_chat_voucher_type">
		<tr>
			<td rowspan="2" width="30" style="vertical-align:top;"><input type="radio" id="vi_<!--id-->" name="ticket_type" value="" onclick="this.checked=true;parent.parent.lz_chat_buy_voucher_calculate(<!--vat_amount-->,'<!--currency-->','<!--price-->',<!--price_unformatted-->,'<!--id-->');"></td>
			<td class="lz_chat_ticket_title"><!--title--></td>
			<td width="10"></td>
			<td class="lz_chat_voucher_price"><!--price--></td>
			<td width="30"><!--currency--></td>
		</tr>
		<tr>
			<td colspan="4"><!--description--></td>
		</tr>
	</table>
	<input type="hidden" id="voucher_tos_<!--id-->" value="<!--terms-->">
	<script>document.getElementById("vi_<!--id-->").checked=false;</script>
</div>
