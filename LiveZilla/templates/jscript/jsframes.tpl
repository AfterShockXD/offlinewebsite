function lz_chat_release_frame(_name)
{
	lz_chat_data.PermittedFrames--;

    if(lz_chat_data.PermittedFrames==-1)
		lz_chat_close();
	if(lz_chat_data.PermittedFrames == 0 && lz_chat_data.Status.Status == lz_chat_data.STATUS_START)
	{
		lz_chat_set_parentid();
		if(!lz_chat_data.SetupError)
		{
			if(lz_geo_resolution_needed && lz_chat_data.ExternalUser.Session.GeoResolved.length != 7)
				lz_chat_geo_resolute();
			else
			{
				lz_chat_data.GeoResolution.SetStatus(7);
				setTimeout("lz_chat_startup();",200);
			}
		}
		else
		{
			lz_chat_release(false,lz_chat_data.SetupError);
		}
		
	}
	else if(lz_chat_data.PermittedFrames == 0 && lz_chat_data.Status.Status == lz_chat_data.STATUS_INIT)
		lz_chat_loaded();
}

function lz_chat_switch_file_upload()
{
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.1.0','lz_chat_file_frame').style.display = '';
	var frame_rows = lz_chat_get_frame_object('','lz_chat_frameset_chat').rows.split(",");
	if(frame_rows[2] != 0)
		frame_rows[2] = 0;
	if(frame_rows[3] != 0)
		frame_rows[3] = 0;
		
	if(frame_rows[1] == 0 && lz_chat_data.Status.Status == lz_chat_data.STATUS_STOPPED)
	{
		lz_chat_chat_alert(lz_chat_data.Language.RepresentativeLeft,lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0',''),null);
		return;
	}
		
	if(!lz_chat_data.InternalUser.Available)
	{
		lz_chat_chat_alert(lz_chat_data.Language.WaitForRepresentative,lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0',''),null);
		return;
	}
	
	frame_rows[1] = (frame_rows[1] == 0) ? 57 : 0;
	lz_chat_get_frame_object('','lz_chat_frameset_chat').rows = frame_rows.join(",");
}

function lz_chat_is_dd_open()
{
    var frame_rows = lz_chat_get_frame_object('','lz_chat_frameset_chat').rows.split(",");
    return (frame_rows[1] != 0 || frame_rows[2] != 0 || frame_rows[3] != 0 || frame_rows[4] != 0);
}

function lz_chat_switch_rating()
{
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.1.0','lz_chat_file_frame').style.display = 'none';
	if(!lz_chat_data.InternalUser.Id.length > 0)
	{
		lz_chat_chat_alert(lz_chat_data.Language.WaitForRepresentative,lz_chat_get_frame_object('lz_chat_frame.3.2.chat.5.0',''),null);
		return;
	}
	var frame_rows = lz_chat_get_frame_object('','lz_chat_frameset_chat').rows.split(",");
	if(frame_rows[1] != 0)
		frame_rows[1] = 0;
	if(frame_rows[3] != 0)
		frame_rows[3] = 0;
	frame_rows[2] = (frame_rows[2] == 0) ? 57 : 0;
	lz_chat_get_frame_object('','lz_chat_frameset_chat').rows = frame_rows.join(",");
}

function lz_chat_switch_com_chat_box(_visible)
{
	var frame_rows = lz_chat_get_frame_object('','lz_chat_frameset_chat').rows.split(",");
	frame_rows[4] = (_visible) ? 30 : 0;
	lz_chat_get_frame_object('','lz_chat_frameset_chat').rows = frame_rows.join(",");	
}

function lz_chat_switch_smiley_box()
{
	lz_chat_get_frame_object('lz_chat_frame.3.2.chat.1.0','lz_chat_file_frame').style.display = 'none';
	var frame_rows = lz_chat_get_frame_object('','lz_chat_frameset_chat').rows.split(",");
	if(frame_rows[1] != 0)
		frame_rows[1] = 0;
	if(frame_rows[2] != 0)
		frame_rows[2] = 0;
	
	frame_rows[3] = (frame_rows[3] == 0) ? 57 : 0;
	lz_chat_get_frame_object('','lz_chat_frameset_chat').rows = frame_rows.join(",");	
}

function lz_chat_get_frame_object(_frame,_id)
{
	try
	{
		if(_id == "")
			return frames['lz_chat_frame.3.2'].frames[_frame];
		else if(_frame == "")
			return frames['lz_chat_frame.3.2'].document.getElementById(_id);
		else
        {
            if(frames['lz_chat_frame.3.2'].frames[_frame].document.getElementById(_id)==null)
                alert("Invalid frame/object not available: "+_id);
			return frames['lz_chat_frame.3.2'].frames[_frame].document.getElementById(_id);
        }
	}
	catch(ex)
	{
		alert(ex+_frame);
	}
}

function lz_chat_change_url(_url)
{
	lz_chat_remove_from_parent();
	lz_chat_data.WindowNavigating = true;
	window.location.href = _url;
}