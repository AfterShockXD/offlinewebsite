var lz_move_active = false;
var lz_move_x,lz_move_y,lz_move_tx,lz_move_ty;
var lz_move_object;
var lz_move_margins;

function lz_livebox(_name,_template,_width,_height,_mleft,_mtop,_mright,_mbottom,_position,_speed,_effect)
{
	this.lz_livebox_slide_finished = false;
	this.lz_livebox_template = _template;
	this.lz_livebox_name = _name;
	this.lz_livebox_move = lz_livebox_move_box;
	this.lz_livebox_show = lz_livebox_show_box;
    this.lz_livebox_unload = lz_livebox_unload_box;
	this.lz_livebox_shadow = lz_livebox_set_shadow;
	this.lz_livebox_background = lz_livebox_set_background;
	this.lz_livebox_preset = lz_livebox_center_set_preset;
	this.lz_livebox_close = lz_livebox_close_box;
	this.lz_livebox_chat = lz_livebox_direct_chat;
	this.lz_livebox_chat = lz_livebox_direct_chat;
	this.lz_livebox_get_left = lz_livebox_center_get_left;
	this.lz_livebox_get_right = lz_livebox_center_get_right;
	this.lz_livebox_get_top = lz_livebox_center_get_top;
	this.lz_livebox_get_bottom = lz_livebox_center_get_bottom;
	this.lz_livebox_div = null;
	this.lz_livebox_shadow_class = null;
	this.lz_livebox_close_button = null;
	this.lz_livebox_background_class = null;
	this.lz_livebox_background_div = null;
	this.lz_livebox_preset_top = null;
	this.lz_livebox_preset_left = null;
	this.lz_livebox_preset_right = null;
	this.lz_livebox_preset_bottom = null;
	this.lzibst_width = _width;
	this.lzibst_height = _height;
	this.lzibst_margin = lz_move_margins = new Array(_mleft,_mtop,_mright,_mbottom);
	this.lzibst_position = _position;
	this.lzibst_slide_speed = 10;
	this.lzibst_effect = _effect;
	this.lzibst_slide_step = Math.max(_speed,2);

	function lz_livebox_direct_chat(_intid,_groupid)
	{
		var user_header = lz_user_header;
		var user_email = lz_user_email;
		var user_company = lz_user_company;
		var area_code = lz_area_code;
		var question = lz_user_question;
		var custom = lz_user_customs;
		var user_name = lz_user_name;
		var language = lz_user_language;
		var website = lz_user_website;
		
		if(document.getElementById('lz_invitation_name') != null && document.getElementById('lz_invitation_name').value.length > 0)
			user_name = lz_global_base64_url_encode(document.getElementById('lz_invitation_name').value);

		var params = "";
		if(user_header.length > 0)
			params += '&eh='+user_header;
		if(user_email.length > 0)
			params += '&ee='+user_email;
		if(user_company.length > 0)
			params += '&ec='+user_company;
		if(area_code.length > 0)
			params += '&code='+area_code;
		if(question.length > 0)
			params += '&eq='+question;
		if(language.length > 0)
			params += '&el='+language;
		if(custom.length > 0)
			for(var i=0;i<=9;i++)
				if(lz_user_customs.length>i)
					params += "&cf" + i + "=" + lz_user_customs[i];
		if(website.length > 0)
			params += '&ws='+website;

		void(window.open(lz_poll_server + lz_poll_file_chat + '?' + lz_get_parameters + '&intid='+_intid+'&en='+user_name+'&intgroup='+_groupid + params,'LiveZilla','width='+lz_window_width+',height='+lz_window_height+',left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,slidebars=no'));
	}
	
	function lz_livebox_close_box(uid)
	{
		if(this.lz_livebox_background_div != null)
		{
			document.body.removeChild(this.lz_livebox_background_div);
			this.lz_livebox_background_div = null;
		}
		if(!this.lz_livebox_slide_finished)
			return;
		document.body.removeChild(this.lz_livebox_div);
	}
	
	function lz_livebox_set_shadow(_intense,_x,_y,_color)
	{
		this.lz_livebox_shadow_class = document.createElement('STYLE');
		this.lz_livebox_shadow_class.type = 'text/css';
    	var style = document.createTextNode(".livezilla_livebox_shadow_class"+this.lz_livebox_name+"{-moz-box-shadow: "+_x+"px "+_y+"px "+_intense+"px "+_color+";-webkit-box-shadow: "+_x+"px "+_y+"px "+_intense+"px "+_color+";box-shadow: "+_x+"px "+_y+"px "+_intense+"px "+_color+";} ");

		if(this.lz_livebox_shadow_class.styleSheet)
		    this.lz_livebox_shadow_class.styleSheet.cssText = style.nodeValue;
		else 
			this.lz_livebox_shadow_class.appendChild(style);
			
		document.getElementsByTagName('head')[0].appendChild(this.lz_livebox_shadow_class);
	}
	
	function lz_livebox_set_background(_bgcolor,_bgop)
	{
		if(this.lz_livebox_background_class == null)
		{
			this.lz_livebox_background_class = document.createElement('STYLE');
			this.lz_livebox_background_class.type = 'text/css';
	    	var style = document.createTextNode(".livezilla_livebox_background_class"+this.lz_livebox_name+"{width:100%;height:100%;z-index:99998;top:0px;left:0px;position:absolute;filter:alpha(opacity="+(_bgop*100)+"); -moz-opacity:"+_bgop+"; -khtml-opacity: "+_bgop+"; opacity:"+_bgop+";background:"+_bgcolor+";} ");

			if(this.lz_livebox_background_class.styleSheet)
			    this.lz_livebox_background_class.styleSheet.cssText = style.nodeValue;
			else 
				this.lz_livebox_background_class.appendChild(style);
			document.getElementsByTagName('head')[0].appendChild(this.lz_livebox_background_class);

			this.lz_livebox_background_div = document.createElement('DIV');
			this.lz_livebox_background_div.style.position = lz_livebox_get_css_position();
			this.lz_livebox_background_div.className = "livezilla_livebox_background_class" + this.lz_livebox_name;
			document.body.appendChild(this.lz_livebox_background_div);
		}
	}
	
	function lz_livebox_show_box()
	{
		this.lz_livebox_div = document.createElement('DIV');
		
		if(this.lz_livebox_shadow_class != null)
			this.lz_livebox_div.className = "livezilla_livebox_shadow_class"+this.lz_livebox_name;
		
		this.lz_livebox_div.id = this.lz_livebox_name;
		this.lz_livebox_div.style.position = lz_livebox_get_css_position();

        if(this.lzibst_effect == 6 && !('opacity' in document.body.style))
            this.lzibst_effect = 0;

        if(this.lzibst_effect != 5)
        {
		    this.lz_livebox_div.style.height = this.lzibst_height+'px';
		    this.lz_livebox_div.style.width = this.lzibst_width+'px';

        }
        else
        {
            this.lz_livebox_div.style.height = '0px';
            this.lz_livebox_div.style.width = '0px';

        }
        this.lz_livebox_div.style.overflow = 'hidden';
		this.lz_livebox_div.style.zIndex = 99999;
		this.lz_livebox_div.style.cursor = "move";
		this.lz_livebox_div.style.margin = "0px";

        if(this.lzibst_effect == 6)
            this.lz_livebox_div.style.opacity = "0.0";

		this.lz_livebox_div.innerHTML = this.lz_livebox_template.replace("<!--username-->",lz_global_base64_url_decode(lz_user_name));

        if(this.lzibst_effect == 0)
            this.lz_livebox_div.style.display = 'none';
        else if(this.lzibst_effect == 1)
            this.lz_livebox_div.style.top = -(this.lzibst_height+100)+'px';
        else if(this.lzibst_effect == 2)
            this.lz_livebox_div.style.left = lz_global_get_window_width()+(this.lzibst_width+100)+'px';
        else if(this.lzibst_effect == 3)
            this.lz_livebox_div.style.top = lz_global_get_window_height()+(this.lzibst_height+100)+'px';
		else if(this.lzibst_effect == 4)
			this.lz_livebox_div.style.left = -(this.lzibst_width+100)+'px';

        if(this.lzibst_effect == 6 && this.lzibst_position == '22')
            this.lz_livebox_div.style.left = -(this.lzibst_width+100)+'px';

		document.body.appendChild(this.lz_livebox_div);
		window.setTimeout("if(window['"+ this.lz_livebox_name +"']!=null)window['"+ this.lz_livebox_name +"'].lz_livebox_move()",1);

		document.onmousedown=lz_livebox_init_move;
		document.onmouseup=new Function("lz_move_active=false;lz_livebox_save_pos();");
	}

    function lz_livebox_unload_box()
    {
        var currentopacity = parseFloat(this.lz_livebox_div.style.opacity);
        currentopacity -= 0.06;
        this.lz_livebox_div.style.opacity = currentopacity;
        if(currentopacity > 0)
            window.setTimeout("window['"+ this.lz_livebox_name +"'].lz_livebox_unload()",2);
    }
	
	function lz_livebox_init_move(e) 
	{
		try
		{
			var fobj = (!lz_is_ie) ? e.target : event.srcElement;
			if(fobj.tagName.toLowerCase() == "input" || fobj.tagName.toLowerCase() == "textarea" || fobj.tagName.toLowerCase() == "iframe" || fobj.tagName.toLowerCase() == "a")
				return;
				
			var count = 0;
			while (fobj != null && fobj.style.zIndex != 99999 && count++ < 20)
			{
				fobj = (!lz_is_ie) ? fobj.parentNode : fobj.parentElement;
					if(!(fobj != null && fobj.className != "unmovable"))
						return true;
			}
	
			if (fobj != null && fobj.tagName != 'undefined' && fobj.style.zIndex == 99999)
			{
				lz_move_active = true;
				lz_move_object = fobj;
	
				//if(lz_move_object.style.top == '')
					//lz_move_object.style.top = ((lz_global_get_window_height()) - (parseInt(lz_move_object.style.height) - lz_move_margins[3])) + 'px';

				//if(lz_move_object.style.left == '')
					//lz_move_object.style.left = ((lz_global_get_window_width()) - parseInt(lz_move_object.style.width)) + 'px';
					
				lz_move_tx = parseInt(lz_move_object.style.left+0);
				lz_move_x = !lz_is_ie ? e.clientX : event.clientX;
				
				if(lz_move_object.style.top != '')
				{
					lz_move_y = !lz_is_ie ? e.clientY : event.clientY;
					lz_move_ty = parseInt(lz_move_object.style.top+0);
				
				}
				else
				{
					lz_move_y = !lz_is_ie ? e.clientY : event.clientY;
					lz_move_ty = lz_global_get_window_height() - (parseInt(lz_move_object.style.bottom+0)+parseInt(lz_move_object.style.height));
				}
				
				if(lz_move_object.style.left != '')
				{
					lz_move_tx = parseInt(lz_move_object.style.left+0);
					lz_move_x = !lz_is_ie ? e.clientX : event.clientX;
				}
				else
				{
					lz_move_x = !lz_is_ie ? e.clientX : event.clientX;
					lz_move_tx = lz_global_get_window_width() - (parseInt(lz_move_object.style.right+0)+parseInt(lz_move_object.style.width));
				}
	
				document.onmousemove=lz_livebox_process_move;
				return false;
			}
		}
		catch(e)
		{
		
		}
	}
	
	function lz_livebox_process_move(e)
	{
		if (lz_move_active)
		{
			var top = ((!lz_is_ie) ? lz_move_ty + e.clientY - lz_move_y : lz_move_ty + event.clientY - lz_move_y);

				lz_move_object.style.top = top + 'px';
			//else
			//	lz_move_object.style.bottom = (lz_global_get_window_height() - (top+parseInt(lz_move_object.style.height))) + 'px';
			
			var left = ((!lz_is_ie) ? lz_move_tx + e.clientX - lz_move_x : lz_move_tx + event.clientX - lz_move_x);
			

				lz_move_object.style.left = left + 'px';
			//else
			//	lz_move_object.style.right = (lz_global_get_window_width() - (left+parseInt(lz_move_object.style.width))) + 'px';
			
			return false;
		}
	}
	
	function lz_livebox_get_css_position()
	{
		return 'fixed';
	}

	function lz_livebox_move_box()
	{
		this.lz_livebox_div.style.bottom = this.lz_livebox_get_bottom();
		this.lz_livebox_div.style.right = this.lz_livebox_get_right();

		if(this.lzibst_effect == 0)
		{
			this.lz_livebox_div.style.left = this.lz_livebox_get_left();
			this.lz_livebox_div.style.top = this.lz_livebox_get_top();
			this.lz_livebox_div.style.right = this.lz_livebox_get_right();
			this.lz_livebox_div.style.bottom = this.lz_livebox_get_bottom();
            this.lz_livebox_div.style.display = '';
			this.lz_livebox_slide_finished = true;
		}
		else
		{
            if(this.lzibst_effect == 1 || this.lzibst_effect == 3)
            {
                var current = parseInt(this.lz_livebox_div.style.top.replace("px","").replace("pt",""));

                if(this.lzibst_effect == 1)
                    current+=this.lzibst_slide_step;
                else
                    current-=this.lzibst_slide_step;

                this.lz_livebox_div.style.top = current+'px';
                this.lz_livebox_div.style.left = this.lz_livebox_get_left();

                var topdist = parseInt(this.lz_livebox_get_top().replace("px",""));
                if((this.lzibst_effect == 1 && current < (topdist-this.lzibst_slide_step)) || (this.lzibst_effect == 3 && current > (topdist-this.lzibst_slide_step)))
                    window.setTimeout("if(window['"+ this.lz_livebox_name +"']!=null)window['"+ this.lz_livebox_name +"'].lz_livebox_move()",this.lzibst_slide_speed);
                else
                {
                    this.lz_livebox_div.style.top = topdist+'px';
                    this.lz_livebox_slide_finished = true;
                }
            }
            else if(this.lzibst_effect == 2 || this.lzibst_effect == 4)
            {
                var current = parseInt(this.lz_livebox_div.style.left.replace("px","").replace("pt",""));

                if(this.lzibst_effect == 2)
                    current-=this.lzibst_slide_step;
                else
                    current+=this.lzibst_slide_step;

                this.lz_livebox_div.style.left = current+'px';
                this.lz_livebox_div.style.top = this.lz_livebox_get_top();

                var leftdist = parseInt(this.lz_livebox_get_left().replace("px",""));
                if((this.lzibst_effect == 2 && current > (leftdist-this.lzibst_slide_step)) || (this.lzibst_effect == 4 && current < (leftdist-this.lzibst_slide_step)))
                    window.setTimeout("if(window['"+ this.lz_livebox_name +"']!=null)window['"+ this.lz_livebox_name +"'].lz_livebox_move()",this.lzibst_slide_speed);
                else
                {
                    this.lz_livebox_div.style.left = leftdist+'px';
                    this.lz_livebox_slide_finished = true;
                }
            }
			else if(this.lzibst_effect == 5)
            {
				var currentheight = parseInt(this.lz_livebox_div.style.height.replace("px","").replace("pt",""));
                var currentwidth = parseInt(this.lz_livebox_div.style.width.replace("px","").replace("pt",""));

                var wstep = this.lzibst_slide_step;
                var hstep = parseInt(this.lzibst_slide_step*(this.lzibst_height/this.lzibst_width));

                if(currentheight < (this.lzibst_height-hstep))
                    currentheight += hstep;
                if(currentwidth < (this.lzibst_width-wstep))
                    currentwidth += wstep;

				this.lz_livebox_div.style.height = currentheight+'px';
                this.lz_livebox_div.style.width = currentwidth+'px';

                this.lz_livebox_div.style.left = parseInt(this.lz_livebox_get_left().replace("px","").replace("pt",""))+((this.lzibst_width-currentwidth)/2)+"px";
                this.lz_livebox_div.style.top = parseInt(this.lz_livebox_get_top().replace("px","").replace("pt",""))+((this.lzibst_height-currentheight)/2)+"px";

				if((currentheight < (this.lzibst_height-hstep)) || (currentwidth < (this.lzibst_width-wstep)))
					window.setTimeout("window['"+ this.lz_livebox_name +"'].lz_livebox_move()",this.lzibst_slide_speed);
				else
				{
                    this.lz_livebox_div.style.height = this.lzibst_height+'px';
                    this.lz_livebox_div.style.width = this.lzibst_width+'px';
					this.lz_livebox_slide_finished = true;
				}
			}
            else if(this.lzibst_effect == 6)
            {
                this.lz_livebox_div.style.left = this.lz_livebox_get_left();
                this.lz_livebox_div.style.top = this.lz_livebox_get_top();
                var currentopacity = parseFloat(this.lz_livebox_div.style.opacity);
                currentopacity += 0.025;
                this.lz_livebox_div.style.opacity = currentopacity;
                if(currentopacity < 1)
                    window.setTimeout("window['"+ this.lz_livebox_name +"'].lz_livebox_move()",this.lzibst_slide_speed);
                else
                    this.lz_livebox_slide_finished = true;
            }

		}
		if(this.lz_livebox_slide_finished && document.body.onresize == null)
		{
			window.onresize = lz_livebox_center_box;
		}
	}
	
	function lz_livebox_center_get_left()
	{
		if(this.lz_livebox_preset_left != null)
			return this.lz_livebox_preset_left;
		else if(this.lz_livebox_preset_right != null)
			return "";

		var left = 0;
		if(this.lzibst_position == '01' || this.lzibst_position == '11' || this.lzibst_position == '21')
		{
			left  = parseInt((lz_global_get_window_width() * 50 / 100));
			//if(this.lz_livebox_div.style.position == 'absolute')
				//left += lz_global_get_page_offset_x()
			left -= parseInt(this.lzibst_width / 2);
			
			if(this.lzibst_margin[0] != 0)
				left += this.lzibst_margin[0];
			if(this.lzibst_margin[2] != 0)
				left -= this.lzibst_margin[2];
			
			return left+'px';
		}
		else if(this.lzibst_position == '00' || this.lzibst_position == '10' || this.lzibst_position == '20')
		{
			if(this.lzibst_margin[0] != 0)
				left += this.lzibst_margin[0];
			if(this.lzibst_margin[2] != 0)
				left -= this.lzibst_margin[2];

			left+=lz_global_get_page_offset_x();
			return left+'px';
		}
		else if(this.lzibst_position == '02' || this.lzibst_position == '12' || this.lzibst_position == '22')
		{
			left = lz_global_get_window_width();
            left -= parseInt(this.lzibst_width);

			if(this.lzibst_margin[0] != 0)
				left += this.lzibst_margin[0];
			if(this.lzibst_margin[2] != 0)
				left -= this.lzibst_margin[2];

			return left+'px';
		}
	}
	

	
	function lz_livebox_center_get_top()
	{

		if(this.lz_livebox_preset_top != null)
			return this.lz_livebox_preset_top;
		else if(this.lz_livebox_preset_bottom != null)
			return "";

		var top = 0;
		if(this.lzibst_position == '10' || this.lzibst_position == '11' || this.lzibst_position == '12')
		{
			top = parseInt((lz_global_get_window_height() * 50 / 100));
			if(this.lz_livebox_div.style.position == 'absolute')
				top += lz_global_get_page_offset_y();
			top -= parseInt(this.lzibst_height / 2);
			
			if(this.lzibst_margin[1] != 0)
				top += this.lzibst_margin[1];
			if(this.lzibst_margin[3] != 0)
				top -= this.lzibst_margin[3];

			return parseInt(top)+'px';
		}
		else if(this.lzibst_position == '00' || this.lzibst_position == '01' || this.lzibst_position == '02')
		{
			if(this.lzibst_margin[1] != 0)
				top += this.lzibst_margin[1];
			if(this.lzibst_margin[3] != 0)
				top -= this.lzibst_margin[3];
			return parseInt(top)+'px';
		}
        else if(this.lzibst_position == '20' || this.lzibst_position == '21' || this.lzibst_position == '22')
        {
            top = lz_global_get_window_height();
            top -= parseInt(this.lzibst_height);

            if(this.lzibst_margin[1] != 0)
                top += this.lzibst_margin[1];
            if(this.lzibst_margin[3] != 0)
                top -= this.lzibst_margin[3];
            return parseInt(top)+'px';
        }
	}
	
	function lz_livebox_center_get_bottom()
	{
        if(this.lz_livebox_preset_bottom != null)
            return this.lz_livebox_preset_bottom;
        else
            return '';
	}

    function lz_livebox_center_get_right()
    {
        return '';
    }
	
	function lz_livebox_center_set_preset(_preset,_vertical)
	{
		if(_preset == null)
			return;

		var parts = _preset.split(",");

		if(_vertical && parts[0] != null)
		{
			var presett = parseInt(parts[0].replace("px",""));
			if(presett < 0)
				this.lz_livebox_preset_top = 0 + "px";
			else if(presett+this.lzibst_height > lz_global_get_window_height())
				this.lz_livebox_preset_top = (lz_global_get_window_height() - this.lzibst_height) + "px";
			else if(!isNaN(presett))
				this.lz_livebox_preset_top = parts[0];
		}
        else
        {
            this.lz_livebox_preset_bottom = "0px";
        }

		if(parts[1] != null)
		{
			var presetl = parseInt(parts[1].replace("px",""));
			if(presetl < 0)
				this.lz_livebox_preset_left = 0 + "px";
			else if(presetl+this.lzibst_width > lz_global_get_window_width())
				this.lz_livebox_preset_left = (lz_global_get_window_width() - (this.lzibst_width+15)) + "px";
			else
				this.lz_livebox_preset_left = parts[1];
		}
	}
}



function lz_livebox_center_box()
{
	var objname = "";
	if(document.getElementById("lz_request_window") != null)
		objname = "lz_request_window";
	else if(document.getElementById("lz_alert_window") != null)
		objname = "lz_alert_window";
	else if(document.getElementById("lz_floating_button") != null)
		objname = "lz_floating_button";
	else if(document.getElementById("lz_overlay_box") != null)
		objname = "lz_overlay_box";
	else if(document.getElementById("lz_overlay_chat") != null)
    {
       objname = "lz_overlay_chat";
       if(lz_is_tablet)
       {
           document.getElementById("lz_overlay_chat").style.bottom = "0px";
           document.getElementById("lz_overlay_chat").style.top = "";
           document.getElementById(objname).style.left = window[objname].lz_livebox_get_left();
           return;
       }
    }

    if(document.getElementById(objname) != null)
    {
        document.getElementById(objname).style.top = window[objname].lz_livebox_get_top();
        document.getElementById(objname).style.left = window[objname].lz_livebox_get_left();
        document.getElementById(objname).style.right = window[objname].lz_livebox_get_right();
        document.getElementById(objname).style.bottom = window[objname].lz_livebox_get_bottom();
    }


}

function lz_livebox_save_pos()
{
	if(lz_session != null && document.getElementById("lz_overlay_chat") != null)
	{
		lz_session.OVLCPos = document.getElementById("lz_overlay_chat").style.top+","+document.getElementById("lz_overlay_chat").style.left;
		lz_session.Save();
	}
}