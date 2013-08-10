var lz_chat_full_load = true;
var lz_chat_status_change = true;
var lz_chat_last_post_received = null;
var lz_chat_last_message_received = null;
var lz_chat_last_poster = null;
var lz_operator = null;
var lz_sound_available = false;
var lz_sound_player = null;
var lz_external = new lz_chat_external_user();
var lz_chat_connecting = false;
var lz_chat_application = false;
var lz_ticket = null;
var lz_chat_state_expanded = false;
var lz_timer_typing = null;
var lz_timer_connecting = null;
var lz_header_text = "";
var lz_header_bot_text = "";
var lz_sound_format = "ogg";
var lz_chat_id = "";
var lz_closed = false;
var lz_chat_waiting_posts_visible = false;
var lz_chat_waiting_posts_timer;
var lz_chat_invite_timer = null;
var lz_desired_operator = null;
var lz_last_post = "";
var lz_leave_message_required = false;
var lz_chat_talk_to_human = false;
var lz_chat_scrolled = false;
var lz_change_name = null;
var lz_change_email = null;
var lz_chat_botmode = false;

function lz_chat_set_focus(_chat)
{
    try
    {
        if(document.getElementById("lz_chat_overlay_options_box").style.display != "none")
            return;

        if(lz_chat_state_expanded)
        {
            var input = null;
            if(!_chat)
                input = document.getElementById('lz_chat_overlay_ticket_message');
            else
                input = document.getElementById('lz_chat_text');

            input.focus();
            var val = input.value;
            input.value = '';
            input.value = val;
        }

    }
    catch(ex)
    {


    }
}

function lz_chat_scoll_down()
{
    setTimeout("document.getElementById('lz_chat_content_box').scrollTop = document.getElementById('lz_chat_content_box').scrollHeight;",100);
	lz_chat_set_focus(lz_chat_application);
}

function lz_chat_pop_out()
{	
	var add = "";
	lz_closed = true;
	if(lz_chat_id.length > 0 && !lz_chat_botmode)
	{
		lz_tracking_poll_server(1111);
	}
	else
	{
		add += "&mp=1";
		if(document.getElementById("lz_chat_invite_id") != null)
			lz_chat_decline_request(lz_request_active=document.getElementById("lz_chat_invite_id").value,false,false,true);
	}

    if(lz_poll_website != "")
        add += "&ws="+lz_poll_website;
		
	lz_chat_change_state(true,true);
	
	var group = (lz_operator != null) ? ("&intgroup="+lz_global_base64_url_encode(lz_operator.Group)) : "";
	var operator = (lz_desired_operator != null) ? ("&intid="+lz_global_base64_url_encode(lz_desired_operator)) : "";
	var name = (lz_external.Username != "") ? lz_external.Username : "";
	void(window.open(lz_poll_server + lz_poll_file_chat + '?acid='+lz_global_base64_url_encode(lz_chat_id)+'&livezilla='+lz_global_base64_url_encode(lz_session.UserId)+'&en='+lz_global_base64_url_encode(name) + operator + group + add + "&" + lz_get_parameters,'LiveZilla','width='+lz_window_width+',height='+lz_window_height+',left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,slidebars=no'));
}

function lz_chat_switch_options(_cancel)
{
	if(!_cancel)
	{
		var show = document.getElementById("lz_chat_overlay_options_box").style.display == "none";
		document.getElementById('lz_chat_overlay_options_sound').disabled = !lz_sound_available;
		if(show)
		{
			document.getElementById("lz_chat_overlay_options_name").value = lz_external.Username;
			if(lz_external.Username.length == 0)
				document.getElementById("lz_chat_overlay_options_name").value = lz_guest_name;
			document.getElementById("lz_chat_overlay_options_transcript").value = lz_external.Email;
			document.getElementById('lz_chat_overlay_options_sound').checked = lz_sound_available && lz_session.OVLCSound==1;
		}
		else
		{
			lz_session.OVLCSound = (document.getElementById('lz_chat_overlay_options_sound').checked) ? 1 : 0;
			if(document.getElementById("lz_chat_overlay_options_transcript").parentNode.style.display == "none")
			{
				lz_external.Email = document.getElementById('lz_chat_overlay_ticket_email').value;
				lz_chat_init_data_change(lz_global_trim(document.getElementById("lz_chat_overlay_options_name").value),null);
			}
			else
				lz_chat_init_data_change(lz_global_trim(document.getElementById("lz_chat_overlay_options_name").value),lz_global_trim(document.getElementById("lz_chat_overlay_options_transcript").value));
			document.getElementById("lz_chat_overlay_ticket_name").value = lz_external.Username;
			document.getElementById("lz_chat_overlay_ticket_email").value = lz_external.Email;
			lz_session.Save();
			lz_tracking_poll_server(1112);
		}
	}
	document.getElementById("lz_chat_overlay_options_box_bg").style.display =
	document.getElementById("lz_chat_overlay_options_box").style.display = (show) ? "" : "none";
}

function lz_chat_init_data_change(_name,_email)
{
	if(_name==null)
		_name = lz_external.Username;
	if(_email==null)
		_email = lz_external.Email;
		
	lz_external.Email = lz_change_email = _email;
	lz_external.Username = lz_change_name = _name;
}

function lz_chat_change_fullname(_name)
{
	if(lz_global_trim(_name) == "")
		_name = lz_guest_name;
	for(var i=0;i<document.getElementById("lz_chat_content_box").getElementsByTagName("TD").length;i++)
		if(document.getElementById("lz_chat_content_box").getElementsByTagName("TD")[i].className == "operator_name")
			document.getElementById("lz_chat_content_box").getElementsByTagName("TD")[i].innerHTML = lz_global_htmlentities(_name);
}

function lz_chat_play_sound()
{
	if(lz_sound_available && document.getElementById('lz_chat_overlay_options_sound').checked)
	{
		if(lz_sound_player == null)
			lz_sound_player = new Audio(lz_poll_server + "sound/message." + lz_sound_format);
		lz_sound_player.play();
	}
	window.focus();
}

function lz_chat_set_talk_to_human(_value,_poll)
{
    lz_chat_input_bot_state(false,false);
	lz_chat_talk_to_human = _value;
	if(_poll && _value)
		lz_tracking_poll_server(1119);
}

function lz_chat_input_bot_state(_botmode,_hide)
{
    lz_chat_botmode = _botmode;
    _hide = (lz_is_tablet) ? false : _hide;
    document.getElementById("lz_chat_text").style.display = (_hide) ? 'none' : '';
    document.getElementById("lz_bot_reply_loading").style.display = (!_hide) ? 'none' : '';
}

function lz_chat_message()
{
    if(lz_chat_botmode)
        lz_chat_input_bot_state(true,true);

	lz_closed = false;
	if(lz_global_trim(document.getElementById("lz_chat_text").value) != '')
	{
		if(document.getElementById("lz_chat_invite_id") != null)
			lz_chat_decline_request(lz_request_active=document.getElementById("lz_chat_invite_id").value,true,false,true);
			
		var msg = new lz_chat_post();
		msg.MessageText = document.getElementById("lz_chat_text").value;
		msg.MessageId = lz_global_microstamp();
		msg.MessageTime = lz_global_timestamp();
		lz_external.MessagesSent[lz_external.MessagesSent.length] = msg;
		document.getElementById("lz_chat_text").value = '';
		if(lz_operator==null)
			lz_chat_set_connecting(true,null,false);

		var posthtml = (lz_chat_last_poster != lz_external.Id) ? lz_global_base64_decode(lz_post_html) : lz_global_base64_decode(lz_add_html);
		posthtml = posthtml.replace("<!--message-->",lz_global_htmlentities(msg.MessageText));
		posthtml = posthtml.replace("<!--name-->",(lz_external.Username.length == 0) ? lz_guest_name : lz_external.Username);
		lz_chat_add_html_element(lz_global_base64_encode(posthtml),false,null,null,lz_global_base64_encode(lz_external.Id),null);		
	
		lz_tracking_poll_server(1114);
		lz_chat_set_focus(lz_chat_application);
	}
	return false;
}

function lz_chat_set_host(_id,_chatId,_groupId,_userid,_caller)
{
	lz_chat_id = _chatId;
	if(_id != null)
	{
		lz_operator = new lz_chat_operator();
		lz_operator.Id = _id;
		lz_operator.Group = _groupId;
		lz_desired_operator = _userid;
		lz_chat_init_data_change(null,null);
	}
	else
	{
		lz_desired_operator = null;
		lz_chat_init_data_change(null,null);
		lz_external.MessagesSent = new Array();
		lz_operator = null;
	}
	
}

function lz_chat_set_typing(_typingText,_fromTimer)
{
	if(lz_chat_connecting)
	{
		if(!_fromTimer && lz_timer_connecting != null)
			return;
			
		if(document.getElementById("lz_chat_overlay_info").innerHTML.length == 0)
			document.getElementById("lz_chat_overlay_info").innerHTML = lz_connecting_info_text;
		else
			document.getElementById("lz_chat_overlay_info").innerHTML = "";
			
		lz_timer_connecting = setTimeout("lz_chat_set_typing('',true);",700);
	}
	else
	{
		if(lz_timer_connecting != null)
			clearTimeout(lz_timer_connecting);
		lz_timer_connecting = null;
		document.getElementById("lz_chat_overlay_info").innerHTML = (_typingText != null) ? lz_global_base64_decode(_typingText) : lz_default_info_text;
	}
}

function lz_chat_switch_extern_typing(_typing)
{	
	var announce = (_typing != lz_external.Typing);
	if(_typing)
	{
		if(lz_timer_typing != null)
			clearTimeout(lz_timer_typing);
		lz_timer_typing = setTimeout("lz_chat_switch_extern_typing(false);",5000);
	}
	lz_external.Typing = _typing;
	if(announce && lz_operator != null)
		lz_tracking_poll_server(1115);
}

function lz_chat_set_connecting(_connecting,_id,_hidePopOut)
{
	if(_id != null)
		lz_external.Id = _id;
	lz_chat_connecting = _connecting;
	if(_connecting)
		lz_chat_set_typing(null,false);
    document.getElementById("lz_chat_apo").style.visibility = (_hidePopOut) ? "hidden" : "visible";
}

function lz_chat_set_last_post(_post)
{
	lz_last_post = lz_global_base64_decode(_post);
}

function lz_chat_require_leave_message()
{
    if(lz_chat_handle_ticket_forward(true))
        return;

	document.getElementById("lz_chat_overlay_ticket_back_button").style.display = "";
	lz_leave_message_required = true;

    if(document.getElementById('lz_chat_text').value.length > 0 && document.getElementById('lz_chat_overlay_ticket_message').value.length == 0)
        document.getElementById('lz_chat_overlay_ticket_message').value = document.getElementById('lz_chat_text').value;
    else if(lz_last_post.length > 0 && document.getElementById('lz_chat_overlay_ticket_message').value.length == 0)
        document.getElementById('lz_chat_overlay_ticket_message').value = lz_last_post;

	lz_chat_set_application(false, lz_chat_botmode);
}

function lz_chat_message_return()
{
	document.getElementById("lz_chat_overlay_ticket_back_button").style.display = "none";
	lz_leave_message_required = false;
	lz_chat_set_application(true, lz_chat_botmode);
}

function lz_chat_set_application(_chat,_bot,_human,_title)
{

	if(lz_leave_message_required)
		_chat = false;
	else if(lz_operator != null || lz_chat_connecting)
		_chat = true;

    if(lz_is_tablet)
    {
        if(!_human)
            _chat = false;
        _bot=false;
    }

	if(_chat && document.getElementById('lz_chat_overlay_ticket_message').value.length > 0)
	{
		document.getElementById('lz_chat_text').value = document.getElementById('lz_chat_overlay_ticket_message').value;
		document.getElementById('lz_chat_overlay_ticket_message').value = "";
	}
	else if(!_chat && document.getElementById('lz_chat_text').value.length > 0 && document.getElementById('lz_chat_overlay_ticket_message').value.length == 0)
		document.getElementById('lz_chat_overlay_ticket_message').value = document.getElementById('lz_chat_text').value;

    lz_chat_change_widget_application(_chat);

	document.getElementById("lz_chat_loading").style.display = "none";

    try
    {
	    if(_title != '')
		    lz_header_bot_text = lz_global_base64_decode(_title);
    }
    catch(ex)
    {

    }

	if(_chat && _bot)
		document.getElementById("lz_chat_overlay_text").innerHTML = lz_header_bot_text;
	else
		document.getElementById("lz_chat_overlay_text").innerHTML = (_chat) ? lz_header_online : lz_header_offline;

	if(!_chat && document.getElementById("lz_chat_queued_posts") != null)
		document.getElementById("lz_chat_content_box").removeChild(document.getElementById("lz_chat_queued_posts"));

	if(lz_chat_application != _chat)
	{
		lz_chat_set_element_width();
		lz_chat_scoll_down();
		lz_chat_set_focus(_chat);
	}
	lz_chat_application = _chat;
}

function lz_chat_set_name(_name,_email)
{
	if(lz_change_name == null || lz_global_base64_decode(_name) == lz_change_name || lz_external.Username.length == 0)
	{
		lz_chat_change_fullname(lz_global_base64_decode(_name));
		
		if(lz_chat_application)
		{
			document.getElementById('lz_chat_overlay_ticket_name').value = lz_external.Username = lz_global_base64_decode(_name);
			document.getElementById('lz_chat_overlay_ticket_email').value = lz_external.Email = lz_global_base64_decode(_email);
		}
		
		lz_change_name = null;
		lz_change_email = null;
	}
}

function lz_chat_poll_parameters() 
{
	var params = "";
	if(lz_operator != null)
		params += "&op=" + lz_operator.Id;
		
	if(lz_external.Typing)
		params += "&typ=1";

	if(lz_closed)
		params += "&clch=1";
	
	if(lz_chat_full_load)
		params += "&full=1";
		
	if(lz_chat_status_change)
		params += "&sc=1";
		
	if(lz_chat_talk_to_human)
		params += "&tth=1";
		
	if(lz_change_name != null || lz_change_email != null || (lz_operator != null && lz_chat_full_load))
	{
		if(lz_change_name != null && lz_guest_name != lz_change_name && lz_external.Username.length > 0)
			params += "&en=" + lz_global_base64_url_encode(lz_external.Username);
		if(lz_change_email != null && lz_external.Email.length > 0)
			params += "&ee=" + lz_global_base64_url_encode(lz_external.Email);
	}
		
	if(lz_ticket != null)
	{
		params += "&tid=" + lz_global_base64_url_encode(lz_ticket[0]) + "&tin=" + lz_global_base64_url_encode(lz_ticket[1]) + "&tie=" + lz_global_base64_url_encode(lz_ticket[2]) + "&tim=" + lz_global_base64_url_encode(lz_ticket[3]);
		lz_ticket = null;
	}

	lz_chat_status_change = false;
	if(lz_chat_last_post_received != null)
		params += "&lpr=" + lz_global_base64_url_encode(lz_chat_last_post_received);
	if(lz_chat_last_message_received != null)
		params += "&lmr=" + lz_global_base64_url_encode(lz_chat_last_message_received);
	if(lz_chat_last_poster != null)
		params += "&lp=" +lz_global_base64_url_encode(lz_chat_last_poster);
	if(lz_desired_operator != null)
		params += "&intid="+lz_global_base64_url_encode(lz_desired_operator); 
	
	var count=0;
	for(var i=0;i<lz_external.MessagesSent.length;i++)
		if(!lz_external.MessagesSent[i].Received)
			params+="&mi" + count.toString() + "=" + lz_global_base64_url_encode(lz_external.MessagesSent[i].MessageId) + "&mp" + (count++).toString() + "=" + lz_global_base64_url_encode(lz_external.MessagesSent[i].MessageText);

	return params;
}

function lz_overlay_chat_impose_max_length(_object, _max)
{
	if(_object.value.length > _max)
		_object.value = _object.value.substring(0,_max);
}

function lz_chat_release_post(_id)
{	
	newMessageList = new Array();
	for(var mIndex in lz_external.MessagesSent)
		if(lz_external.MessagesSent[mIndex].MessageId == _id)
			lz_external.MessagesSent[mIndex].Received=true;
}

function lz_chat_update_waiting_posts(_wposts,_fromTimer)
{
	if(_fromTimer)
	{
		lz_chat_waiting_posts_timer = null;
		lz_chat_waiting_posts_visible = !lz_chat_waiting_posts_visible;
	}
	
	if(_wposts > -1 && lz_session.OVLCWM != _wposts)
	{
		lz_session.OVLCWM = _wposts;
		lz_session.Save();
	}
	
	if(_fromTimer)
		document.getElementById("lz_chat_waiting_messages").style.display = (!lz_chat_state_expanded && lz_session.OVLCWM > 0 && !lz_chat_waiting_posts_visible) ? "" : "none";
	
	document.getElementById("lz_chat_waiting_messages").innerHTML = "&nbsp;"+lz_session.OVLCWM+"&nbsp;";
	
	if(lz_chat_waiting_posts_timer == null)
		lz_chat_waiting_posts_timer = setTimeout("lz_chat_update_waiting_posts(-1,true);",1000);
}

function lz_chat_add_html_element(_html,_full,_lpr,_lmr,_lp,_ip,_posts)
{
	if(_posts != null)
		lz_chat_update_waiting_posts((_posts == -1) ? 0 : (lz_session.OVLCWM + parseInt(_posts)),false);
	
	if(_html != null)
	{
		if(lz_chat_full_load && _full)
			lz_chat_full_load = false;
			
		if(_ip != null && lz_global_base64_url_decode(_ip) != lz_chat_last_poster && lz_chat_last_poster != null)
		{
			lz_tracking_poll_server(1117);
			return;
		}

		if(_lpr != null && lz_chat_last_post_received != lz_global_base64_decode(_lpr))
			lz_chat_last_post_received = lz_global_base64_decode(_lpr);
		
		if(_lmr != null && lz_chat_last_message_received != lz_global_base64_decode(_lmr))
			lz_chat_last_message_received = lz_global_base64_decode(_lmr);
			
		if(_lp != null && _html != null && lz_chat_last_poster != lz_global_base64_decode(_lp))
			lz_chat_last_poster = lz_global_base64_decode(_lp);
			
		var dx = document.createElement("div");
		dx.innerHTML = lz_global_base64_decode(_html);
		document.getElementById("lz_chat_content_inlay").appendChild(dx);
		lz_update_chat_area();
	}
}

function lz_update_chat_area()
{
	lz_chat_set_element_width();
	lz_chat_set_typing(null,false);
	
	var spacer = document.getElementById("xspacer");
	if(spacer != null)
		document.getElementById("lz_chat_content_box").removeChild(spacer);
	else
		spacer = document.createElement("div");
		
	spacer.style.height =
	spacer.style.lineHeight = "8px";
	spacer.id = "xspacer";
	document.getElementById("lz_chat_content_box").appendChild(spacer);
	
	lz_chat_scoll_down();	
}

function lz_chat_post()
{
	this.MessageText = '';
	this.MessageId = '';
	this.MessageTime = 0;
	this.Received = false;
}

function lz_chat_operator()
{
	this.Id = '';
	this.Fullname = '';
	this.Available = false;
	this.Group = '';
	this.Language = "en";
}

function lz_chat_external_user()
{
	this.Id = '';
	this.Username = '';
	this.Email = '';
	this.Company = '';
	this.Question = '';
	this.Typing = false;
	this.MessagesSent = new Array();
	this.MessagesReceived = new Array();
}

function lz_chat_detect_sound()
{
	var sa = document.createElement('audio');
	var avail_ogg = !!(sa.canPlayType && sa.canPlayType('audio/ogg; codecs="vorbis"').replace(/no/, ''));
	var avail_mp3 = !!(sa.canPlayType && sa.canPlayType('audio/mpeg;').replace(/no/, ''));
	lz_sound_available = (avail_ogg || avail_mp3);
	lz_sound_format = (avail_ogg) ? "ogg" : "mp3";
}

function lz_chat_decline_request(_id,_operator,_stateChange,_result)
{
	if(_result == null)
		_result = false;
	var node = document.getElementById(_id);
	if(node != null && node.style.display != 'none')
	{
		if(!_operator)
		{
			lz_request_active=_id;
			lz_tracking_action_result('chat_request',_result,false,lz_chat_poll_parameters());
		}
		node.parentNode.removeChild(node);
		if(_stateChange && lz_chat_state_expanded && lz_chat_id.length == 0 && lz_external.MessagesSent.length == 0)
			lz_chat_change_state(true,true);
		lz_chat_set_element_width();
	}
}

function lz_chat_ticket_display(_inputs)
{
	document.getElementById("lz_chat_ticket_received").style.display = (!_inputs) ? "" : "none";
	document.getElementById('lz_chat_ticket_form').style.display = (_inputs) ? "" : "none";
}

function lz_chat_mail_callback(_received)
{
	lz_chat_ticket_display(false);
	document.getElementById('lz_ticket_received').style.display = (_received) ? "" : "none";
	document.getElementById('lz_ticket_flood').style.display = (!_received) ? "" : "none";
	if(_received)
	{
		document.getElementById('lz_chat_overlay_ticket_message').value = "";
		lz_ticket = null;
	}
	lz_chat_ticket_progress(false);
}

function lz_chat_ticket_progress(_progress)
{
	document.getElementById('lz_chat_overlay_ticket_button').style.cursor = (_progress) ? "wait" : "pointer";
	document.getElementById('lz_chat_ticket_form').style.cursor = (_progress) ? "wait" : "default";
	document.getElementById('lz_chat_overlay_ticket_name').disabled = 
	document.getElementById('lz_chat_overlay_ticket_email').disabled = 
	document.getElementById('lz_chat_overlay_ticket_message').disabled = _progress;
}

function lz_chat_send_ticket()
{
	document.getElementById('lz_chat_overlay_ticket_required_name').className = (document.getElementById('lz_chat_overlay_ticket_name').value.length > 0) ? "lz_overlay_chat_required" : "lz_overlay_chat_required_red";
	document.getElementById('lz_chat_overlay_ticket_required_email').className = (document.getElementById('lz_chat_overlay_ticket_email').value.length > 0) ? "lz_overlay_chat_required" : "lz_overlay_chat_required_red";
	document.getElementById('lz_chat_overlay_ticket_required_message').className = (document.getElementById('lz_chat_overlay_ticket_message').value.length > 0) ? "lz_overlay_chat_required" : "lz_overlay_chat_required_red";

	if(document.getElementById('lz_chat_overlay_ticket_name').value.length > 0 && document.getElementById('lz_chat_overlay_ticket_email').value.length > 0 && document.getElementById('lz_chat_overlay_ticket_message').value.length > 0) 
	{
		lz_chat_ticket_progress(true);
		
		lz_change_name = lz_external.Username = document.getElementById('lz_chat_overlay_ticket_name').value;
		lz_change_email = lz_external.Email = document.getElementById('lz_chat_overlay_ticket_email').value;
		
		lz_ticket = new Array(lz_global_timestamp(),document.getElementById('lz_chat_overlay_ticket_name').value, document.getElementById('lz_chat_overlay_ticket_email').value, document.getElementById('lz_chat_overlay_ticket_message').value);
		lz_tracking_poll_server(1116);
	}
	else
		lz_chat_ticket_progress(false);
}

function lz_chat_scroll()
{
	if(!lz_chat_scrolled)
	{
		lz_chat_scrolled = true;
		lz_chat_set_element_width();
		lz_chat_scoll_down();
	}
}

function lz_chat_set_element_width()
{
	for(var i = 0;i<document.getElementById("lz_chat_content_box").childNodes.length;i++)
		if(document.getElementById("lz_chat_content_box").childNodes[i].tagName.toLowerCase() == "div")
			document.getElementById("lz_chat_content_box").childNodes[i].style.width = (lz_chat_scrolled || document.getElementById("lz_chat_content_box").scrollHeight > document.getElementById("lz_chat_content_box").clientHeight) ? "238px" : "255px";
}