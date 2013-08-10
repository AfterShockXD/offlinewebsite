function lz_chat_data_box()
{
	this.Id = '<!--chat_id-->';
    this.Debug = <!--debug-->;
	this.ComChatInput = null;
	this.ComChatVouchers = null;
	this.ComChatVoucherChangeHTML = "";
	this.ComChatVoucherActive = null;
	this.ComChatSessionTimer = null;
	this.Language = new lz_chat_data_language();
	this.Templates = new lz_chat_data_templates();
	this.InternalUser = new lz_chat_internal_user();
	this.ExternalUser = new lz_chat_external_user();
	this.Status = new lz_chat_status();
	this.LastSender = -2;
	this.LastSound = 0;
	this.QueuePostsAdded = false;
	this.AlternateRow = true;
	this.SetupError = (lz_global_base64_decode('<!--setup_error-->'));
	this.FileUpload = new lz_chat_file_upload();
	this.PermittedFrames = 14;
	this.LastConnection = 0;
    this.Rated = false;
	this.CurrentApplication = (<!--function_chat-->) ? "chat" : ((<!--function_callback-->) ? "callback" : "ticket");

	this.ChatFrequency = <!--extern_frequency-->;
	this.PollTimeout = <!--extern_timeout-->;
	this.CallMeBackMode = false;
	this.ChatActive = false;
	this.PollHash = '';
	this.PollAcid = '';
	this.DynamicGroup = '';
	this.ShoutNeeded = false;
	this.ShoutRunning = false;
	this.ConnectionBroken = false;
	this.ConnectionRunning = false;
	this.LastConnectionFailed = false;
	this.DirectLogin = <!--direct_login-->;
    this.PreselectTicket = <!--preselect_ticket-->;
	this.CheckoutOnly = <!--checkout_only-->;
	this.CheckoutExtendSuccess = <!--checkout_extend_success-->;
	this.SoundsAvailable = false;
	this.SoundPlayer = null;
	this.IECompatible = <!--is_ie-->;
	this.Groups = null;
	this.TimerTyping = null;
	this.PreMessage = null;
	this.TimerWaiting = null;
    this.TimerReloadGroups = null;
	this.GetParameters = '<!--url_get_params-->';
	this.TempImage = new Image();
	this.TimezoneOffset = (new Date().getTimezoneOffset() / 60) * -1;
	this.ActiveAlertFrame = null;
	this.GeoResolution;
	this.QueueMessageAppended = false;
	this.ConnectedMessageAppended = false;
	this.WaitingMessageAppended = false;
	this.ValidationRequired = false;
	this.WindowUnloaded = false;
	this.WindowNavigating = false;
	this.WindowAnnounce = null;
	this.MessageCount = 1;
    this.MessageCountReceived = 0;
    this.CheckoutActive = false;

	this.SelectedGroup = null;
	this.Members = new Array();
	this.MembersPrevious = new Array();
	this.ChatGroupAvailable = false;
	this.InputFieldIndices = null;
	this.InputFieldValues = null;

    this.MouseX=0;
    this.MouseY=0;

	this.SYSTEM = -1;
	this.INTERNAL = 0;
	this.EXTERNAL = 1;
	this.MAXCHATLENGTH = 1500;
	this.STATUS_STOPPED = 4;
	this.STATUS_ACTIVE = 3;
	this.STATUS_ALLOCATED = 2;
	this.STATUS_INIT = 1;
	this.STATUS_START = 0;
	this.FILE_UPLOAD_OVERSIZED = 2;
	this.FILE_UPLOAD_REJECTED = 1;
	this.IMAGE_FILE_UPLOAD_SUCCESS = './images/file_upload_success.gif';
	
	function lz_chat_status()
	{
		this.Status = 0;
		this.Loaded = false;
	}
	
	function lz_chat_data_language()
	{
		this.JoinGroup = "<!--lang_client_join_group-->";
		this.LeaveGroup = "<!--lang_client_leave_group-->";
		this.FillMandatoryFields = "<!--lang_client_fill_mandatory_fields-->";
		this.FillMandatoryFields = "<!--lang_client_fill_mandatory_fields-->";
		this.SelectValidGroup = "<!--lang_client_select_valid_group-->";
		this.LanguageLeaveMessageShort = "<!--lang_client_leave_message-->";	
		this.LanguageLeaveMessage = "<!--lang_client_ticket_header-->";
		this.LanguageLeaveMessageInformation = "<!--lang_client_ticket_information-->";
		this.SendMessage = "<!--lang_client_send_message-->";	
		this.StartChat = "<!--lang_client_start_chat-->";
		this.StartChatInformation = "<!--lang_client_start_chat_information-->";
		this.StartChatComInformation = "<!--lang_client_start_chat_comm_information-->";
		this.StartSystem = "<!--lang_client_start_system-->";
		this.ConnectionBroken = "<!--lang_client_con_broken-->";
		this.MessageTooLong = "<!--lang_client_message_too_long-->";
		this.MessageReceived = "<!--lang_client_message_received-->";
		this.MessageFlood = "<!--lang_client_message_flood-->";
		this.RequestPermission = "<!--lang_client_file_upload_requesting-->";
		this.StartUpload = "<!--lang_client_file_upload_send_file-->";
		this.SelectFile = "<!--lang_client_file_upload_select_file-->";
		this.FileProvided = "<!--lang_client_file_upload_provided-->";
		this.WaitForRepresentative = "<!--lang_client_wait_for_representative-->";
		this.RepresentativeLeft = "<!--lang_client_no_representative-->";
		this.SelectRating = "<!--lang_client_please_rate-->";
		this.FileUploadRejected = "<!--lang_client_file_request_rejected-->";
		this.FileUploadOversized = "<!--lang_client_file_upload_oversized-->";
		this.TransmittingFile = "<!--lang_client_transmitting_file-->";
		this.Guest = "<!--lang_client_guest-->";
		this.ClientForwarding = "<!--lang_client_forwarding-->";
		this.ClientInternArrives = "<!--lang_client_intern_arrives-->";
		this.ClientErrorUnavailable = "<!--lang_client_error_unavailable-->";
		this.ClientIntLeft = "<!--lang_client_int_left-->";
		this.ClientInternLeft = "<!--lang_client_intern_left-->";
		this.ClientIntDeclined = "<!--lang_client_int_declined-->";
		this.ClientStillWaitingInt = "<!--lang_client_still_waiting_int-->";
		this.ClientThankYou = "<!--lang_client_thank_you-->";
		this.ClientIntIsConnected = "<!--lang_client_int_is_connected-->";
		this.ClientNoInternUsers = "<!--lang_client_no_intern_users-->";
		this.ClientNoInternUsersShort = "<!--lang_client_no_intern_users_short-->";
		this.ClientErrorGroups = "<!--lang_client_error_groups-->";
		this.ClientInvalidData = "<!--lang_client_invalid_data-->";
		this.ClientTopic = "<!--lang_client_topic-->";
		this.ClientInvalidComChatAccount = "<!--lang_client_voucher_not_found-->";
		this.ClientEmptyComChatAccount = "<!--lang_client_voucher_expired-->";
		this.ClientCallMeNow = "<!--lang_client_call_me_now-->";
		this.ClientRequestInstantCallback = "<!--lang_client_request_instant_callback-->";
		this.ClientRequestInstantCallbackInfo = "<!--lang_client_request_callback_information-->";
		this.ClientVoucherInUse = "<!--lang_client_voucher_in_use-->";
		this.RateSuccess = "<!--lang_client_rate_success-->";
		this.RateMax = "<!--lang_client_rate_max-->";
		this.System = "<!--lang_client_system-->";
		this.QueueMessage = "<!--lang_client_queue_message-->";
		this.NextOperator = "<!--lang_client_queue_next_operator-->";
		this.InfoFieldText = "<!--info_text-->";
		this.RepresentativeIsTyping = "<!--lang_client_representative_is_typing-->";
        this.ChatNotAvailable = "<!--lang_client_chat_not_available-->";
	}
	
	function lz_chat_data_templates()
	{
		this.MessageInternal = '<!--template_message_intern-->';
		this.MessageExternal = '<!--template_message_extern-->';
		this.MessageAdd = '<!--template_message_add-->';
		this.MessageAddAlt = '<!--template_message_add_alt-->';
        this.CallMeBackIF = '<!--template_call_me_back-->';
	}
	
	function lz_chat_internal_user()
	{
		this.Id = lz_global_base64_decode('<!--requested_intern_userid-->');
		this.Fullname = lz_global_base64_decode('<!--requested_intern_fullname-->');
		this.Available = false;
		this.ProfilePictureTime = 0;
		this.Language = "en";
	}
	
	function lz_chat_external_user()
	{
		this.Id = '';
		this.Username = '';
		this.Email = '';
		this.Company = '';
		this.Question = '';
		this.Phone = '';
		this.MailText = '';
		this.Group = '';
		this.Typing = false;
		this.MessagesSent = new Array();
		this.MessagesReceived = new Array();
		this.Session;
		this.TextAlign = 'left';
	}

	function lz_chat_file_upload()
	{
		this.Filename;
		this.Running;
		this.Permitted;
		this.Error;
	}
}

function lz_chat_post()
{
	this.MessageText = '';
	this.MessageTranslation = '';
	this.MessageId = '';
	this.MessageTime = 0;
	this.Received = false;
}

function lz_chat_input(_index,_active,_caption,_infoText,_name,_type,_value,_validation,_validationURL,_validationTimeout,_validationContinueOnTimeout)
{
	this.Index = _index;
	this.Active = _active;
	this.Caption = lz_global_base64_decode(_caption);
    this.InfoText = lz_global_base64_decode(_infoText);
	this.Name = lz_global_base64_decode(_name);
	this.Type = _type;
	this.Value = lz_global_base64_decode(_value);
	this.Validation = _validation;
	this.ValidationURL = lz_global_base64_decode(_validationURL);
	this.ValidationTimeout = _validationTimeout;
	this.ValidationContinueOnTimeout = _validationContinueOnTimeout;
	this.ValidationScript;
	this.ValidationTimeoutObject = null;
	this.ValidationContinueAt;
	this.Validated = false;
	this.ValidationResult = null;
	this.ValidationFrame;
	this.Validate = lz_chat_input_validate;
	this.SetStatus = lz_chat_input_set_status;
	
	function lz_chat_input_validate(_contFunc)
	{
		this.ValidationContinueAt = _contFunc+"()";
		this.ValidationTimeoutObject = setTimeout('lz_validate_input_result(-1,'+this.Index.toString()+')',this.ValidationTimeout*1000);
		var get = this.ValidationURL.replace('<!--input_id-->',this.Index.toString()).replace('<!--value-->',this.Value);
		if(get.indexOf("?") != -1)
			get += "&timestamp=" + lz_global_timestamp();
		else
			get += "?timestamp=" + lz_global_timestamp();
		var newScript = document.createElement("script");
		newScript.id = "livezilla_vscript" + this.Index.toString();
		newScript.src = get;
		document.getElementsByTagName("head")[0].appendChild(newScript);
	}
	
	function lz_chat_input_set_status(_frame,_enabled)
	{
		if(lz_chat_get_frame_object(_frame,'').document.getElementsByName("form_"+this.Index).length == 1)
			lz_chat_get_frame_object(_frame,'').document.getElementsByName("form_"+this.Index)[0].disabled=!_enabled;
	}
}

function lz_chat_com_chat_ticket()
{
	this.Id = "";
	this.ChatTimeMax = 0;
	this.ChatSessionsMax = 0;
	this.ChatTime = 0;
	this.ChatSessions = 0;
    this.Expires = 0;
    this.Expired = false;
}

