function lz_chat_show_waiting_message()
{
	if(lz_chat_data.Status.Status != lz_chat_data.STATUS_ACTIVE && lz_chat_data.Status.Status != lz_chat_data.STATUS_CLOSED && lz_chat_data.Status.Status != lz_chat_data.STATUS_STOPPED && !lz_chat_data.WaitingMessageAppended)
	{
		if(!lz_chat_data.CallMeBackMode)
			lz_chat_add_system_text(5,"");
		lz_chat_data.WaitingMessageAppended = true;
	}
}

function lz_chat_change_voucher_init()
{
	lz_chat_chat_alert(lz_chat_data.ComChatVoucherChangeHTML,lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0',''),lz_chat_change_voucher_proceed,null,false);
}

function lz_chat_change_voucher_proceed()
{
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_alert_box').style.display = 'none';
	for (var v in lz_chat_data.ComChatVouchers)
		for(var radio in lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','').document.getElementsByName("voucher_select"))
			if(lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','').document.getElementsByName("voucher_select")[radio].checked)
				if(lz_chat_data.ComChatVouchers[v].Id == lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','').document.getElementsByName("voucher_select")[radio].value)
					lz_chat_data.ComChatVoucherActive = lz_chat_data.ComChatVouchers[v];
}


function lz_chat_set_signature(_userId)
{			
	lz_chat_data.ExternalUser.Session.UserId = _userId;
	lz_chat_data.ExternalUser.Session.Save();
}

function lz_chat_buy_voucher_validate_form()
{
	var fields = {email:"email",firstname:"firstname",lastname:"lastname",address_1:"address_1",city:"city",zip:"zip"};
	var missing = false;
	for(fieldid in fields)
	{
		lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0',"lz_form_checkout_mandatory_" + fields[fieldid]).style.display = (lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName("form_" + fields[fieldid])[0].value.length > 0) ? "none" : "";
		if(lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName("form_" + fields[fieldid])[0].value.length == 0)
			missing = true;
	}
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0',"lz_form_checkout_mandatory_country").style.display = (lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName("form_country")[0].selectedIndex > 0) ? "none" : "";
	if(lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName("form_country")[0].selectedIndex == 0)
	{
		missing = true;
	}
	
	if(missing)
		lz_chat_chat_alert(lz_chat_data.Language.FillMandatoryFields,lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0',''),null,null,null,true);
	return !missing;
}

function lz_chat_buy_voucher_proceed_to_payment()
{
	if(lz_chat_buy_voucher_validate_form())
	{
		lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','proceed_to_payment_button').disabled = true;
		lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','checkout_form').submit();
	}
}

function lz_chat_buy_voucher_calculate(_vat_amount,_currency,_priceFormatted,_priceUnformatted,_vouchertype)
{
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','vat_amount').innerHTML= "(" + _vat_amount + '&nbsp;' + _currency+")";
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','total_amount').innerHTML = "<b>" + _priceFormatted + '</b>&nbsp;' + _currency + "&nbsp;&nbsp;";
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','total_label').style.display = '';
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','proceed_to_details_button').disabled = false;
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName('form_total_price')[0].value = _priceUnformatted;
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName('form_vat')[0].value = _vat_amount;
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName('form_currency')[0].value = _currency;
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName('form_voucher_type')[0].value = _vouchertype;
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName('form_group')[0].value = lz_chat_data.SelectedGroup.Id;
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName('form_visitor_id')[0].value = lz_chat_data.ExternalUser.Session.UserId;
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_buy_voucher_tos').value = lz_global_base64_url_decode(lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','voucher_tos_' + _vouchertype).value);
}

function lz_chat_buy_voucher_navigate(_target,_reverse)
{
	if("<!--checkout_url-->" != "")
	{
		window.open("<!--checkout_url-->");
		return;
	}

	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','proceed_to_payment_button').disabled = false;
	for(var i=0;i<lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName("voucher_item").length;i++)
	{
		var found = false;
		for(var x=0;x<lz_chat_data.SelectedGroup.ChatVouchersRequired.length;x++)
			if(lz_chat_data.SelectedGroup.ChatVouchersRequired[x] == lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName("voucher_item")[i].id)
			{
				found = true;
				break;
			}
		lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','').document.getElementsByName("voucher_item")[i].style.display = (found) ? "" : "none";
	}

	if(_target == "cancel" && !lz_chat_data.CheckoutOnly)
		_target = "start_chat";
	else if(_target == "cancel")
		_target = "voucher_select";

	if(_target == "accept_tos" && lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_buy_voucher_tos').value.length==0)
	{
		if(_reverse)
			_target = "voucher_select";
		else
			_target = "enter_details";
	}

	lz_chat_data.CheckoutActive = _target != "start_chat";

	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_extend_voucher_success').style.display = (_target == "voucher_extend_success") ? "" : "none";
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_login').style.display = (_target == "start_chat") ? "" : "none";
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_buy_voucher').style.display = (_target == "voucher_select") ? "" : "none";
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_checkout_details').style.display = (_target == "enter_details") ? "" : "none";
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_checkout_tos').style.display = (_target == "accept_tos") ? "" : "none";
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_checkout_cancel').style.display = (!lz_chat_data.CheckoutOnly) ? "" : "none";
	lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_com_voucher_pp').style.display = (_target == "start_chat") ? "none" : ((lz_chat_get_frame_object('lz_chat_frame.3.2.lgin.1.0','lz_chat_com_voucher_pp').src.length > 0) ? "" : "none");
}

function lz_chat_initiate_forwarding(_group)
{
	if(lz_chat_data.QueueMessageAppended)
	{
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_queue_position').id = "oq_a" + lz_global_timestamp();
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_queue_waiting_time').id = "oq_b" + lz_global_timestamp();
		lz_chat_data.QueueMessageAppended = false
	}

	lz_chat_data.ConnectedMessageAppended = false;
	lz_chat_data.WaitingMessageAppended = false;
	lz_chat_set_host('','','','',false,false,'');
	
	if(lz_chat_data.FileUpload.Running)
		lz_chat_file_stop();

	lz_chat_add_system_text(0,null);
	lz_chat_set_intern_image(0,'',false);
	lz_chat_set_group(_group);
	lz_chat_set_status(lz_chat_data.STATUS_ALLOCATED);
}

function lz_chat_message(_translation,_original) 
{
	if(_original=='')
		var msg = lz_chat_get_frame_object('lz_chat_frame.3.2.chat.6.0','lz_chat_text').value;
	if(_original != '')
		msg = _original;

	if(lz_chat_data.Status.Status == lz_chat_data.STATUS_STOPPED)
		return lz_chat_chat_alert(lz_chat_data.Language.RepresentativeLeft,lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0',''),null);
	else if(msg.length > 0 && lz_global_trim(msg).length > 0 && msg.length <= lz_chat_data.MAXCHATLENGTH)
	{
		var sendmsg = true;
		if(lz_chat_get_frame_object('lz_chat_frame.3.2.chat.7.0','lz_translation_service_active').checked && _original=='' && isNaN(msg))
		{
			if(!lz_chat_data.InternalUser.Available)
			{
				lz_chat_chat_alert(lz_chat_data.Language.WaitForRepresentative,lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0',''),null);
				return false;
			}		
			
			var transfrom = lz_chat_get_frame_object('lz_chat_frame.3.2.chat.7.0','lz_chat_translation_target_language').options[lz_chat_get_frame_object('lz_chat_frame.3.2.chat.7.0','lz_chat_translation_target_language').selectedIndex].value;
			var transinto = lz_chat_data.InternalUser.Language;
			sendmsg = transfrom == transinto;
			if(!sendmsg)
			{
				var newScript = document.createElement('script');
				newScript.type = 'text/javascript';
				var sourceText = escape(msg);
				
				window.doneTranslateCallback = function translateText(response){lz_chat_message(response.data.translations[0].translatedText,msg);window.doneTranslateCallback=null;}
				var source = "https://www.googleapis.com/language/translate/v2?key=<!--gtv2_api_key-->&format=html&source="+transfrom+"&target="+transinto+"&callback=doneTranslateCallback&q=" + msg;
				newScript.src = source;
				lz_document_head.appendChild(newScript);
			}
		}
		if(sendmsg)
		{
			var message = new lz_chat_post();
			message.MessageText = msg;
			message.MessageTranslation = _translation;
			message.MessageId = lz_chat_data.MessageCount++;
			lz_chat_data.ExternalUser.Typing = false;
			lz_chat_data.ExternalUser.PreMessage = null;
			lz_chat_add_extern_text(msg,_translation,true);
			lz_chat_data.ExternalUser.MessagesSent[lz_chat_data.ExternalUser.MessagesSent.length] = message;
			lz_chat_shout(10);
		}
	}
	else if(msg.length > lz_chat_data.MAXCHATLENGTH)
		return lz_chat_chat_alert(lz_chat_data.Language.MessageTooLong,lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0',''),null);
	
	setTimeout("lz_chat_clear_field()",1);
	return false;
}

function lz_chat_repost_from_queue(_post)
{
	if(!lz_chat_data.QueuePostsAdded)
	{
		var message = new lz_chat_post();
		message.MessageText = lz_global_base64_url_decode(_post);
		message.MessageId = lz_chat_data.MessageCount++;

		lz_chat_add_extern_text("<i>" + lz_global_base64_url_decode(_post) + "</i>","",false);
		lz_chat_data.ExternalUser.MessagesSent[lz_chat_data.ExternalUser.MessagesSent.length] = message;
	}
}

function lz_chat_clear_field()
{
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.6.0','lz_chat_text').value = "";
	lz_chat_focus_textbox();
}

function lz_chat_focus_textbox()
{
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.6.0','').focus();
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.6.0','lz_chat_text').focus();
}

function lz_chat_chat_alert_move()
{
	if(lz_chat_data.ActiveAlertFrame != null && lz_chat_data.ActiveAlertFrame.document.getElementById('lz_chat_alert_box').style.display == 'inline')
		lz_chat_chat_alert_set_position(lz_chat_data.ActiveAlertFrame);
}

function lz_chat_chat_alert_set_position(_frame)
{
	if(_frame == null)
		_frame = lz_chat_data.ActiveAlertFrame;
		
	if(lz_chat_get_frame_object('','lz_chat_frameset_chat') != null)
		var frame_rows = lz_chat_get_frame_object('','lz_chat_frameset_chat').rows.split(",");
	else
		var frame_rows = new Array(0,0,0,0,0);

	var top = (_frame.document.body.clientHeight-_frame.document.getElementById('lz_chat_alert_box').offsetHeight-frame_rows[2]-frame_rows[3]) / 2;
	var left = (_frame.document.body.scrollWidth-_frame.document.getElementById('lz_chat_alert_box').offsetWidth+20) / 2;

	_frame.document.getElementById('lz_chat_alert_box').style.top = top + _frame.document.body.scrollTop;
	_frame.document.getElementById('lz_chat_alert_box').style.left = left;
}

function lz_chat_chat_alert(_text,_frame,_event,_buttonText,_showIcon,_shadow)
{
    if(_shadow)
        _frame.document.getElementById('lz_chat_alert_bg').style.display = 'block';

	_frame.document.getElementById('lz_chat_alert_box_text').innerHTML = _text;
	_frame.document.getElementById('lz_chat_alert_box').style.display = 'inline';
	_frame.document.getElementById("lz_chat_alert_button").onclick = function(){_frame.document.getElementById('lz_chat_alert_bg').style.display = 'none';_frame.document.getElementById('lz_chat_alert_box').style.display = 'none';};
	
	if(_event == -1)
		_frame.document.getElementById('lz_chat_alert_button').disabled = true;
	else if(_event != null)
    {
        _frame.document.getElementById('lz_chat_alert_button').onclick = _event;
        _frame.document.getElementById('lz_chat_alert_button').onmouseup = function(){_frame.document.getElementById('lz_chat_alert_bg').style.display = 'none';_frame.document.getElementById('lz_chat_alert_box').style.display = 'none';};
    }


	if(_buttonText != null)
		_frame.document.getElementById('lz_chat_alert_button').value = _buttonText;
    else
        _frame.document.getElementById('lz_chat_alert_button').value = "Ok";
		
	if(_showIcon != null)
		_frame.document.getElementById('lz_chat_alert_icon').style.display = "none";
	else
		_frame.document.getElementById('lz_chat_alert_icon').style.display = "";
		
	lz_chat_data.ActiveAlertFrame = _frame;
	lz_chat_chat_alert_set_position();
}

function lz_chat_take_smiley(_smiley)
{
	var sign = "";
	switch(_smiley)
	{
		case"smile":sign = ":-)";break;
		case"sad":sign = ":-(";break;
		case"neutral":sign = ":-|";break;
		case"tongue":sign = ":-P";break;
		case"cry":sign = ":'-(";break;
		case"lol":sign = ":-]";break;
		case"shocked":sign = ":-O";break;
		case"wink":sign = ";-)";break;
		case"cool":sign = "8-)";break;
		case"sick":sign = ":-\\\\";break;
		case"question":sign = ":?";break;
		case"sleep":sign = "zzZZ";break;
	}
	lz_chat_switch_smiley_box();
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.6.0','lz_chat_text').value += sign;
	lz_chat_focus_textbox();
}

function lz_chat_set_room_member(_id,_fullname)
{
	_id = lz_global_base64_decode(_id);
	
	if(lz_array_indexOf(lz_chat_data.MembersPrevious,_fullname) == -1)
		lz_chat_add_system_text(1,_fullname);

	if(lz_array_indexOf(lz_chat_data.Members,_fullname) == -1)
		lz_chat_data.Members[lz_chat_data.Members.length] = _fullname;
}

function lz_chat_call_back_info(_html)
{
    lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_call_me_back_st').innerHTML = lz_global_base64_decode(_html);
}

function lz_chat_set_host(_id,_fullname,_groupname,_language,_typing,_vcard,_dygroup)
{
	if(_id.length > 0)
	{
		lz_chat_data.InternalUser.Id = (lz_global_base64_decode(_id));
		lz_chat_data.InternalUser.Fullname = (_fullname.length > 0) ? (lz_global_base64_decode(_fullname)) : lz_chat_data.InternalUser.Id;
		lz_chat_data.InternalUser.Language = _language;
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_internal_fullname').innerHTML = lz_chat_data.InternalUser.Fullname;
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_internal_groupname').innerHTML = (lz_global_base64_decode(_groupname));
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_vcard_image').href = 
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_vcard_text').href = "./visitcard.php?intid=" + encodeURIComponent(lz_chat_data.InternalUser.Id);
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.6.0','lz_chat_operator_typing_info').innerHTML = lz_chat_data.Language.RepresentativeIsTyping.replace("<!--operator_name-->",lz_chat_data.InternalUser.Fullname);
	}
	
	if((lz_chat_data.InternalUser.Available && _id.length == 0) || _id.length > 0)
	{
		for(var i=0;i<lz_chat_data.MembersPrevious.length;i++)
		{
			if(lz_array_indexOf(lz_chat_data.Members,lz_chat_data.MembersPrevious[i]) == -1 && _fullname != lz_chat_data.MembersPrevious[i])
				lz_chat_add_system_text(10,lz_chat_data.MembersPrevious[i]);
		}
		lz_chat_data.MembersPrevious = lz_chat_data.Members;
		lz_chat_data.Members = new Array();
	}

	if(lz_global_base64_decode(_dygroup) == '' && _dygroup != lz_chat_data.DynamicGroup && lz_chat_data.DynamicGroup != '')
	{
		lz_chat_add_system_text(12,lz_chat_data.DynamicGroup);
		lz_chat_data.DynamicGroup = '';
	}

	if(_dygroup != '' && _dygroup != lz_chat_data.DynamicGroup)
	{
		lz_chat_add_system_text(11,_dygroup);
		lz_chat_data.DynamicGroup = _dygroup;
	}

	lz_chat_data.InternalUser.Available = (_id.length > 0);
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.6.0','lz_chat_subline').style.display = (_id.length > 0 && _typing) ? "" : "none";
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_vcard_box').style.display = (_id.length > 0 && _vcard) ? "" : "none";
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_top_bg').style.visibility = (_id.length == 0) ? "visible" : "hidden";
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_representative').style.visibility = (_id.length > 0) ? "visible" : "hidden";
}

function lz_chat_set_intern_image(_edited,_file,_filtered)
{
	if(_edited == 0 || _filtered)
	{
		lz_chat_data.TempImage.src = "./images/nopic.jpg";
	}
	else if(_edited != lz_chat_data.InternalUser.ProfilePictureTime)
	{
		lz_chat_data.TempImage.src = "./" + _file;
		lz_chat_data.InternalUser.ProfilePictureTime = _edited;
	}
}

function lz_chat_show_intern_image()
{
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_intern_image').src = lz_chat_data.TempImage.src;
	if(lz_chat_data.TempImage.height > 0)
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_pic').style.width = (lz_chat_data.TempImage.width > 80) ? lz_chat_data.TempImage.width : 80;
}	

function lz_chat_switch_extern_typing(_typing)
{	
	var announce = (_typing != lz_chat_data.ExternalUser.Typing);
	if(_typing)
	{
		if(lz_chat_data.TimerTyping != null)
			clearTimeout(lz_chat_data.TimerTyping);
		lz_chat_data.TimerTyping = setTimeout("lz_chat_switch_extern_typing(false);",5000);
		lz_switch_title_mode(false);
	}
	
	lz_chat_data.ExternalUser.Typing = _typing;
	lz_chat_data.ExternalUser.PreMessage = lz_chat_get_frame_object('lz_chat_frame.3.2.chat.6.0','lz_chat_text').value;
	
	if(announce)
		lz_chat_shout(11);
}

function lz_chat_show_connected()
{
	if(!lz_chat_data.ConnectedMessageAppended)
	{
		lz_chat_data.ConnectedMessageAppended = true;
		lz_chat_add_system_text(6,null);
	}
}

function lz_chat_show_queue_position(_position,_time)
{
	if(!lz_chat_data.QueueMessageAppended)
	{
		var qmessage = lz_chat_data.Language.QueueMessage.replace("<!--queue_position-->","<span id='lz_chat_queue_position'>-1</span>");
		qmessage = qmessage.replace("<!--queue_waiting_time-->","<span id='lz_chat_queue_waiting_time'>-1</span>");
		lz_chat_add_system_text(-1,qmessage);
		lz_chat_data.QueueMessageAppended = true;
	}
	
	var cposition = parseInt(lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_queue_position').innerHTML);
	var cwtime = parseInt(lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_queue_waiting_time').innerHTML);
	
	if(_position == 1 && (cposition != _position))
		lz_chat_add_system_text(9,"");
		
	if(cposition == -1 || (_position > 0 && _position <= cposition))
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_queue_position').innerHTML = _position;
		
	if(cwtime == -1 || (_time > 0 && _time <= cwtime))
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_queue_waiting_time').innerHTML = _time;
}

function lz_chat_add_system_text(_type,_texta) 
{	
	var text = "";
	if(_type == 0)
		text = lz_chat_data.Language.ClientForwarding;
	else if(_type == 1)
		text = lz_chat_data.Language.ClientInternArrives.replace("<!--intern_name-->",(lz_global_base64_decode(_texta)));
	else if(_type == 2)
		text = lz_chat_data.Language.ClientErrorUnavailable + (lz_global_base64_decode(_texta));
	else if(_type == 3)
		text = lz_chat_data.Language.ClientIntLeft + " <a href=\"javascript:parent.parent.lz_chat_goto_message(true,false);\"><b>" + lz_chat_data.Language.LanguageLeaveMessageShort + "</b></a>. " + lz_chat_data.Language.ClientThankYou;
	else if(_type == 4)
		text = lz_chat_data.Language.ClientIntDeclined + " <a href=\"javascript:parent.parent.lz_chat_goto_message(true,false);\"><b>" + lz_chat_data.Language.LanguageLeaveMessageShort + "</b></a>. " + lz_chat_data.Language.ClientThankYou;
	else if(_type == 5)
		text = lz_chat_data.Language.ClientStillWaitingInt + " <a href=\"javascript:parent.parent.lz_chat_goto_message(true,false);\"><b>" + lz_chat_data.Language.LanguageLeaveMessageShort + "</b></a>. " + lz_chat_data.Language.ClientThankYou;
	else if(_type == 6)
		text = lz_chat_data.Language.ClientIntIsConnected;
	else if(_type == 8)
		text = lz_chat_data.Language.ClientNoInternUsers + " <a href=\"javascript:parent.parent.lz_chat_goto_message(true,false);\"><b>" + lz_chat_data.Language.LanguageLeaveMessageShort + "</b></a>. " + lz_chat_data.Language.ClientThankYou;
	else if(_type == 9)
		text = lz_chat_data.Language.NextOperator;
	else if(_type == 10)
		text = lz_chat_data.Language.ClientInternLeft.replace("<!--intern_name-->",(lz_global_base64_decode(_texta)));
	else if(_type == 11)
		text = lz_chat_data.Language.JoinGroup.replace("<!--group_name-->",(lz_global_base64_decode(_texta)));
	else if(_type == 12)
		text = lz_chat_data.Language.LeaveGroup.replace("<!--group_name-->",(lz_global_base64_decode(_texta)));
	else if(_type == 99)
		text = lz_global_base64_decode(_texta);
	else
		text = _texta;
		
	text = lz_global_replace_smilies(text,true);

	if(lz_chat_data.LastSender != lz_chat_data.SYSTEM)
	{
		text = lz_global_base64_decode(lz_chat_data.Templates.MessageExternal).replace("<!--message-->",text);
		lz_chat_data.AlternateRow = true;
	}
	else
	{
		if(lz_chat_data.AlternateRow)
			text = lz_global_base64_decode(lz_chat_data.Templates.MessageAddAlt).replace("<!--message-->",text);
		else
			text = lz_global_base64_decode(lz_chat_data.Templates.MessageAdd).replace("<!--message-->",text);
		lz_chat_data.AlternateRow = !lz_chat_data.AlternateRow;
	}

	text = text.replace("<!--time-->",lz_chat_get_locale_time());
	text = text.replace("<!--name-->",lz_chat_data.Language.System);
	lz_chat_append_text(text);
	lz_chat_data.LastSender = lz_chat_data.SYSTEM;
}

function lz_chat_add_internal_text(_text,_acid,_internalName,_repost) 
{
	_text = lz_global_base64_decode(_text);
	_acid = lz_global_base64_decode(_acid);

	var message = new lz_chat_post();
	var template = (_repost) ? lz_chat_data.Templates.MessageExternal : lz_chat_data.Templates.MessageInternal;
	message.MessageId = _acid;
	message.MessageText = _text;
	message.MessageTime = lz_global_timestamp();

	if(!lz_chat_message_is_received(message))
	{
        lz_chat_data.MessageCountReceived++;

        if(!lz_chat_data.ChatActive && lz_chat_data.MessageCountReceived>=2)
            lz_chat_activate();

        if(_repost)
            _text = "<i>" + _text + "</i>";

        if(lz_chat_data.LastSender != lz_chat_data.INTERNAL+_internalName)
        {
            _text = lz_global_base64_decode(template).replace("<!--message-->",_text);
            lz_chat_data.AlternateRow = true;
        }
        else
        {
            if(lz_chat_data.AlternateRow)
                _text = lz_global_base64_decode(lz_chat_data.Templates.MessageAddAlt).replace("<!--message-->",_text);
            else
                _text = lz_global_base64_decode(lz_chat_data.Templates.MessageAdd).replace("<!--message-->",_text);
            lz_chat_data.AlternateRow = !lz_chat_data.AlternateRow;
        }

		if(!_repost)
			_text = _text.replace("<!--time-->",lz_chat_get_locale_time());
		else
			_text = _text.replace("<!--time-->","");
			
		if(lz_global_base64_decode(lz_chat_data.DynamicGroup) != '')
			_text = _text.replace("<!--name-->",lz_global_base64_decode(_internalName) + " [" + lz_global_base64_decode(lz_chat_data.DynamicGroup) + "]");
		else
			_text = _text.replace("<!--name-->",lz_global_base64_decode(_internalName));
		
		_text = lz_global_replace_smilies(_text,true);

		lz_switch_title_mode(true);
		lz_chat_append_text(_text);
		
		if(lz_chat_data.LastSound < (lz_global_timestamp()-1))
		{
			lz_chat_data.LastSound = lz_global_timestamp();
			lz_chat_play_sound();
		}
		lz_chat_data.LastSender = lz_chat_data.INTERNAL+_internalName;
	}
}

function lz_chat_message_is_received(_message)
{
	for(var mIndex in lz_chat_data.ExternalUser.MessagesReceived)
		if(lz_chat_data.ExternalUser.MessagesReceived[mIndex].MessageId == _message.MessageId)
			return true;
			
	lz_chat_data.ExternalUser.MessagesReceived[lz_chat_data.ExternalUser.MessagesReceived.length] = _message;
	lz_chat_shout(12);
	
	var mNewMessages = new Array();
	for(var mIndex in lz_chat_data.ExternalUser.MessagesReceived)
		if(lz_chat_data.ExternalUser.MessagesReceived[mIndex].MessageTime >= (lz_global_timestamp()-3600))
			mNewMessages[mNewMessages.length] = lz_chat_data.ExternalUser.MessagesReceived[mIndex];

	lz_chat_data.ExternalUser.MessagesReceived = mNewMessages;
	return false;
}

function lz_chat_message_set_received(_id)
{
	for(var mIndex in lz_chat_data.ExternalUser.MessagesReceived)
		if(lz_chat_data.ExternalUser.MessagesReceived[mIndex].MessageId == _id)
			return (lz_chat_data.ExternalUser.MessagesReceived[mIndex].Received = true);
	return true;
}

function lz_chat_set_id(_id)
{
	lz_chat_data.Id = _id;
	return true;
}

function lz_chat_add_extern_text(_text,_translation,_escape) 
{
	if(_escape)
		_text = lz_global_htmlentities(_text);
	_text = lz_global_replace_breaks(_text);

	if(_translation != '')
	{
		_translation = lz_global_htmlentities(_translation);
		_translation = lz_global_replace_breaks(_translation);
		_text = _translation + "<div class=\"lz_message_translation\">"+_text+"</div>";
	}
	
	if(lz_chat_data.LastSender != lz_chat_data.EXTERNAL)
	{
		_text = lz_global_base64_decode(lz_chat_data.Templates.MessageExternal).replace("<!--message-->",_text);
		lz_chat_data.AlternateRow = true;
	}
	else
	{
		if(lz_chat_data.AlternateRow)
			_text = lz_global_base64_decode(lz_chat_data.Templates.MessageAddAlt).replace("<!--message-->",_text);
		else
			_text = lz_global_base64_decode(lz_chat_data.Templates.MessageAdd).replace("<!--message-->",_text);
		lz_chat_data.AlternateRow = !lz_chat_data.AlternateRow;
	}
	_text = lz_global_replace_smilies(_text,true);
	_text = _text.replace("<!--time-->",lz_chat_get_locale_time());
	
	if(lz_global_base64_decode(lz_chat_data.DynamicGroup) != '')
		_text = _text.replace("<!--name-->",lz_global_htmlentities(lz_chat_data.ExternalUser.Username + " [" + lz_global_base64_decode(lz_chat_data.DynamicGroup) + "]"));
	else
		_text = _text.replace("<!--name-->",lz_global_htmlentities(lz_chat_data.ExternalUser.Username));
		
	_text = _text.replace("<!--align-->",lz_chat_data.ExternalUser.TextAlign);
	
	lz_chat_append_text(_text);
	lz_chat_data.LastSender = lz_chat_data.EXTERNAL;
}

function lz_chat_append_text(_html)
{
	window.focus();
	if(!lz_chat_data.Status.Loaded)
		return;

	var newMessage = lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','').document.createElement("DIV");
	newMessage.innerHTML = _html;
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','lz_chat_main').appendChild(newMessage);

    var links = newMessage.getElementsByTagName("a");
    if(links.length > 0)
        for (link in links)
            if(links[link].target=="")
                links[link].target = "_blank";

	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','').scrollTo(0,100000);
	if(lz_chat_data.Status.Status < lz_chat_data.STATUS_STOPPED)
		lz_chat_focus_textbox();
}

function lz_chat_print() 
{
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','').focus();
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0','').print();
}

function lz_chat_release_post(_id)
{	
	if(lz_chat_data.Status.Status < lz_chat_data.STATUS_STOPPED)
	{
		newMessageList = new Array();
		for (var mIndex in lz_chat_data.ExternalUser.MessagesSent)
			if(lz_chat_data.ExternalUser.MessagesSent[mIndex].MessageId != _id)
				newMessageList[newMessageList.length] = lz_chat_data.ExternalUser.MessagesSent[mIndex];
		lz_chat_data.ExternalUser.MessagesSent = newMessageList;
	}
}

function lz_chat_change_window_state(_minimized)
{
	//lz_switch_title_mode(_minimized);
}

function lz_chat_update_com_chat_data(_timer)
{
	if(lz_chat_data.Status.Status == lz_chat_data.STATUS_ACTIVE)
	{
		lz_chat_data.ComChatVoucherActive.ChatTime++;
		if(_timer)
			lz_chat_data.ComChatSessionTimer = setTimeout("lz_chat_update_com_chat_data(true);",1000);
	}

	if(lz_chat_data.ComChatVoucherActive.ChatTimeMax > 0)
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_length_value').innerHTML = lz_format_time_span(lz_chat_data.ComChatVoucherActive.ChatTimeMax - lz_chat_data.ComChatVoucherActive.ChatTime);
	else
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_length_value').innerHTML = lz_format_time_span(lz_chat_data.ComChatVoucherActive.ChatTime);
	
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_amount_value').innerHTML = lz_chat_data.ComChatVoucherActive.ChatSessions + ((lz_chat_data.ComChatVoucherActive.ChatSessionsMax == -1) ? "" : " / " + lz_chat_data.ComChatVoucherActive.ChatSessionsMax);
	
	var date = new Date(lz_chat_data.ComChatVoucherActive.Expires*1000);
	
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_period_value').innerHTML = date.toLocaleDateString();
	
	if(lz_chat_data.ComChatVoucherActive.ChatSessionsMax > -1 && lz_chat_data.ComChatVoucherActive.ChatSessionsMax <= lz_chat_data.ComChatVoucherActive.ChatSessions)
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_amount_value').className = 'lz_chat_com_chat_panel_value_over';
	else
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_amount_value').className = 'lz_chat_com_chat_panel_value';
		
	if(lz_chat_data.ComChatVoucherActive.ChatTime >= lz_chat_data.ComChatVoucherActive.ChatTimeMax && lz_chat_data.ComChatVoucherActive.ChatTimeMax > -1)
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_length_value').className = 'lz_chat_com_chat_panel_value_over';
	else
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_length_value').className = 'lz_chat_com_chat_panel_value';
		
	if(lz_chat_data.ComChatVoucherActive.Expired)
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_period_value').className = 'lz_chat_com_chat_panel_value_over';
	else
		lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_period_value').className = 'lz_chat_com_chat_panel_value';
		
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_amount_value').style.display =
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_amount_caption').style.display = (lz_chat_data.ComChatVoucherActive.ChatSessionsMax > -1) ? "" : "none";	
		
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_length_value').style.display =
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_length_caption').style.display = (lz_chat_data.ComChatVoucherActive.ChatTimeMax > -1) ? "" : "none";	
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_length_spacer').style.display = (lz_chat_data.ComChatVoucherActive.ChatSessionsMax > -1 && lz_chat_data.ComChatVoucherActive.ChatTimeMax > -1) ? "" : "none";	

	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_period_spacer').style.display = (lz_chat_data.ComChatVoucherActive.Expires > 0 && (lz_chat_data.ComChatVoucherActive.ChatSessionsMax > -1 || lz_chat_data.ComChatVoucherActive.ChatTimeMax > -1)) ? "" : "none";	
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_period_caption').style.display = 
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_period_value').style.display = (lz_chat_data.ComChatVoucherActive.Expires > 0) ? "" : "none";	

	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_voucher_id').style.display = (lz_chat_data.ComChatVoucherActive.Expires == -1 && lz_chat_data.ComChatVoucherActive.ChatSessionsMax == -1 && lz_chat_data.ComChatVoucherActive.ChatTimeMax == -1) ? "" : "none";
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.4.0','lz_chat_com_chat_chat_voucher_id').innerHTML = lz_chat_data.ComChatVoucherActive.Id;
}

function lz_chat_set_status(_status)
{	
	if(_status == lz_chat_data.STATUS_ACTIVE)
	{
		var buttons = {sm:"SM",so:"SO",pr:"PR",ra:"RA",fv:"FV",fu:"FU"};
		if(lz_chat_data.ChatActive)
            for(buttinid in buttons)
            {
                lz_chat_get_frame_object('lz_chat_frame.3.2.chat.0.0','lz_cf_' + buttinid).style.display = lz_chat_get_frame_object('lz_chat_frame.3.2.chat.0.0','lz_cf_value_' + buttinid).value;
                lz_chat_get_frame_object('lz_chat_frame.3.2.chat.0.0','lz_cf_line_' + buttinid).style.display = lz_chat_get_frame_object('lz_chat_frame.3.2.chat.0.0','lz_cf_value_' + buttinid).value;
            }

		if(lz_chat_data.TimerWaiting != null)
			clearTimeout(lz_chat_data.TimerWaiting);
		if(lz_chat_data.Status.Status != _status && lz_chat_data.ComChatVoucherActive != null)
			lz_chat_data.ComChatSessionTimer = setTimeout("lz_chat_update_com_chat_data(true);",1000);
	}
	else if(_status == lz_chat_data.STATUS_INIT && lz_chat_data.TimerWaiting == null && <!--show_waiting_message-->)
	{
		lz_chat_data.TimerWaiting = setTimeout("lz_chat_show_waiting_message();",<!--waiting_message_time-->*1000);
	}
	if(lz_chat_data.Status.Status < lz_chat_data.STATUS_STOPPED)
		lz_chat_data.Status.Status = _status;
}

function lz_chat_handle_response(_status, _response)
{
	lz_chat_data.LastConnectionFailed = false;
	lz_chat_data.ConnectionRunning = false;
	lz_chat_process_xml(_response);
}

function lz_chat_handle_shout_response(_status, _response)
{
	lz_chat_process_xml(_response);
	setTimeout("lz_chat_reshout()",lz_chat_data.ChatFrequency * 1000);
}

function lz_chat_process_xml(_xml)
{
	try
	{
		if(_xml.length > 0 && _xml.indexOf("<livezilla_js>") != -1)
		{
			lz_chat_data.LastConnection = lz_global_timestamp();
			var lzTstart = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><livezilla_js>";
			var lzTend = "</livezilla_js>";
			eval(lz_global_base64_decode(_xml.substr(_xml.indexOf(lzTstart) + lzTstart.length,_xml.indexOf(lzTend)-_xml.indexOf(lzTstart)-lzTstart.length)));
		}
		else if(lz_chat_data.Status.Status < lz_chat_data.STATUS_INIT)
			setTimeout("lz_chat_handle_connection_error(null,null);",2000);
	}
	catch(ex)
    {
        if(lz_chat_data.Debug)
            alert(ex);
    }
}

function lz_chat_handle_connection_error(_status, _response)
{
	lz_chat_data.ShoutNeeded = true;
	lz_chat_data.ConnectionRunning = 
	lz_chat_data.ShoutRunning = false;
	lz_chat_data.LastConnectionFailed = true;
	if(lz_chat_data.Status.Status < lz_chat_data.STATUS_INIT)
		setTimeout("lz_chat_reload_groups();",5000);
}
