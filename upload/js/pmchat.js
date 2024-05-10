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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,["error","pmchat_close_button_label","pmchat_idle_disconnected_message","pmchat_idle_disconnected_title","pmchat_reconnect_button_label"]);window.vBulletin.options=window.vBulletin.options||{};window.vBulletin.options.precache=window.vBulletin.options.precache||[];window.vBulletin.options.precache=$.merge(window.vBulletin.options.precache,["pmchat_enabled","pmchat_header_polling_interval","pmchat_chat_polling_idle_timeout","pmchat_chat_polling_min_interval","pmchat_chat_polling_max_interval"]);(function(F){var A=pageData.baseurl_pmchat;function E(H){var I="";if(H.hasOwnProperty("messageid")){I="?messageid="+encodeURIComponent(H.messageid)}else{I="?aboutNodeid="+encodeURIComponent(H.aboutNodeid)+"&toUserid="+encodeURIComponent(H.toUserid)}if(I==""){return"#"}return A+I}function C(K){var H,I="width=600,height=700,resizable=yes,scrollbars=yes,status=yes",J;if(K.hasOwnProperty("url")){H=K.url}else{H=E(K)}if(H=="#"){return }J=window.open(H,"_blank",I);if(!J||J.closed||typeof J.closed=="undefined"){window.open(H+queryString)}}function B(){var L=F(".js-pmchat__dropdown");var g=L.find(".js-pmchat__messages-count");var f=F(".notifications-count").not(".js-pmchat__messages-count");var c=L.find(".js-pmchat__dropdown-submenu");var T=c.find(".js-pmchat__dropdown__single-pm-template").removeClass("js-pmchat__dropdown__single-pm-template");var h=c.find(".js-pmchat__insert-marker");var S=".js-pmchat__submenu__ephemeral";var M=L.find(".js-pmchat__submenu__loading-icon");var Q=L.find(".js-pmchat__header-data");var a=parseInt(Q.data("messages-folderid"),10);var d=vBulletin.options.get("pmchat_header_polling_interval")||30;var P;var J=0;var b=60;var Z=vBulletin.options.get("pmchat_chat_polling_idle_timeout")||5*60;var e=0;var U={};var V=0;function R(){var j={},i=F(this).attr("href");if(i!="#"){j.url=i}else{j.messageid=F(this).data("starter")}C(j);F(this).parent().removeClass("b-comp-menu-dropdown__content-item--unread");return false}function I(o){e=Date.now();var l=o[Object.keys(o)[0]],n=l&&l.orderListForJs||[],r=n.length,p=false,u=false,m={};if(r>0){c.find(S).remove()}else{return false}for(var q=0;q<r;q++){var k=n[q];if(o.hasOwnProperty(k)){var v=o[k],j=T.clone(true),s=j.find(".js-pmchat__submenu__link"),t="js-pmchat__messagefinder__"+k;j.removeClass("h-hide-imp");s.attr("data-starter",k).prop("title",v.previewtext).attr("href",E({messageid:k}));j.find(".js-pmchat__submenu__title").html(v.title);if(v.msgread==0){j.addClass("b-comp-menu-dropdown__content-item--unread")}j.addClass(t);j.insertBefore(h);u=(V>0&&(!U.hasOwnProperty(k)||U[k]["publishdate"]!=v.publishdate||U[k]["userid"]!=v.userid)&&v.msgread==0&&v.userid!=pageData.userid);if(u){p=true}m[k]={publishdate:v.publishdate,userid:v.userid,title:v.title,previewtext:v.previewtext,findmehelper:t,isNew:u}}}U=m;V=e;if(h.prev(".b-comp-menu-dropdown__content-item").length>0){h.next(".b-comp-menu-dropdown__content-item").addClass("b-comp-menu-dropdown__content-item--divider").removeClass("b-comp-menu-dropdown__content-item--first")}else{h.next(".b-comp-menu-dropdown__content-item").removeClass("b-comp-menu-dropdown__content-item--divider").addClass("b-comp-menu-dropdown__content-item--first")}if(p){X()}}function X(){console.log("New Messages found in polling! Notifying user");var p=L.find(".js-comp-menu-dropdown").hasClass("b-comp-menu-dropdown--open");if(p){var n,k=[];for(n in U){if(U.hasOwnProperty(n)&&U[n]["isNew"]){k.push("."+U[n]["findmehelper"])}}if(k.length>0){var o=k.join(", "),m=F(o),l=setInterval(function(){m.toggleClass("b-comp-menu-dropdown__content-item--unread-alert")},100),j=function(){clearInterval(l);m.removeClass("b-comp-menu-dropdown__content-item--unread-alert")},i=3;setTimeout(j,i*1000)}}else{L.find(".js-comp-menu-dropdown").addClass("b-comp-menu-dropdown--alert")}}function O(k){L.find(".js-comp-menu-dropdown").removeClass("b-comp-menu-dropdown--alert");J=0;var i=d;if((Date.now()-e)<i*1000){console.log("PM Load aborted: less than "+i+" seconds since last load.");return }M.removeClass("h-hide-imp");var j=function(){M.addClass("h-hide-imp");W();if(typeof vBulletin.CompMenuDropdown.updateMenuFormat=="function"){vBulletin.CompMenuDropdown.updateMenuFormat()}};K(j)}function H(){console.log("Idle detection enabled");var i=setInterval(Y,b*1000);F(document).on("click keypress scroll",function(j){J=0})}function Y(){J+=b;if(J>=Z){W()}}function W(){clearTimeout(P);if(J>=Z){console.log("Header polling stopped due to idle timeout. (idle time: "+J+"s)");return }P=setTimeout(N,d*1000)}function N(){W();console.log("Polled for header data!"+Date.now());K()}function K(j){var i=false;vBulletin.loadingIndicator.suppressNextAjaxIndicator();vBulletin.AJAX({call:"/chat/loadheaderdata",success:function(l){var k=l.headerCounts.messages,o=g.text(),m=l.headerCounts.nonpms_sum,n=f.text();if(o!=k){g.text(k);if(k>0){g.removeClass("h-hide-imp")}else{g.addClass("h-hide-imp")}i=true}if(n!=m){f.text(m);if(m>0){f.removeClass("h-hide-imp")}else{f.addClass("h-hide-imp")}i=true}if(i){console.log("Header messages/notification count(s) changed with updated data from loadheaderdata")}else{}if(!(F.isEmptyObject(l.messages))){I(l.messages)}},emptyResponse:function(){console.log("/chat/loadheaderdata returned an empty response!")},error:function(k,m,l){console.log("/chat/loadheaderdata failed!");console.log("----------------");console.log("jqXHR:");console.dir(k);console.log("text status:");console.dir(m);console.log("error thrown:");console.dir(l);console.log("----------------")},complete:j})}c.off("click").on("click",".js-pmchat__submenu__link",R);L.find(".js-comp-menu-dropdown__trigger").on("click",O);H();P=setTimeout(N,d*1000)}function D(){var P=F(".js-pmchat__container");var K=F(".js-pmchat__participants");var AF=P.find(".js-pmchat__thread-container");var y=P.find("form");var L=P.find(".js-pmchat__data");var f=parseInt(L.data("pmchannelid"),10);var r=parseInt(L.data("parentid"),10);var e=parseInt(L.data("pm_messageid"),10);var T=parseInt(L.data("to_userid"),10);var u=L.data("pm_title");var d=0;var g=F(".js-pmchat__insert-marker");var h=AF.find(".js-pmchat__thread-placeholder");var j;var V=false;if(f==r||!(e>0)){V=true}var X=0;var AK;var J=Date.now();var v;var w;var AG=1;var c=vBulletin.options.get("pmchat_chat_polling_min_interval")||1;var k=vBulletin.options.get("pmchat_chat_polling_max_interval")||30;if(k<c){var R=k;k=c;c=R}w=(c*10+k*1)/11;v=w;var a=vBulletin.options.get("pmchat_chat_polling_idle_timeout")||5*60;var t=0;var I=10;var Q=I-1;var n=[];var AE;for(AE=0;AE<I;++AE){n[AE]=0}var S={messagesLoading:false,queued:false};var o=false;var p=F(".js-participants-collapser-ui");var s=F(".js-participants-collapsible");var AB=s.find(".js-vbscroller-wrapper");var AA=F(".js-participants-show-add-recipients-ui");var AI=F(".js-participants-add-recipients-wrapper");var m=y.find(".js-wrapper-contententry_title");var O=y.find(".js-wrapper-contententry__msgRecipients");function AH(){var AN=F(".js-pmchat__insert-marker"),AM=AN.offset().top,AL=F(".js-pm-content-entry-container"),AO=AL.outerHeight(),AQ=(AL.css("position")=="fixed"),AP=F(window).outerHeight(),i=(AM>(AP-AO));if(!AQ||i){vBulletin.animateScrollTop(AM)}}function l(AL){if(V){console.log("Nothing to load, awaiting first message.");return }if(S.messagesLoading){console.log("loadNewMessages() rejected: Still awaiting messages from a previous call.");return }console.log("loadNewMessages() executing.");var i=[];AF.find(".js-pmchat__post-wrapper").each(function(AP,AO){var AN=F(AO).data("publishdate");if(AN&&AN>d){d=AN}var AM=F(AO).data("nodeid");if(AM&&AM>0){i.push(AM)}if(AN&&d>AN){F(AO).removeClass("js-pmchat__post-wrapper")}});S.messagesLoading=true;vBulletin.loadingIndicator.suppressNextAjaxIndicator();vBulletin.AJAX({call:"/chat/loadnewmessages",data:{parentid:r,newreplyid:AL,lastpublishdate:d,loadednodes:i,},success:function(AM){Q=(Q+1)%I;if(AM.html&&AM.html!==""){n[Q]=1;t=Date.now();console.log("loadNewMessages(): Inserting new messages.");F(AM.html).insertBefore(g);AH()}else{n[Q]=-1;console.log("loadNewMessages(): No new messages.")}if(AM.nodeids){}},error:function(AM){console.log("loadNewMessages(): Error loading new PMs. Ajax result:");console.log(AM)},complete:function(AM){window.vBulletin.loadingIndicator.hide();S.messagesLoading=false;if(S.queued){S.queued=false;window.vBulletin.loadingIndicator.show();setTimeout(l,0)}U()}})}function U(){clearTimeout(AK);var AO=Date.now(),AM=(AO-t),i=(AO-X),AN=(n[Q]>0),AP=n.reduce(function(AR,AQ){return AR+AQ})/I;if(t>0){if(AN){if(v>w){v=w}v-=AG;if(t>J){J=t}}else{v-=(AP*AG)}}else{v+=AG}if(v<c){v=c}else{if(v>k){v=k}}X=AO;if(o){o=false;J=AO}var AL=(AO-J)/1000;console.log("Idle time: "+AL+"s. (timeout: "+a+"s)");if(AL>=a){console.log("Polling for messages stopped due to idle timeout. Idle start time: "+J+" time now: "+AO);AD();J=AO;return }AK=setTimeout(N,v*1000);console.log("Polling for messages complete. Next poll in "+v+"seconds. Time since last poll: "+(i/1000)+"s, since last hit: "+(AM/1000)+"s.")}function N(){U();l()}function AD(){if(j==null){var AL=function(){J=Date.now();v=w;N()};var i={title:vBulletin.phrase.get("pmchat_idle_disconnected_title"),message:vBulletin.phrase.get("pmchat_idle_disconnected_message"),width:"50%",buttonLabel:{yesLabel:vBulletin.phrase.get("pmchat_reconnect_button_label"),noLabel:vBulletin.phrase.get("pmchat_close_button_label")},onClickYes:AL,onClickNo:function(){window.close()}};j=openConfirmDialog(i)}else{j.dialog("open")}}function q(){var i=y.attr("ck-editorid")||F(".js-editor",y).attr("id"),AL=F("#"+i);AL.on("afterInit",function(){var AN=vBulletin.ckeditor.getEditor(i);if(!AN){console.log("Editor not found, cannot set predefined starter PM text");return }console.log("Setting predefined starter PM text: "+L.data("pm_textprefill"));var AM=L.data("pm_textprefill");if(AM){AM+="&nbsp;"}AN.setData(AM,function(){vBulletin.ckeditor.fixTableFunctionality.call(vBulletin.ckeditor,{},AN);var AP=O.find(".autocompleteHelper");if(AP.length>0){AP.focus()}else{AN.focus()}if(AM){var AO=AN.createRange();AO.moveToPosition(AO.root,CKEDITOR.POSITION_BEFORE_END);AN.getSelection().selectRanges([AO])}AH()})})}function AC(AO){var AN=F(this);AN.prop("disabled",true);J=Date.now();if(V){var AL=y.find('input[name="msgRecipients"]');if(AL.length==0&&T){console.log({msg:"sentto input added with prefill:",to_userid:T,});F("<input>").attr({type:"hidden",name:"sentto[]",value:T}).appendTo(y)}else{console.log({msg:'User editable input[name="msgRecipients"] was found. Prefilled sentto skipped.',})}var AM=y.find('input[name="title"]');if(AM.length==0){console.log({msg:"title input added with prefill:",pm_title:u,});F("<input>").attr({type:"hidden",name:"title",value:u}).appendTo(y)}else{console.log({msg:'User editable input[name="title"] was found. Prefilled title skipped.',})}}else{var AP=y.find('input[name="respondto"]');if(AP.length>0){AP.val(e)}else{F("<input>").attr({type:"hidden",name:"respondto",value:e}).appendTo(y)}var i=y.find('input[name="msgtype"]');if(i.length==0){F("<input>").attr({type:"hidden",name:"msgtype",value:"message"}).appendTo(y)}}if(V){h.addClass("h-hide")}window.vBulletin.loadingIndicator.show();vBulletin.AJAX({url:y.attr("action"),data:y.serialize(),success:function(AQ){if(AQ&&AQ.nodeId){var AR=parseInt(AQ.nodeId,10);if(V){y.find('input[name="parentid"]').val(AR);L.data("parentid",AR);L.data("pm_messageid",AR);L.attr("data-parentid",AR);L.attr("data-pm_messageid",AR);e=AR;r=AR;V=false;AJ(AR)}console.log("==================NEW MESSAGE POSTED, LOADING NEW MESSAGES!");if(S.messagesLoading){S.queued=true}else{l(AR)}vBulletin.contentEntryBox.resetForm(y,false,function(){var AT=y.attr("ck-editorid")||F(".js-editor",y).attr("id"),AS=vBulletin.ckeditor.getEditor(AT);vBulletin.hv.reset();AS.focus()})}},api_error:function(AQ){vBulletin.hv.resetOnError();window.vBulletin.loadingIndicator.hide();return true},complete:function(AQ){AN.prop("disabled",false)}});return false}function AJ(i){if(!i){console.log("Missing nodeid for loadParticipants()");return }m.remove();O.remove();y.attr("data-message-type","pm-reply");var AL=pageData.baseurl_pmchat+"?messageid="+i;History.replaceState({},u,AL);H(i)}function H(i,AL){vBulletin.AJAX({call:"/chat/loadparticipants",data:{nodeid:i},success:function(AM){var AN=F(".js-pmchat__participants-insert-marker");if(AM.participants_html&&AM.participants_html!=""&&AN.length){AN.html(AM.participants_html);AB.trigger("event-js-content-change")}if(K.hasClass("h-hide")){K.removeClass("h-hide");p.click();s.removeClass("h-hide")}if(AM.phrase&&AM.phrase!=""){F(".js-participants-count-phrase").text(AM.phrase)}if(AM.title&&AM.title!=""){F(document).find("title").html(AM.title)}if(AL&&AL.success&&typeof AL.success=="function"){return AL.success.apply(this,[AM])}},complete:function(AN,AO,AM){if(AL&&AL.complete&&typeof AL.complete=="function"){return AL.complete.apply(this,[AN,AO,AM])}},error:function(AM,AO,AN){console.log("/ajax/chat/loadparticipants failed!");console.log("----------------");console.log("jqXHR:");console.dir(AM);console.log("text status:");console.dir(AO);console.log("error thrown:");console.dir(AN);console.log("----------------")}})}function W(AM){AM.preventDefault();var i=F(this).attr("href"),AL;AL=window.open(i,"_blank");if(!AL||AL.closed||typeof AL.closed=="undefined"){window.open(i)}}function b(AM){var AL={height:"toggle"},i={duration:"fast",start:function(){AB.trigger("event-js-element-resize-start")},progress:function(AP,AN,AO){AB.trigger("event-js-element-resize")},complete:function(){AB.trigger("event-js-element-resize")},};s.animate(AL,i);AM.preventDefault()}function M(){var AO=function(AT){var AS=(AB.height()>0);if(AT.data&&AT.data.command){switch(AT.data.command){case"open":AI.removeClass("h-hide");break;case"close":AI.addClass("h-hide");break;default:if(AS){AI.toggleClass("h-hide")}else{AI.removeClass("h-hide")}break}}else{AI.toggleClass("h-hide")}if(!AS){b(AT)}else{AB.trigger("event-js-element-resize")}AT.preventDefault()},AN={},AL=function(AS,AU,AT){if(typeof AT!="undefined"&&typeof AT.id!="undefined"&&AT.id){AN[AU]=AT.id}},AR={apiClass:"user",afterAdd:AL,delimiter:";",},i=F(".js-participants-add-recipients-input"),AM=new vBulletin_Autocomplete(F(".js-participants-add-recipients-input"),AR),AQ=function(AS){AM.clearElements();if(AS.data){AS.data.command="close"}else{AS.data={command:"close"}}AO(AS)},AP=function(AT){var AS=AM.getInputField(),AV=AS.val();if(AV){AS.val("");AM.addElement(AV,AV)}var AU=AM.getValues();vBulletin.AJAX({call:"/ajax/api/content_privatemessage/addPMRecipientsByUsernames",data:{pmid:r,usernames:AU,usernamesToIds:AN,},success:function(AW){var AX=function(){AQ(AT);if(S.messagesLoading){S.queued=true}else{l()}},AY=function(){window.vBulletin.loadingIndicator.hide()},AZ={success:AX,complete:AY};H(r,AZ)},})};console.log({addRecipientsAutoComplete:AM,});AA.off("click").on("click",{command:"toggle"},AO);AI.find(".js-add-recipients-cancel").off("click").on("click",{command:"close"},AQ);AI.find(".js-add-recipients-submit").off("click").on("click",AP)}function Z(){F(document).on("click keypress scroll",function(AM){o=true});var i=y.attr("ck-editorid")||F(".js-editor",y).attr("id"),AL=F("#"+i);AL.on("afterInit",function(){var AM=vBulletin.ckeditor.getEditor(i);AM.on("contentDom",function(){var AO=AM.editable();if(AO==null){return }function AN(){o=true}AO.attachListener(AO,"keypress",AN);AO.attachListener(AO,"click",AN);AO.attachListener(AO,"focus",AN)})})}function x(AL){var AM=AL+"-replacement",i=F("."+AL),AO=F("."+AM);if(AO.length==0){AO=F("<div />").addClass(AM).insertAfter(i)}function AN(){if(i.css("position")=="fixed"){AO.removeClass("h-hide");var AP=i.outerHeight()-30;AO.css("height",AP+"px")}else{AO.addClass("h-hide")}}F(AN);F(document).off("click",AN).on("click",AN);vBulletin.Responsive.Debounce.registerCallback(AN);CKEDITOR.on("instanceReady",function(AP){AN();AP.editor.on("focus",function(){setTimeout(function(){AN()},0)})});i.on("event-js-element-resize",AN)}function Y(){var AL=F("#debug-information-wrapper");if(AL.length==1){function i(){if(F("body").is(".l-xsmall")){AL.appendTo("body");AL.find("#debug-information").css("margin-top",20)}else{AL.insertBefore(".js-pm-content-entry-container");AL.find("#debug-information").css("margin-top",0)}}i();vBulletin.Responsive.Debounce.registerCallback(i)}}var z=new vBulletin_Autocomplete(F(".privatemessage_author"),{apiClass:"user"});F(".js-pm-content-entry-container .js-pmchat-submit").off("click").on("click",AC);AF.on("click","a:not('.js-pmchat-ignoreanchor')",W);K.on("click","a:not('.js-pmchat-ignoreanchor')",W);F("body").on("click","a.js-pmchat-ignoreanchor",function(i){i.preventDefault()});p.off("click").on("click",b);if(AA.length>0&&AI.length>0){M()}F(".js-pm-content-entry-container .js-button").enable();AH();N();Z();window.setTimeout(q,0);x("js-pm-content-entry-container");x("js-pmchat__header");Y()}function G(){F(function(){F("body").off("click",".js-pmchat-link").on("click",".js-pmchat-link",function(I){I.preventDefault();var H={url:F(this).attr("href")};C(H);return false});$pmchatDropdown=F(".js-pmchat__dropdown");if($pmchatDropdown.length>0){if(!$pmchatDropdown.attr("data-initialized")){console.log("Initializing PM Dropdown!");$pmchatDropdown.attr("data-initialized",true);B()}else{console.log("PM Dropdown already initialized. Skipping re-init.")}}else{console.log("PM Dropdown not detected, skipping init.")}$pmchatContainer=F(".js-pmchat__container");if($pmchatContainer.length>0){if(!$pmchatContainer.attr("data-initialized")){console.log("Initializing PM Chat window!");$pmchatContainer.attr("data-initialized",true);D()}else{console.log("PM Chat window already initialized. Skipping re-init.")}}else{console.log("PM Chat window not detected, skipping init.")}})}G()})(jQuery);