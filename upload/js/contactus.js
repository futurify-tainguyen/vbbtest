/*=======================================================================*\
|| ###################################################################### ||
|| # vBulletin 5.5.2
|| # ------------------------------------------------------------------ # ||
|| # Copyright 2000-2019 MH Sub I, LLC dba vBulletin. All Rights Reserved.  # ||
|| # This file may not be redistributed in whole or significant part.   # ||
|| # ----------------- VBULLETIN IS NOT FREE SOFTWARE ----------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html   # ||
|| ###################################################################### ||
\*========================================================================*/
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,["bademail","contact_us","nosubject","please_complete_required_fields","sentfeedback"]);(function(A){A(document).ready(function(){setTimeout(vBulletin.hv.reset,0);A("form.contactusForm").submit(function(F){F.preventDefault();var B=["name","email","subject","other_subject","message"],D={},E={},C,G=true;A.each(A(this).serializeArray(),function(H,I){if(A.inArray(I.name,B)!=-1){if(I.name!="other_subject"&&A.trim(I.value).length==0){G=false;return false}D[I.name]=I.value}else{if(C=/^humanverify\[([^\]]*)\]/.exec(I.name)){E[C[1]]=I.value}}});if(!G){vBulletin.warning("contact_us","please_complete_required_fields");return false}vBulletin.AJAX({call:"/ajax/api/contactus/sendMail",data:({maildata:D,hvinput:E}),success:function(H){vBulletin.alert("contact_us","sentfeedback",false,function(){window.location.href=pageData.baseurl})},api_error:function(H){A.each(H,function(I,J){if(/^humanverify_/.test(H[0])){vBulletin.hv.reset(true);return false}});return true},title_phrase:"contact_us",error_phrase:"invalid_server_response_please_try_again"});return false})})})(jQuery);