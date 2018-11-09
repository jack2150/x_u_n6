/******************************************
* validateUpload()
* validate the uploads before submit
******************************************/
function validateUpload()
{
    var hasuploads=false;
    var invaliduploads = 0;
    var prefix = 'uploadfile';
    
    // prefix of field name
    if(currentmode==1) prefix = 'uploadfile';
    if(currentmode==2) prefix = 'uploadurl';
    if(currentmode==3) prefix = 'uploadftp';
    
    // flash will validate this itself!
    if(currentmode!=4)
    for(var i=0;i<max_uploads;i++)
    {
        if(!is_object(prefix+'_'+i)) continue;
         
        var filename=document.getElementById(prefix+'_'+i).value;

        if(filename.length) hasuploads = true;

        if(filename.length
           &&
           ((allowed_types&&!in_array(filename,allowed_types))
           ||
           (disabled_types&&in_array(filename,disabled_types)))
           )
         {
             invaliduploads ++;
         }
    }
    // set hasFlashUploads from flash object
    if(currentmode==4)
    {
        hasuploads = hasFlashUploads;
    }
    
    // if no file select
    if(hasuploads==false)
    {
        alert(NoFileSelected);
        return false;
    }
    else if(invaliduploads)
    {
        if(allowed_types) alert(UploadInvalid+' '+allowed_types+' '+AllowedFiletypes);
        else alert(UploadInvalid+' '+disabled_types+' '+DisabledFiletypes);
        return false;
    }

    // detect the filed is available and check it meet the reqirement
    if(is_object('fromemail')&&is_object('toemail'))
    {
        var fromemail=document.getElementById('fromemail').value;
        var toemail=document.getElementById('toemail').value;
        if(fromemail.length&&!checkmail(fromemail))
        {
            alert(EmailInvalid);
            document.getElementById('fromemail').focus();
            return false;
        }
        if(fromemail.length==0&&toemail.length)
        {
            alert(SenderRequired);
            document.getElementById('fromemail').focus();
            return false;
        }
    }
    // check perms is aggreed
    if(is_object('terms'))
    {
        var terms=document.getElementById('terms');
        if(terms.checked==false)
        {
            alert(TOSUnchecked);
            document.getElementById('terms').focus();
            return false;
        }
    }
    
    return true;
};
/******************************************
* showProgress(params)
* params: string
*         bread,itotal,dtremainingf,dtelapsedf,bspeedf,
******************************************/
var lastRead=0;
var uploadDone=0;
function showProgress(params)
{
    document.getElementById('progressbar').style.display= '';
    document.getElementById('progressbar2').style.display= '';

    var parts = params.split(',');
    
    lastRead = parts[0]>lastRead?parts[0]:lastRead;
    if(lastRead && parts[0]==0) return 1;
    if(parts[0]==parts[1]&&parts[0]) uploadDone=1;
    
		if (parts[1]<1048576) {
			document.getElementById('bread').innerHTML = (parts[0]/1024).toFixed(2)+" Kb";
			document.getElementById('itotal').innerHTML = (parts[1]/1024).toFixed(2)+" Kb";
		}
		else {
			document.getElementById('bread').innerHTML = (parts[0]/1048576).toFixed(2)+" MB";
			document.getElementById('itotal').innerHTML = (parts[1]/1048576).toFixed(2)+" MB";
		}
		document.getElementById('dtremainingf').innerHTML = parts[2];
		document.getElementById('dtelapsedf').innerHTML = parts[3];
		document.getElementById('bspeedf').innerHTML = parts[4];

    if(use_flash_progress)
    {
        document.flash_progress_bar.SetVariable("/:percentage", parts[5]);
    } else
    {
        document.getElementById('progBar').innerHTML= "<div class='progInner' style='width: " + parts[5] + "%;'>" + parts[5] + "%</div>";
    }
};
/******************************************
* RegisterNow()
* redirect users to register page.
******************************************/
function RegisterNow()
{
    var ok = confirm(NeedRegister);
    if(ok==1) document.location = "register.php";
};
/******************************************
* submitEmailForm()
* submit email form after upload is done.
* Note:the form may be resubmitted for 10 times if no uploaded files is found at server
******************************************/
function submitEmailForm()
{
    var fromemail=document.getElementById('fromemail').value;
    var toemail=document.getElementById('toemail').value;
    var terms=document.getElementById('terms');
    if(fromemail.length==0&&toemail.length!=0)
    {
        alert(FromemailNeeded);
        return false;
    }
    else
    {
        if(uploadDone==0)
        {
            var iread  = document.getElementById('itotal').innerHTML;
            var dtremainingf = "00:00:00";
            var dtelapsedf = document.getElementById('dtelapsedf').innerHTML;
            var bspeedf = document.getElementById('bspeedf').innerHTML;
            var percent = "100";

            showProgress(iread+","+iread+","+dtremainingf+","+dtelapsedf+","+bspeedf+","+percent);
        }
        document.emailform.uploadmode.value=currentmode;

		
        setTimeout("document.emailform.submit();", 2000);
        //return document.emailform.submit();
    }
};
/******************************************
* newUploadField(type)
* generate new upload files
* type:string file,url,ftp
******************************************/
var attaIdx=1;
var fileNum=1;
var urlNum=1;
var ftpNum=1;
function newUploadField(type)
{
    if(type=='file') attaIdx = fileNum;
    if(type=='url')  attaIdx = urlNum;
    if(type=='ftp')  attaIdx = ftpNum;

    if(attaIdx>=max_uploads) {alert(MaxUploadsGot);return;}
    
    var fileobj=document.getElementById(type+'_'+'wraper0');
    var filelist=document.getElementById(type+'list');
    var filecode = fileobj.innerHTML;

    //alert( filecode);

    filecode = filecode.replace(/_0/g,'_'+attaIdx+'');
    filecode = filecode.replace(/\[0\]/g,'['+attaIdx+']');
    
    //alert( filecode);
    new Insertion.After(filelist, '<div id="'+type+'_wraper'+attaIdx+'" class="file_wraper">'+filecode+'</div>');

    //clear out the filled values
    if(type!='file')
    document.getElementById('upload'+type+'_'+attaIdx).value='';
    
    document.getElementById(type+'_descr_'+attaIdx).value='';
    document.getElementById(type+'_password_'+attaIdx).value='';
    
    attaIdx++;
    
    if(type=='file') fileNum = attaIdx;
    if(type=='url')  urlNum  = attaIdx;
    if(type=='ftp')  ftpNum  = attaIdx;
};
/******************************************
* calsize()
* output the nice format of bytes
******************************************/
function calsize(obj,str)
{
    var size=obj.value;
    var unit = 'B';
    if(size=='') size=0;
    if(size>1024) { size=size/1024; unit='KB'; }
    if(size>1024) { size=size/1024; unit='MB'; }
    if(size>1024) { size=size/1024; unit='GB'; }
    size = Math.round(size*100)/100;

    document.getElementById(str).innerHTML=size+' '+unit;
};
/******************************************
* showDownloadLinks()
* show the download links after the upload
******************************************/
function showDownloadLinks()
{
    var uploadfrmdoc = document.getElementById('emailframe').contentWindow;

    try{

    document.getElementById('dl_link').innerHTML = uploadfrmdoc.document.getElementById('linksholder').innerHTML;
    document.getElementById('uploadoverview').innerHTML = uploadfrmdoc.document.getElementById('uploadoverview').innerHTML;
	var filesize=document.getElementById('filesize').innerHTML;
	if (filesize<1048576) {
		document.getElementById('filesize').innerHTML = filesize+" bytes";
	}
	else {
		document.getElementById('filesize').innerHTML = (filesize/1048576).toFixed(2)+" MB";
	}

    }
    catch (e) {alert(e)}

	document.getElementById('cancelupload').style.display='none';
    document.getElementById('uploadresults').style.display='';
    document.getElementById('uploadoverview').style.display='';
    document.getElementById('helpwindow').style.display='none';
	document.getElementById('uploadmore').style.display='';
};
/******************************************
* showDownloadErrors()
* show the download errors if the upload fails
******************************************/
function showDownloadErrors(error_type)
{
    try{
		document.getElementById('uploaderror').style.display='';
		if (error_type == 1) {
			document.getElementById('errordetail1').style.display='';
		}
		else if (error_type == 2) {
			document.getElementById('errordetail2').style.display='';
		}
		else if (error_type == 3) {
			document.getElementById('errordetail3').style.display='';
		}
		else if (error_type == 4) {
			document.getElementById('errordetail4').style.display='';
		}
		else {
			document.getElementById('errordetail').style.display='';
		}
	    stopUpload();
    }
	catch (e) {
        alert('showDownloadErrors:'+e)
    }
};
/******************************************
* resetUploadInterface()
* show the upload interface again if the upload fails
******************************************/
function resetUploadInterface()
{
    try{
    lastRead=0;
    uploadDone=0;

    document.getElementById('uploadwindow').style.display='';
    document.getElementById('emailwindow').style.display='';
    document.getElementById('uploadoverview').style.display='none';
    document.getElementById('uploadresults').style.display='none';
    document.getElementById('progressbar').style.display='none';
    document.getElementById('progressbar2').style.display='none';
    document.getElementById('helpwindow').style.display='none';
    
    }catch (e) {
        alert('resetUploadInterface:'+e)
    }
};
/******************************************
* startUploading()
* initiate the upload interface once user click upload
******************************************/
function startUploading()
{
    try{
    lastRead=0;
    uploadDone=0;
    
	document.getElementById('cancelupload').style.display   ='';
    document.getElementById('uploaderror').style.display   ='none';
    document.getElementById('uploadwindow').style.display  ='none';
    document.getElementById('emailwindow').style.display   ='none';
    document.getElementById('uploadoverview').style.display='none';
    document.getElementById('uploadresults').style.display ='none';
    
    document.getElementById('progressbar').style.display   ='';
    document.getElementById('progressbar2').style.display  ='';
    document.getElementById('helpwindow').style.display    ='';
              
    stopupload = false;
    }catch (e) {
        alert('startUploading:'+e)
    }
};
/******************************************
* ch_mode(id)
* switch upload mode,and record the mode into cookie
* id:string file,url,ftp
******************************************/
function ch_mode(id)
{
    for(var i=1;i<=4;i++)
    {
       if(i==id)
       {
          currentmode=id;
          setcookie('yab_uploadmode',id);
          document.getElementById('uploadmode'+i).style.display='';
		  document.getElementById('uploadmode').value=id;
          document.uploadform.action=actions[id];
          
          if(id==4)
          {
              var version = deconcept.SWFObjectUtil.getPlayerVersion();
              if (document.getElementById && (version['major'] > 0)) {
                  if(version['major']<8)
                  {
                      document.getElementById('uploadmode4').style.display='none';
                      document.getElementById('flashversion').style.display='block';
                  }
                  else
                  {
                      buildFlashUpload(flashPath);
                  }
              }
          }
       }
       else
       {
           if(is_object('uploadmode'+i))
           document.getElementById('uploadmode'+i).style.display='none';
       }
    }
};
/******************************************
* postIt()
* submit upload form
******************************************/
var rParam='';
function postIt()
{
    // reset the form action url
    document.uploadform.action=actions[currentmode];
    
    // validate file types before uplaod
    var ok = validateUpload();

    if(ok==false) return false;

    // set uploading interface
    startUploading();
    
    // collect param to sen in progress url
    var sid = document.uploadform.sessionid.value;
	iTotal = escape("-1");
	rParam = "iTotal=" + iTotal;
	rParam += "&iRead=0";
	rParam += "&iStatus=1";
    rParam += "&iNums="+max_uploads;
    rParam += "&iMode="+currentmode;
	rParam += "&sessionid=" + sid;

    // if progress is syn mode, disable the ajax use!
    if(currentmode==1&&cgi_prog_mode=='ajax') AjaxRequest(rParam);
    if(currentmode==2&&url_prog_mode=='ajax') AjaxRequest(rParam);
    if(currentmode==3&&ftp_prog_mode=='ajax') AjaxRequest(rParam);
    
    // submit form
 	document.uploadform.submit();
    return true;
};
/******************************************
* showResponse()
* process the response of progress bar
******************************************/
function showResponse(originalRequest)
{
    var parts= new Array();
    var params=originalRequest.responseText;

    //alert('Response:'+params);
    //start to upload,
    if(params.substring(0,5)=='start')
    {
        rParam = params.substring(6);
        setTimeout("AjaxRequest(rParam);",2000);
    }
    else
    {
        parts = params.split(',');
        if(parts.length>5)
        {
            var url = parts[6];

            // fail to get the correct data, use lastSuccessParam
            if(url.substring(0,7)!='iTotal=' && url!='')
            {
                var url = lastSuccessParam;
            }
            else
            {
                showProgress(params);
            }
        }
        else
        {
            //alert('Fail Response:'+params+lastSuccessParam);
            var url = lastSuccessParam;
        }

        if(url.length>5)
        {
            setTimeout("AjaxRequest('"+url+"');",2000);
        }
    }
};
/******************************************
* stopUpload()
* disable future ajax request of progress bar
******************************************/
function stopUpload()
{
    stopupload = true;
};
/******************************************
* AjaxRequest()
* send progress bar request
******************************************/
var lastSuccessUrl = '';
function AjaxRequest(rParam)
{
    if(uploadDone==1) return;
    var url=document.uploadform.returnurl.value;
    lastSuccessParam = rParam;
    //alert(url+rParam+'&url='+progress_url+'&r='+Math.random());
    var myAjax = new Ajax.Request(url, {method: 'get', parameters: rParam+'&url='+progress_url+'&r='+Math.random(), onComplete: showResponse,onFailure: reportError});
};
/******************************************
* reportError()
* for debuging
******************************************/
function reportError(request)
{   
    alert(originalRequest.responseText);
    alert('Sorry. There was an error.');
};
/******************************************
* AjaxSubmit(formname,holder)
* submi the form with the 'formname',
* if holder is specifed, show the content in holder
* @param string
* @param string
******************************************/
function AjaxSubmit(formname,holder)
{
    var url = $(formname).action;
    var httpmethod = $(formname).method;

    var Param = Form.serialize(formname);
    
    if(holder)
    var myAjax = new Ajax.Updater (
                    holder,
                    url,
                    {method: httpmethod, parameters: Param,evalScripts: true}
                    );
    else
    {
        var success	= function(t){submitComplete(t, formname);};
    	var failure	= function(t){submitFailed(t, formname);};
        var myAjax = new Ajax.Request (url, {method:httpmethod, parameters:Param, onSuccess:success, onFailure:failure});
    }
};
/******************************************
* is_object(id)
* check if the object with the 'id' exists
* @param string
* @return bool
******************************************/
function is_object(id)
{
    return document.getElementById(id) != null;
};
/******************************************
* in_array(name,arr)
* check name exists in array 'arr'
* @param string
* @param array
* @return bool
******************************************/
function in_array(name,arr)
{
   name=name.toLowerCase();
   var last=name.lastIndexOf('.');
   var ok=0;
   if(last!=-1)
   {
      var ext=name.substring(last+1);
      if(ext!='')
      {
          var exts=arr.split(',');
          for(var i=0;i<exts.length;i++)
          {
              if(exts[i]==ext) return 1;
          }
      }
      else
      {
          return 0;
      }
   }
   else
   {
       return 0;
   }
   return 0;
};
/**
* Emulates unhtmlspecialchars in vBulletin
*
* @param	string	String to process
*
* @return	string
*/
function unhtmlspecialchars(str)
{
	f = new Array(/&lt;/g, /&gt;/g, /&quot;/g, /&amp;/g);
	r = new Array('<', '>', '"', '&');

	for (var i in f)
	{
		str = str.replace(f[i], r[i]);
	}

	return str;
};

/**
* Emulates PHP's htmlspecialchars()
*
* @param	string	String to process
*
* @return	string
*/
function htmlspecialchars(str)
{
	//var f = new Array(/&(?!#[0-9]+;)/g, /</g, />/g, /"/g);
	var f = new Array(
		(is_mac && is_ie ? new RegExp('&', 'g') : new RegExp('&(?!#[0-9]+;)', 'g')),
		new RegExp('<', 'g'),
		new RegExp('>', 'g'),
		new RegExp('"', 'g')
	);
	var r = new Array(
		'&amp;',
		'&lt;',
		'&gt;',
		'&quot;'
	);

	for (var i = 0; i < f.length; i++)
	{
		str = str.replace(f[i], r[i]);
	}

	return str;
};
function ClipBoard(id)
{
	var ccc = MM_findObj(id);
    if (document.all){
	ccc.value = ccc.innerText;
	Copied = ccc.createTextRange();
	Copied.execCommand("Copy");
    alert("URL copied!");
    }
    else
    {
        alert('Close this box and press \'CTL-c\' to copy');
        ccc.focus();
        ccc.select();
    }
};
function MM_findObj(n, d) { //v4.01
    var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
    if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
    for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
    if(!x && d.getElementById) x=d.getElementById(n); return x;
};
function highlight(id) {
    var field = MM_findObj(id);
	field.focus();
  	field.select();
};
function toggle(el) {
    lyr = document.getElementById(el);

    if (lyr.style.display != 'block') { lyr.style.display = 'block'; }

    else { lyr.style.display = 'none'; }
};
function checkAll()
{
    for (var i=0;i<document.myform.elements.length;i++)
    {
       var e = document.myform.elements[i];
       if (e.type == 'checkbox' && e.name != 'allbox'&& !e.disabled)
       e.checked = document.myform.allbox.checked;
    }
};
function opConfirm(text, conf)
{
    for (var i=0;i<document.myform.elements.length;i++)
    {
       var e = document.myform.elements[i];
       if (e.type == 'checkbox' && e.name != 'allbox' && e.checked == 1 ) {
          if (conf) {
             return confirm(text);
          } else {
             return 1;
          }
       }
    }
    return false;
};

function checkmail(e){
	var returnval=/^\w+[\+\.\w-]*@([\w-]+\.)*\w+[\w-]*\.([a-z]{2,4}|\d+)$/i.test(e);
	return returnval;
};
function getexpirydate( nodays){
   var UTCstring;
   Today = new Date();
   nomilli=Date.parse(Today);
   Today.setTime(nomilli+nodays*24*60*60*1000);
   UTCstring = Today.toUTCString();
   return UTCstring;
};
function getcookie(cookiename) {
   var cookiestring=""+document.cookie;
   var index1=cookiestring.indexOf(cookiename);
   if (index1==-1 || cookiename=="") return "";
   var index2=cookiestring.indexOf(';',index1);
   if (index2==-1) index2=cookiestring.length;
   return unescape(cookiestring.substring(index1+cookiename.length+1,index2));
};
function setcookie(name,value)
{
   duration=30;

   cookiestring=name+"="+escape(value)+";EXPIRES="+getexpirydate(duration);
   document.cookie=cookiestring;

};
function delcookie(name)
{
   cookiestring=name+"="+escape('')+";EXPIRES="+getexpirydate(-1);
   document.cookie=cookiestring;
};
var stopupload = false;