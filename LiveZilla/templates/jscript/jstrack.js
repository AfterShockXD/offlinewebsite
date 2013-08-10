var lz_referrer = document.referrer;
var lz_stopped = false;
var lz_chat_available = false;
var lz_request_window = null;
var lz_alert_window = null;
var lz_request_active = null;
var lz_request_last = null;
var lz_overlay_chat = null;
var lz_floating_button = null;
var lz_overlay_box = null;
var lz_overlay_active = null;
var lz_alert_active = null;
var lz_website_push_active = null;
var lz_session;
var lz_poll_id = 0;
var lz_timer = null;
var lz_timezone_offset = (new Date().getTimezoneOffset() / 60) * -1;
var lz_chat_windows = new Array();
var lz_check_cw = null;
var lz_cb_url = new Array();
var lz_document_head = document.getElementsByTagName("head")[0];
var lz_poll_required = false;
var lz_timer_connection_error = null;

if(!lz_is_ie)
    window.onbeforeunload = lz_tracking_unload;

function lz_tracking_unload()
{
    if(lz_floating_button != null)
        lz_floating_button.lz_livebox_unload();
    if(lz_request_window != null)
        lz_request_window.lz_livebox_unload();
    if(lz_overlay_box != null)
        lz_overlay_box.lz_livebox_unload();
    if(lz_overlay_chat != null)
        lz_overlay_chat.lz_livebox_unload();
}

function lz_tracking_add_chat_window(_browserId,_parent)
{
	try
	{
		var bfound, bdelete, bactive = false;
		for(var browser in lz_chat_windows)
		{
			if(lz_chat_windows[browser].BrowserId == _browserId || _parent)
			{
				if(!_parent)
				{
					lz_chat_windows[browser].LastActive = lz_global_timestamp();
					lz_chat_windows[browser].Deleted = false;
					lz_chat_windows[browser].Closed = false;
				}
				else if(!lz_chat_windows[browser].Deleted && !lz_chat_windows[browser].Closed && (lz_chat_windows[browser].LastActive <= (lz_global_timestamp()-10)))
				{
					lz_chat_windows[browser].Closed = true;
					bdelete = true;
				}
				bfound = true;
			}
			
			if(!lz_chat_windows[browser].Closed)
				bactive = true;
		}
		if(!bfound && !_parent)
		{
			var chatWindow = new lz_chat_window();
			chatWindow.BrowserId = _browserId;
			chatWindow.LastActive = lz_global_timestamp();
			lz_chat_windows.push(chatWindow);
			bactive = true;
		}
		else if(_parent && bdelete)
		{
			lz_tracking_poll_server(1004);
		}
	
		if(bactive && lz_check_cw == null)
			lz_check_cw = setTimeout("lz_check_cw=null;lz_tracking_add_chat_window('"+_browserId+"',true);",2000);
	}
	catch(ex)
	{

	}
}

function lz_is_geo_resolution_needed()
{
	return (lz_geo_resolution_needed && lz_session.GeoResolved.length != 7 && lz_session.GeoResolutions < 5);
}

function lz_tracking_remove_chat_window(_browserId)
{
	try
	{
		for(var browser in lz_chat_windows)
		{
			if(lz_chat_windows[browser].BrowserId == _browserId)
			{
				lz_chat_windows[browser].Deleted =
				lz_chat_windows[browser].Closed = true;
			}
		}
	}
	catch(ex)
	{
	  // domain restriction
	}
}

function lz_get_session()
{
	return lz_session;
}

function lz_tracking_server_request(_get,_scriptId)
{	
	if(lz_stopped)
		return;
		
	var lastScript = document.getElementById(_scriptId);
	if(lastScript == null) 
	{
		for(var index in lz_chat_windows)
			if(!lz_chat_windows[index].Deleted && lz_chat_windows[index].Closed)
			{
				lz_chat_windows[index].Deleted = true;
				_get += "&clch=" + lz_chat_windows[index].BrowserId;
			}

		if(lz_poll_website == "")
			_get = "?request=track&start=" + lz_global_microstamp() + _get;
		else
			_get = "?ws="+lz_poll_website+"&request=track&start=" + lz_global_microstamp() + _get;
			
		var newScript = document.createElement("script");
		newScript.id = _scriptId;
		newScript.src = lz_poll_url + _get;
		newScript.async = true;
		lz_document_head.appendChild(newScript);
	}
	else
		lz_poll_required = true;
}

function lz_tracking_poll_server(_cll)
{
	var getValues = "&browid="+lz_global_base64_url_encode(lz_session.BrowserId)+"&url="+lz_global_base64_url_encode(window.location.href)+"&pc="+(++lz_poll_id);
	getValues += (lz_session.UserId != null) ? "&livezilla="+ lz_global_base64_url_encode(lz_session.UserId) : "";
	getValues += "&cd="+window.screen.colorDepth+"&rh="+screen.height+"&rw="+screen.width+"&rf="+lz_global_base64_url_encode(lz_referrer)+"&tzo="+lz_timezone_offset;
	getValues += "&el="+lz_user_language+"&code="+lz_area_code+"&ec="+lz_user_company+"&dc="+lz_global_base64_url_encode(document.title);
	
	if(lz_user_phone.length > 0)
		getValues += "&ep=" + lz_user_phone;
	if(lz_user_question.length > 0)
		getValues += "&eq=" + lz_user_question;
	
	for(var i=0;i<=9;i++)
		if(lz_user_customs.length>i && lz_user_customs[i].length>0)
			getValues += "&cf" + i + "=" + lz_user_customs[i];
		
	if(lz_geo_resolution_needed && lz_session.GeoResolved.length == 7)
		getValues += "&geo_lat=" + lz_session.GeoResolved[0] + "&geo_long=" + lz_session.GeoResolved[1] + "&geo_region=" + lz_session.GeoResolved[2] + "&geo_city=" + lz_session.GeoResolved[3] + "&geo_tz=" + lz_session.GeoResolved[4] + "&geo_ctryiso=" + lz_session.GeoResolved[5] + "&geo_isp=" + lz_session.GeoResolved[6];

	getValues += "&geo_rid=" + lz_geo_resolution.Status;
	
	if(lz_geo_resolution.Span > 0)getValues += "&geo_ss=" + lz_geo_resolution.Span;
	if(lz_request_active != null)getValues += "&actreq=1";
	if(lz_get_parameters.length > 0)getValues += "&" + lz_get_parameters;
	
	if(lz_overlay_chat_available)
		getValues += lz_chat_poll_parameters();
	if(!lz_overlay_chat_available || (lz_external != null && lz_external.Username == ""))
		getValues += "&en="+lz_user_name + "&ee="+lz_user_email;

	lz_tracking_server_request(getValues,"livezilla_pollscript");

	if(!lz_stopped)
	{
		clearTimeout(lz_timer);
		lz_timer = setTimeout("lz_tracking_poll_server();",(lz_poll_frequency*1000));
	}
}

function lz_tracking_callback(_freq)
{
	if(lz_poll_frequency != _freq)
	{
		lz_poll_frequency = _freq;
		clearTimeout(lz_timer);
		lz_timer = setTimeout("lz_tracking_poll_server();",(lz_poll_frequency*1000));
	}
	
	if(lz_timer_connection_error != null)
		clearTimeout(lz_timer_connection_error);

    if(!lz_stopped)
	    lz_timer_connection_error = setTimeout("lz_tracking_callback("+_freq+");",30 * 1000);
		
	var lastScript = document.getElementById("livezilla_pollscript");
	if(lastScript != null)
		lz_document_head.removeChild(lastScript);
		
	var links = document.getElementsByTagName("a");
	var lcount = 0;
	for(var i=0;i<links.length;i++)
		if(links[i].className=="lz_cbl")
		{
			if(lz_cb_url.length<=lcount)
				lz_cb_url[lcount] = links[i].childNodes[0].src;
			links[i].childNodes[0].src = lz_cb_url[lcount] + "&cb=" + new Date().getTime();
			lcount++;
		}
		
	if(lz_poll_required)
	{
		lz_poll_required = false;
		lz_tracking_poll_server(1123);
	}
}

function lz_tracking_set_sessid(_userId, _browId)
{
	lz_session.UserId = lz_global_base64_decode(_userId);
	lz_session.BrowserId = lz_global_base64_decode(_browId);
	lz_session.Save();
}

function lz_tracking_close_request(_id)
{
	if(lz_request_active != null)
	{
		lz_request_last = lz_request_active;
		lz_request_active = null;
	}

	if(lz_request_window != null)
	{
		lz_request_window.lz_livebox_close('lz_request_window');
		lz_request_window = null;
	}
	
	if(lz_overlay_chat != null)
	{
		if(typeof lz_chat_decline_request != "undefined")
			lz_chat_decline_request(_id,true,false);
	}

}

function lz_tracking_init_website_push(_text,_id)
{	
	if(lz_website_push_active == null)
	{
		lz_website_push_active = _id;
		var exec = confirm((lz_global_base64_decode(_text)));
		setTimeout("lz_tracking_action_result('website_push',"+exec+",true);",100);
	}
}

function lz_tracking_exec_website_push(_url)
{	
	window.location.href = lz_global_base64_decode(_url);
}

function lz_tracking_stop_tracking()
{
	lz_stopped = true;
	lz_tracking_remove_overlay_chat();
}

function lz_tracking_geo_result(_lat,_long,_region,_city,_tz,_ctryi2,_isp)
{	
	lz_session.GeoResolved = Array(_lat,_long,_region,_city,_tz,_ctryi2,_isp);
	lz_session.Save();
	lz_tracking_poll_server(1001);
}

function lz_tracking_set_geo_span(_timespan)
{
	lz_geo_resolution.SetSpan(_timespan);
}

function lz_tracking_geo_resolute()
{
	if(lz_is_geo_resolution_needed())
	{
		lz_session.GeoResolutions++;
		lz_session.Save();
		lz_geo_resolution.SetStatus(1);
		if(lz_session.GeoResolutions < 4)
		{
			lz_geo_resolution.OnEndEvent = "lz_tracking_geo_result";
			lz_geo_resolution.OnSpanEvent = "lz_tracking_set_geo_span";
			lz_geo_resolution.OnTimeoutEvent = lz_tracking_geo_resolute;
			lz_geo_resolution.ResolveAsync();
		}
		else
			lz_tracking_geo_failure();
		return true;
	}
	else
	{
		lz_geo_resolution.SetStatus(7);
		return false;
	}
}

function lz_tracking_action_result(_action,_result,_closeOnClick,_parameters)
{
	if(_parameters == null)
		_parameters = "";

	_parameters = "&browid="+lz_global_base64_url_encode(lz_session.BrowserId)+"&url="+lz_global_base64_url_encode(window.location.href) + _parameters;
	_parameters += (lz_session.UserId != null) ? "&livezilla=" + lz_global_base64_url_encode(lz_session.UserId) : "";

	if(_action=="alert")
		_parameters += "&confalert="+lz_alert_active;
	else if(_action=="overlay_box")
		_parameters += "&confol="+lz_overlay_active;
	else if(_action=="chat_request")
		_parameters += ((!_result) ? "&decreq="+lz_request_active : "&accreq="+lz_request_active);
	else if(_action=="website_push")
	{
		if(_result)
			_parameters += "&accwp="+lz_website_push_active;
		else
			_parameters += "&decwp="+lz_website_push_active;
		setTimeout("lz_website_push_active = null;",10000);
	}
	
	if(_closeOnClick)
	{
		_parameters += "&clreq=1";
		lz_tracking_close_request();
	}
	
	if(lz_overlay_chat_available)
		_parameters += lz_chat_poll_parameters();
	lz_tracking_server_request(_parameters + "&" + lz_get_parameters,Math.random().toString());
}

function lz_tracking_add_floating_button(_pos,_sh,_shblur,_shx,_shy,_shcolor,_ml,_mt,_mr,_mb,_width,_height)
{
	if (lz_floating_button!=null || (document.all && !window.opera && !window.XMLHttpRequest && typeof document.addEventListener != 'function'))
		return;
		
	var fbdiv = document.getElementById("chat_button_image");
	lz_floating_button = new lz_livebox("lz_floating_button",fbdiv.parentNode.parentNode.innerHTML,_width,_height,_ml,_mt,_mr,_mb,_pos,0,6);
	
	if(_sh)
		lz_floating_button.lz_livebox_shadow(_shblur,_shx,_shy,'#'+_shcolor);
		
	lz_floating_button.lz_livebox_show();
	lz_floating_button.lz_livebox_div.style.zIndex = 99997;
}

function lz_tracking_add_overlay_box(_olId,_html,_pos,_speed,_slide,_sh,_shblur,_shx,_shy,_shcolor,_ml,_mt,_mr,_mb,_width,_height,_bg,_bgcolor,_bgop)
{
	if(lz_request_window == null && lz_overlay_box == null && lz_overlays_possible)
	{
		lz_overlay_active = _olId;
		lz_overlay_box = new lz_livebox("lz_overlay_box",lz_global_base64_decode(_html),_width,_height,_ml,_mt,_mr,_mb,_pos,_speed,_slide);
		if(_sh)
			lz_overlay_box.lz_livebox_shadow(_shblur,_shx,_shy,'#'+_shcolor);
		if(_bg)
			lz_overlay_box.lz_livebox_background('#'+_bgcolor,_bgop);
		lz_overlay_box.lz_livebox_show();
		lz_overlay_box.lz_livebox_div.style.zIndex = 99999;
		window.focus();
	}
}

function lz_tracking_send_alert(_alertId,_text)
{
	if(lz_alert_active == null && lz_overlays_possible)
	{
		lz_alert_active = _alertId;
		lz_alert_window = new lz_livebox("lz_alert_window",(lz_global_base64_decode(lz_alert_html)),350,110,0,0,0,0,11,1,0);
		lz_alert_window.lz_livebox_show();
		
		document.getElementById("lz_chat_alert_box").style.display = 'inline';
		document.getElementById("lz_chat_alert_button").onclick = function(){if(lz_alert_window != null){document.body.removeChild(document.getElementById('lz_alert_window'));lz_alert_window=null;lz_tracking_action_result("alert",true,false);lz_alert_active=null;}};
		document.getElementById("lz_chat_alert_box_text").innerHTML = (lz_global_base64_decode(_text));
		window.focus();
	}
}

function lz_tracking_remove_buttons()
{
    for (var i = 0;i<document.getElementsByTagName("a").length;i++)
        if(document.getElementsByTagName("a")[i].className=="lz_cbl")
            document.getElementsByTagName("a")[i].parentNode.removeChild(document.getElementsByTagName("a")[i]);
}

function lz_tracking_request_chat(_reqId,_text,_template,_width,_height,_ml,_mt,_mr,_mb,_position,_speed,_slide,_sh,_shblur,_shx,_shy,_shcolor,_bg,_bgcolor,_bgop)
{
	if(lz_overlay_box == null && lz_request_window == null && lz_overlays_possible)
	{
		_template = (lz_global_base64_decode(_template)).replace("<!--invitation_text-->",(lz_global_base64_decode(_text)));
		lz_request_active = _reqId;
		lz_request_window = new lz_livebox("lz_request_window",_template,_width,_height,_ml,_mt,_mr,_mb,_position,_speed,_slide);
	
		if(_sh)
			lz_request_window.lz_livebox_shadow(_shblur,_shx,_shy,'#'+_shcolor);
		if(_bg)
			lz_request_window.lz_livebox_background('#'+_bgcolor,_bgop);

	 	if(lz_request_last != _reqId)
		{
			lz_request_window.lz_livebox_show();
			window.focus();
		}
	}
}

function lz_tracking_add_overlay_chat(_template,_text,_width,_height,_ml,_mt,_mr,_mb,_position,_expanded,_online)
{
	lz_header_text = lz_global_base64_decode(_text);
	if(lz_overlay_chat == null && lz_overlays_possible)
	{

		if(!_online && lz_tickets_external)
			lz_session.OVLCState = "0";

		_template = (lz_global_base64_decode(_template)).replace("<!--text-->",lz_header_text);
		_height = (lz_session.OVLCState == "1" && !lz_is_tablet) ? _height : 31;

		lz_overlay_chat = new lz_livebox("lz_overlay_chat",_template,_width,_height,_ml,_mt,_mr,_mb,_position,0,6);
		lz_overlay_chat.lz_livebox_preset(lz_session.OVLCPos,lz_session.OVLCState == "1");

		lz_overlay_chat.lz_livebox_show();
		lz_overlay_chat.lz_livebox_div.style.zIndex = 960;

		if(lz_session.OVLCState == "1")
			lz_chat_change_state(false,true);

		lz_chat_set_init();

	}
}

function lz_tracking_remove_overlay_chat()
{
	if(lz_overlay_chat != null)
	{
		clearTimeout(lz_chat_invite_timer);
		clearTimeout(lz_chat_waiting_posts_timer);
		lz_overlay_chat.lz_livebox_close();
		lz_overlay_chat = null;
	}
}

function lz_tracking_geo_failure()
{
	lz_tracking_set_geo_span(lz_geo_error_span);
	lz_geo_resolution.SetStatus(4);
	lz_session.GeoResolved = Array('LTUyMg==','LTUyMg==','','','','','');
	lz_session.Save();
	lz_tracking_poll_server(1002);
}
