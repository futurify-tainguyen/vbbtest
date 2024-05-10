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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,["error_adding_infraction","error_adding_warning","error_reversing_infraction","give_infraction_ginfraction","infraction_added","infraction_reversed","please_check_the_box_to_reverse_infraction","please_specify_reason_to_reverse_infraction","received_infraction","received_warning","warning_added"]);vBulletin.infraction=vBulletin.infraction||{};vBulletin.privateMessage=vBulletin.privateMessage||{};(function(E){var D=E("#infractions-tab"),I=vBulletin.privateMessage&&vBulletin.privateMessage.jsReady||false,F,A=E("#privateMessageContainer");vBulletin.infraction.infractUser=function(O,J){var P=E(this);if(P.data("ajaxstarted")){return false}var N=P.hasClass("js-infraction-received"),L,K,M;if(N){L="received_infraction_form";K="received_infraction";M="receive-infraction-dialog"}else{L="give_infraction_form";K="give_infraction_ginfraction";M="give-infraction-dialog"}P.data("ajaxstarted",true);vBulletin.AJAX({call:"/ajax/render/"+L,data:{userid:P.data("userid"),nodeid:P.closest(".js-post-controls").data("node-id")||P.data("nodeid"),userInfraction:P.data("userinfraction")},complete:function(){P.data("ajaxstarted",null)},success:function(Q){if(E("."+M).length){E("."+M).replaceWith(Q)}else{E(Q).appendTo(document.body).hide()}var R=E("."+M);R.dialog({title:vBulletin.phrase.get(K),autoOpen:true,modal:true,resizable:false,closeOnEscape:false,showCloseButton:false,width:R.hasClass("error-infraction-dialog")?500:(N?600:700),dialogClass:"dialog-container infraction-dialog-container dialog-box",close:function(){var S=E(".js-editor",this);if(S.length&&vBulletin.ckeditor.editorExists(S)){vBulletin.ckeditor.destroyEditor(S)}E(this).dialog("destroy").remove()},open:function(){var V=this;if(!N){var Z=E(".infraction-send-pm",V);vBulletin.ajaxForm.apply(E(V),[{dataType:"json",error_phrase:"error_adding_infraction",success:function(d,f,h,c){var e=(d.infractionNodeid&&!d.infractionNodeid.errors),g="",b="warning";if(e){g=d.isWarning?"warning_added":"infraction_added";b=""}else{g=d.isWarning?"error_adding_warning":"error_adding_infraction"}vBulletin.alert("give_infraction_ginfraction",g,b);if(e){if(typeof J=="function"){J.apply(P.get(0),[d])}E(V).dialog("close")}},beforeSubmit:function(f,d,m){var k=E(".infraction-level-control option:selected",d).val();if(k=="0"){var l=function(n,o){X(E(".infraction-level",V));vBulletin.warning("give_infraction_ginfraction",n,function(){o.focus()})};if(E.trim(E(".custom-reason",d).val())==""){l("please_specify_custom_reason",E(".custom-reason",d));return false}var j=Number(E(".custom-points",d).val());if(isNaN(j)||j<0){l("please_specify_custom_points",E(".custom-points",d));return false}var h=E(".custom-period option:selected",d).val(),c=Number(E(".custom-expires",d).val());if(h!="N"&&(isNaN(c)||c<=0)){l("please_specify_custom_expires",E(".custom-expires",d));return false}}var b=E(".infraction-ban-reason .ban-reason",V);if(b.is(":visible")&&!E.trim(b.val())){X(E(".infraction-ban",V));E(".ban-reason-desc",V).removeClass("h-hide");b.focus();E(".dialog-content",V).scrollTop(0).scrollTop(E(".infraction-ban-reason",V).position().top);return false}var e=Number(Z.data("required"));if(e){var i=false,g=E(".js-editor",d);if(vBulletin.ckeditor.editorExists(g)){g=vBulletin.ckeditor.getEditor(g);if(!E.trim(g.getData())){i=true}}else{if(!E.trim(g.val())){i=true}}if(i){vBulletin.warning("give_infraction_ginfraction","please_specify_infraction_pm",function(){X(Z);g.focus()});return false}}}}]);var U=function(b){T.toggleClass("h-hide",!b).find("input, select").prop("disabled",false).end().find(".selectBox").toggleClass("selectBox-disabled",!b)};E(".infraction-level-control",V).on("change",function(h,g,f){var j=E(this.options[this.selectedIndex]);if(!(f&&f.length==1)){f=E(this).closest(".infraction-level").find(".infraction-warning-control input")}if(!g){if(j.data("allow-warning")){f.prop("disabled",false).val(this.value).parent().removeClass("h-hide");U(false)}else{f.prop("disabled",true).parent().addClass("h-hide");if(this.value=="0"){U(true);E(".textbox",T).first().focus()}}}var i=0,c=0;if(!f.prop("checked")&&this.value!="0"){i=Number(j.data("points"))||0;c=1}else{if(this.value=="0"){i=Number(E(".custom-infraction-info .custom-points",V).val())||0;if(i){c=1}}}var d=E(".infraction-dashboard-stats").data(),b=i&&W(Number(d.points)+i,Number(d.infractions)+c);Y(b);S()});E(".infraction-warning-control input",V).on("click",function(){(this.checked)?Y(false):E(".infraction-level-control",V).trigger("change",[true,this])});E(".custom-points",V).on("change",function(){E(".infraction-level-control",V).trigger("change",[true])});var T=E(".custom-infraction-info",V).removeClass("h-hide");E("select",V).selectBox();T.addClass("h-hide");E(".js-content-entry-panel, .js-editor",Z).data("callback",function(){S()});vBulletin.ckeditor.initEditorComponents(Z,true);E(".toggle-button",V).on("click",function(h){var d=E(this),b=d.closest(".blockrow-head"),g=b.next(".blockrow-body"),f=d.hasClass("expand");g.toggle(f);b.toggleClass("collapsed",!f);S();d.toggleClass("collapse expand");var c=d.attr("title");d.attr("title",d.data("toggle-title")).data("toggle-title",c);return false});var X=function(b){E(".toggle-button.expand",b).trigger("click")};var W=function(c,b){if(c==0&&b==0){return false}var d=false;E(".infraction-ban-list tbody tr",V).each(function(e,h){var j=E(this).data();if(j){var f=Number(j.points),g=Number(j.infractions);if((f&&c>=f)||(g&&b>=g)){d=true;return false}}});return d};var Y=function(b){var c=E(".infraction-ban-reason",V);if(b){c.removeClass("h-hide");X(E(".infraction-ban",V))}else{c.addClass("h-hide")}};var a=E(".dialog-content",V);var S=function(){if(!a[0]){a[0]=V}var b=(a[0].scrollHeight>parseFloat(a.css("max-height")));a.toggleClass("has-scrollbar",b)};S();E(".infraction-level-control",V).trigger("change")}else{vBulletin.ajaxForm.apply(E(".infraction-reverse-form",V),[{error_phrase:"error_reversing_infraction",success:function(c,d,e,b){vBulletin.alert("reverse_this_infraction","infraction_reversed");if(typeof J=="function"){J.apply(P.get(0),[c])}E(V).dialog("close")},beforeSubmit:function(e,d,c){var b,f;if(!E(".infraction-nodeid",d).is(":checked")){b="please_check_the_box_to_reverse_infraction";f=E(".infraction-nodeid",d)}else{if(!E.trim(E(".infraction-reason",d).val())){b="please_specify_reason_to_reverse_infraction";f=E(".infraction-reason",d)}}if(b){vBulletin.warning("reverse_this_infraction",b,function(){f.focus()});return false}return true}}]);E(".reverse-infraction",V).on("click",function(){E(".infraction-reverse-form",V).submit()})}E(".close-infraction",V).on("click",function(){E(V).dialog("close")});E(".ckeditor-bare-box.ckeditor-load-on-focus",V).on("focus",function(){vBulletin.ckeditor.initEditor(this.id,{complete:function(b){S()},error:function(b){E("#"+b).prop("disabled",false).removeClass("ckeditor-load-on-focus")}})})}})}})};vBulletin.infraction.loadUserInfractions=function(J){E.post(vBulletin.getAjaxBaseurl()+"/ajax/render/user_infractions",{userid:J.userid,pagenum:J.pageNumber},function(K,P,O){J.container.html(K);if(E(".pagenav-form",D).length){var L=new vBulletin.pagination({context:D,tabParamAsQueryString:false,allowHistory:D.find(".conversation-toolbar-wrapper").data("allow-history")==1,onPageChanged:function(Q,R){vBulletin.infraction.loadUserInfractions({container:D,userid:D.data("userid"),pageNumber:Q,replaceState:true})}})}if(typeof J.callback=="function"){J.callback(K)}if(J.pushState||J.replaceState){var N=vBulletin.makePaginatedUrl(location.href,J.pageNumber);if(!B){H=D.find(".conversation-toolbar-wrapper").data("allow-history")=="1";B=new vBulletin.history.instance(H)}if(B.isEnabled()){var M={from:"infraction_filter",page:J.pageNumber,tab:D.data("url-path")?D.data("url-path"):"#"+D.attr("id")};B[J.pushState?"pushState":"setDefaultState"](M,document.title,N)}}},"json")};var B,H;vBulletin.infraction.setHistoryStateChange=function(){if(!B){H=D.find(".conversation-toolbar-wrapper").data("allow-history")=="1";B=new vBulletin.history.instance(H)}if(B.isEnabled()){B.setStateChange(function(N){var M=B.getState();if(M.data.from=="infraction_filter"){B.log(M.data,M.title,M.url);var J=D.closest(".ui-tabs"),L=J.find(".ui-tabs-nav > li").filter('li:has(a[href*="#{0}"])'.format(D.attr("id")));if(L.hasClass("ui-tabs-active")){vBulletin.infraction.loadUserInfractions({container:D,userid:D.data("userid"),pageNumber:M.data.page,pushState:false})}else{var K=L.index();vBulletin.selectTabByIndex.call(J,K)}}},"infraction_filter")}};vBulletin.infraction.markInfractions=function(){var J,K,L;E(".infractions-list .list-item").each(function(){L=E(this);K=L.data("nodeId");J=vBulletin.cookie.fetchBbarrayCookie("discussion_view",K);if(J){L.addClass("read")}})};E(document).off("click",".js-post-control__infraction").on("click",".js-post-control__infraction",function(J){vBulletin.infraction.infractUser.apply(this,[J,function(L){var N=E(this),K=N.find(".b-icon"),M=N.hasClass("js-infraction-received");K.removeClass("b-icon__tickets--neutral b-icon__tickets--warned b-icon__tickets--infracted");if(M){N.removeClass("js-infraction-received").attr("title",vBulletin.phrase.get("give_infraction_ginfraction"));K.addClass("b-icon__tickets--neutral")}else{N.addClass("js-infraction-received");if(L.isWarning){N.attr("title",vBulletin.phrase.get("received_warning"));K.addClass("b-icon__tickets--warned")}else{N.attr("title",vBulletin.phrase.get("received_infraction"));K.addClass("b-icon__tickets--infracted")}}}])});D.off("click",".infractionCtrl").on("click",".infractionCtrl",function(J){vBulletin.infraction.infractUser.apply(this,[J,function(K){vBulletin.infraction.loadUserInfractions({container:D,userid:E(this).data("userid"),pageNumber:1,pushState:Number(E('.pagenav-form input[name="page"]',D).val())!=1})}])});D.on("click",".view-infraction",function(J){vBulletin.infraction.infractUser.apply(this,[J,function(K){var L=Number(E('.pagenav-form input[name="page"]',D).val())||1;vBulletin.infraction.loadUserInfractions({container:D,userid:E(this).data("userid"),pageNumber:L,pushState:L!=1})}])});E(document).off("click","#privateMessageContainer .js-button-group .view-infraction").on("click","#privateMessageContainer .js-button-group .view-infraction",function(J){vBulletin.infraction.infractUser.apply(this,[J,function(K){}])});E(".infraction-delete").off("click").on("click",function(L){$button=E(this);var J,K=false;if($button.parents("#pmFloatingBarContent").hasClass("infractions-paginator")){J=getSelectedMessages()}else{J=[E("#privateMessageContainer .js-conversation-starter").data("nodeId")];K=true}if(J.length>0){openConfirmDialog({title:vBulletin.phrase.get("messages_header"),message:vBulletin.phrase.get("are_you_sure_delete_infractions"),iconType:"warning",onClickYes:function(){vBulletin.AJAX({url:vBulletin.getAjaxBaseurl()+"/ajax/api/node/deleteNodes",data:{nodeids:J,hard:0},success:function(M){if(K){location.href=E("#pmBtnBackToInfractions").prop("href")}else{location.reload()}}})}})}});E(".infraction-mark_as_read").off("click").on("click",function(M){var L=this;var K=getSelectedMessages();if(K.length>0){if(pageData.threadmarking=="0"||pageData.userid=="0"){for(var J in K){vBulletin.cookie.setBbarrayCookie("discussion_view",K[J],Math.round(new Date().getTime()/1000));E("[data-node-id={0}]".format(K[J]),".infractions-list").addClass("read").find(".privateMessageActionCheck").attr("checked",false)}}else{vBulletin.AJAX({url:vBulletin.getAjaxBaseurl()+"/ajax/api/node/markReadMultiple",data:{nodeids:K},success:function(N){E(N).each(function(){E("[data-node-id={0}]".format(this),".infractions-list").addClass("read").find(".privateMessageActionCheck").attr("checked",false);var O=E("[data-node-id={0}]".format(this),".infractions-list");O.addClass("read");O.find(".privateMessageActionCheck").attr("checked",false)})}})}}});E(".infraction-mark_as_unread").off("click").on("click",function(M){var L=this;var K=getSelectedMessages();console.log(K);if(K.length>0){if(pageData.threadmarking=="0"||pageData.userid=="0"){for(var J in K){vBulletin.cookie.unsetBbarrayCookie("discussion_view",K[J]);E("[data-node-id={0}]".format(K[J]),".infractions-list").removeClass("read").find(".privateMessageActionCheck").attr("checked",false)}}else{vBulletin.AJAX({url:vBulletin.getAjaxBaseurl()+"/ajax/api/node/markUnreadMultiple",data:{nodeids:K},success:function(N){E(N).each(function(){E("[data-node-id={0}]".format(this),".infractions-list").removeClass("read").find(".privateMessageActionCheck").attr("checked",false)})}})}}});E(document).ready(function(){if(E(".infractions-list").length>0){if(pageData.threadmarking=="0"||pageData.userid=="0"){vBulletin.infraction.markInfractions()}}if(E("#pmBtnBackToInfractions").length>0){var J=E("#privateMessageContainer .conversation-list .b-post--infraction").data("nodeId");if(pageData.threadmarking=="0"||pageData.userid=="0"){vBulletin.cookie.setBbarrayCookie("discussion_view",J,Math.round(new Date().getTime()/1000))}else{vBulletin.markRead(J)}}});E("#infractionFilters").trigger("reset").find(".filter-options input").off("click").on("click",function(J){C.apply(this)});E(document).off("click","#privatemessagePaging .infractionsPrev").on("click","#privatemessagePaging .infractionsPrev",function(K){K.preventDefault();var J=E(this).closest("#privatemessagePaging").find(':input[type=hidden][name="prev-page"]').val();G(E(this),J)});E(document).off("click","#privatemessagePaging .infractionsNext").on("click","#privatemessagePaging .infractionsNext",function(K){K.preventDefault();var J=E(this).closest("#privatemessagePaging").find(':input[type=hidden][name="next-page"]').val();G(E(this),J)});F=function(Q){var L=E("#privatemessagePaging"),N=L.find("input[name='pagenum']"),J=N.val(),M=L.find("#maxPageNum"),O=M.val(),P=E("#infractionFilters"),K={};vBulletin.privateMessage.updatePaginationLinks(A,J,O);vBulletin.privateMessage.getPageFiltersForUrl=function(R){P.find("input:checked").each(function(){R["filter_"+this.name]=this.value});return R};if(E("#infractionsFilter").length){E("#infractionFilters input:checked").each(function(){queryParams["filter_"+this.name]=this.value})}if(vBulletin.privateMessage.pmFilterHistory.isEnabled()){K={infractionFilterParams:{setCurrentPage:J,options:{},maxPage:O,},};P.find("input:checked").each(function(){K.infractionFilterParams["options"][this.name]=this.value});if(K.infractionFilterParams.options.time){K.infractionFilterParams.options.time={from:K.infractionFilterParams.options.time}}vBulletin.privateMessage.pmFilterHistory.setDefaultState(K,document.title,window.location.href);vBulletin.privateMessage.pmFilterHistory.setStateChange(function(S){var R=vBulletin.privateMessage.pmFilterHistory.getState();if(R.data.hasOwnProperty("infractionFilterParams")){var T=R.data.infractionFilterParams;E.each(T.options,function(V,W){var U="input[name='{0}'][value='{1}']".format(V,W);P.find(U).attr("checked","checked")});M.val(T.maxPage);N.val(T.setCurrentPage);G(null,T.setCurrentPage,true,true)}},"privatemessage")}I=true};if(I){F()}else{E(document).one("vb-privatemessage-js-ready",F)}E(document).off("keypress","#privatemessagePaging .infractionsPageTo").on("keypress","#privatemessagePaging .infractionsPageTo",function(K){if(K.keyCode==13){K.preventDefault();var L=E(this);var J=parseInt(L.val(),10);if(isNaN(J)){openAlertDialog({title:vBulletin.phrase.get("error"),message:vBulletin.phrase.get("please_enter_a_valid_page_number"),iconType:"error"});return false}G(L,J)}});function G(J,S,K,M){if(!J){J=E("#private-message-toolbar .infractions-paginator .infractionsPageTo");if(J.length==0){return false}}updateCounter=K||0;M=M||false;var P=J.closest(".infractions-paginator");var R=E("#privateMessageContainer .main-pane .pending-posts-container");var N=P.find("#maxPageNum").filter(":input[type=hidden]").val();var O=parseInt(P.find(":input[type=hidden][name=pagenum]").val(),10);var Q=parseInt(P.find(":input[type=hidden][name=per-page]").val(),10);S=parseInt(S,10);if(isNaN(S)||isNaN(N)||isNaN(O)){return false}if((S<1)||(S>N)){openAlertDialog({title:vBulletin.phrase.get("error"),message:vBulletin.phrase.get("please_enter_a_valid_page_number"),iconType:"error"});return false}else{if((S==O)&&!K){return false}}var L={setCurrentPage:S,setPerPage:Q,getPagingInfo:K,options:{}};E("#infractionFilters input:checked").each(function(){L.options[this.name]=this.value});if(L.options.time){L.options.time={from:L.options.time}}E.ajax({url:vBulletin.getAjaxBaseurl()+"/ajax/render/privatemessage_infraction_main",type:"POST",data:L,dataType:"json",success:function(V){R.html(V);if(E(".infractions-list").length>0){if(pageData.threadmarking=="0"||pageData.userid=="0"){vBulletin.infraction.markInfractions()}}var U=0;var T=0;if(S<N){if(S>1){T=S+1;U=S-1}else{T=S+1}}else{U=S-1}P.find(":input[type=hidden][name=pagenum]").val(S);P.find(":input[type=hidden][name=next-page]").val(T);P.find(":input[type=hidden][name=prev-page]").val(U);if(T){P.find(".infractionsNext").removeClass("h-disabled")}else{P.find(".infractionsNext").addClass("h-disabled")}if(U){P.find(".infractionsPrev").removeClass("h-disabled")}else{P.find(".infractionsPrev").addClass("h-disabled")}P.find(".infractionsPageTo").val(S);if(K){var Z=E("#privateMessageContainer .main-pane .pending-posts-container .pending-posts-pageinfo"),Y=parseInt(Z.find(".totalpages").val(),10);P.find("#maxPageNum").filter(":input[type=hidden]").val(Y);N=Y;P.find(".infractionsPageCount").text(Y)}if(I){vBulletin.privateMessage.updatePaginationLinks(A,S,N)}if(I&&!M&&vBulletin.privateMessage.pmFilterHistory.isEnabled()){L.maxPage=N;var X=vBulletin.privateMessage.getPrivateMessageUrl(S),W={infractionFilterParams:L,};vBulletin.privateMessage.pmFilterHistory.pushState(W,document.title,X.url)}},error:function(){openAlertDialog({title:vBulletin.phrase.get("error"),message:vBulletin.phrase.get("unable_to_contact_server_please_try_again"),iconType:"error"})}})}var C=function(K){$paginateButton=E("#private-message-toolbar .infractions-paginator .infractionsPageTo");if($paginateButton.length){G($paginateButton,1,true);return }var J={};E(this).closest(".filter-options-list").find("input:checked").each(function(){J[this.name]=E(this).val()});if(J.time){J.time={from:J.time}}E(this).attr("checked","checked");J.page=1;J.perpage=pmPerPage;var L={options:J};E.ajax({url:vBulletin.getAjaxBaseurl()+"/ajax/render/privatemessage_infraction_main",type:"POST",dataType:"json",data:L,success:function(M){E("#privateMessageContainer .pending-posts-container").html(M);pmPageNum=1;if(E(".infractions-list").length>0){if(pageData.threadmarking=="0"||pageData.userid=="0"){vBulletin.infraction.markInfractions()}}}})}})(jQuery);