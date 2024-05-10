// ***************************
// js.compressed/sb_groupadmin.js
// ***************************
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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,["add_moderator_gforumdisplay","confirm_delete_group_channel","delete_a_group_channel","delete_group","edit_group_icon","error_adding_moderator","error_deleting_group","group_icon","group_title_exists","groups","kilobytes","loading_ellipsis","manage_subscribers","transfer_group_ownership","unexpected_error"]);var sgIconUploadDlg=false;var sgIconEditDlg=false;(function(A){var B=[".blogadmin-widget",".summary-widget"];if(!vBulletin.pageHasSelectors(B)){return false}A(document).ready(function(){A(".btnGroupReset").off("click").on("click",function(J){if(A(".groupIcon .groupIconUrl").val()){A(".groupIcon .groupIconImg").attr("src",A(".groupIcon .groupIconUrl").val());A(".groupIcon .groupFileDataId").val(A(".groupIcon .initFiledataid").val())}else{A(".groupIcon .groupIconImg").addClass("h-hide");A(".groupIcon .groupFileDataId").val("")}return true});var G=new vBulletin_Autocomplete(A(".moderator_input"),{apiClass:"user",maxItems:1,containerClass:"group-moderator-autocomplete",});A(document).off("click",".add_moderator").on("click",".add_moderator",function(K){K.stopPropagation();$moderatorForm=A(this).closest("form");var J=$moderatorForm.find(':input[name="nodeid"]').val();recipient=G.getElements()[0]["value"];A("body").css("cursor","wait");vBulletin.AJAX({call:"/ajax/api/node/requestChannel",data:{channelid:J,recipientname:recipient,requestType:"sg_moderator_to"},success:function(){location.reload()},complete:function(){A("body").css("cursor","default")},title_phrase:"add_moderator_gforumdisplay",error_phrase:"error_adding_moderator",})});A(document).off("click",".btnRemoveContributor").on("click",".btnRemoveContributor",function(M){M.stopPropagation();var K=A(this).closest("form"),L=K.find(':input[name="nodeid"]').val(),J=A(this).attr("userid");A("body").css("cursor","wait");vBulletin.AJAX({call:"/ajax/api/blog/removeChannelModerator",data:{userId:J,channelId:L},success:function(N){location.reload()},complete:function(){A("body").css("cursor","default")},title_phrase:"delete_moderator",})});A(document).off("click",".btnCancelTransfer").on("click",".btnCancelTransfer",function(N){N.stopPropagation();var M=A(this),K=M.closest("form"),L=K.find(':input[name="nodeid"]').val(),J=M.attr("userid");A("body").css("cursor","wait");vBulletin.AJAX({call:"/ajax/api/blog/cancelChannelTransfer",error_phrase:"error_transfer_ownership",data:{userId:J,channelId:L},success:function(){location.reload()},complete:function(){A("body").css("cursor","default")}})});A(document).off("click",".btnTransferOwnership").on("click",".btnTransferOwnership",function(M){var J=A(this).closest("form"),K=J.find(':input[name="nodeid"]').val();var L=openConfirmDialog({title:vBulletin.phrase.get("transfer_group_ownership"),message:vBulletin.phrase.get("loading_ellipsis"),width:500,dialogClass:"transfer-ownership-dialog loading",buttonLabel:{yesLabel:vBulletin.phrase.get("send_request"),noLabel:vBulletin.phrase.get("cancel")},onClickYes:function(){var O="",N=0;if(A(".transfer-ownership-dialog .transfer_owner_select:visible").length>0){N=A(".transfer-ownership-dialog .transfer_owner_select").val()}else{O=A('.transfer-ownership-dialog :input[name="transfer_owner_autocomplete"]').val()}if(O.length==0&&(N.length==0||N==0)){return false}vBulletin.AJAX({call:"/ajax/api/node/requestChannel",data:{channelid:K,recipient:N,recipientname:O,requestType:"owner_to"},success:function(){location.reload()},complete:function(){A("body").css("cursor","default")},title_phrase:"transfer_group_ownership",error_phrase:"error_transfer_ownership",})}});vBulletin.AJAX({call:"/ajax/render/sgadmin_transferownership",data:({nodeid:K}),success:function(N){A(".transfer-ownership-dialog").removeClass("loading");A(".dialog-content .message",L).html(N).find("[placeholder]").placeholder();L.dialog("option","position",{of:window});transferOwnerAutocomplete=new vBulletin_Autocomplete(A(".transfer_owner_autocomplete"),{apiClass:"user",maxItems:1});A(".transfer_ownership_tabs").tabs()},after_error:function(){L.dialog("close")},})});var I=new vBulletin_Autocomplete(A(".moderator_members_input"),{apiClass:"user",containerClass:"group-moderator-autocomplete"});A(".groupRemoveSubscriber").off("click").on("click",removeSubscriber);A(".groupRemoveMember").off("click").on("click",removeMember);A(".groupAdminSubscriberPaging .right-arrow, .groupAdminSubscriberPaging .left-arrow").off("click").on("click",subscriberChangePage);A(".groupAdminSubscriberPaging .right-arrow ").removeClass("h-disabled");A(".groupAdminSubscriberPaging .pagenav .textbox").off("change").on("change",subscriberChangePage);A("#btnGroupDelete").off("click").on("click",function(){var J=A(this).closest("form");var K=J.find(':input[name="nodeid"]').val();openConfirmDialog({title:vBulletin.phrase.get("delete_a_group_channel"),message:vBulletin.phrase.get("confirm_delete_group_channel"),iconType:"warning",onClickYes:function(){vBulletin.AJAX({call:"/ajax/api/content_channel/delete",data:{nodeid:K},success:function(){window.location.href=pageData.baseurl+"/social-groups";return false},complete:function(){A("body").css("cursor","default")},title_phrase:"delete_group",error_phrase:"error_deleting_group",});return false},onClickNo:function(){}});return false});(function(){try{var J=A("#upload-button-placeholder"),K=A(".groupicon-upload-form");J.height(K.height());K.css({position:"absolute"}).offset(J.offset()).removeClass("h-hide")}catch(L){}});var C=A.cookie(pageData.cookie_prefix+"group_title");if(C!=null){A('.groupAdminForm :input[name="title"]').val(C.replace(/\+/g," "));A.cookie(pageData.cookie_prefix+"group_title",null,{path:pageData.cookie_path,domain:pageData.cookie_domain})}var H=A.cookie(pageData.cookie_prefix+"group_description");if(H!=null){A('.groupAdminForm :input[name="description"]').val(H.replace(/\+/g," "));A.cookie(pageData.cookie_prefix+"group_description",null,{path:pageData.cookie_path,domain:pageData.cookie_domain})}var E=A.cookie(pageData.cookie_prefix+"groupadmin_error");if(E!=null){vBulletin.error("groups",E);A.cookie(pageData.cookie_prefix+"groupadmin_error",null,{path:pageData.cookie_path,domain:pageData.cookie_domain})}A(".editGroupIcon").off("click").on("click",function(J){if(!sgIconEditDlg){sgIconEditDlg=A(J.target).closest(".groupSummaryContainer").find(".sgIconUploader").dialog({width:600,autoOpen:false,modal:true,title:vBulletin.phrase.get("edit_group_icon"),dialogClass:"dialog-container"})}A(sgIconEditDlg).dialog("open");vBulletin.Responsive.Modal.init();return false});A(".sgAddIcon").off("click").on("click",function(J){if(!sgIconUploadDlg){sgIconUploadDlg=A(J.target).closest(".blogAdminRight").find(".sgIconUploader").dialog({width:600,autoOpen:false,modal:true,title:vBulletin.phrase.get("edit_group_icon"),dialogClass:"dialog-container"})}A(sgIconUploadDlg).dialog("open");vBulletin.Responsive.Modal.init();return false});A(".btnSGIconCancel").off("click").on("click",function(J){A(J.target).closest(".sgIconUploader").dialog("close")});A(".btnSGIconUrlSubmit").off("click").on("click",uploadIconFromUrl);A(".sgRadioIconFile").off("click").on("click",function(K){var J=A(K.target).closest(".sgIconUploader ");J.find(".js-file-chooser").removeClass("h-hide");J.find(".sgIconUrlInput ").addClass("h-hide")});A(".sgRadioIconUrl").off("click").on("click",function(K){var J=A(K.target).closest(".sgIconUploader ");J.find(".js-file-chooser").addClass("h-hide");J.find(".sgIconUrlInput ").removeClass("h-hide")});var F=A(".groupIconImg"),D=vBulletin.getAjaxBaseurl()+"/uploader/"+(F.length>0?"uploadSGIcon":"upload-file");A(".js-sg-admin__upload-icon").fileupload({formData:function(J){return[{name:"nodeid",value:J.data("nodeid")},{name:"uploadFrom",value:J.find("input[name=uploadFrom]").val()},{name:"securitytoken",value:pageData.securitytoken}]},dataType:"json",url:D,add:function(L,K){var J=/(gif|jpg|jpeg|jpe|png)$/i;if(J.test(K.files[0].type)){A(".sgIconUploader .js-upload-progress").removeClass("h-hide");K.submit()}else{vBulletin.error("upload","invalid_image_allowed_filetypes_are")}},done:function(L,K){if(K){var J=K.result.errors;if(typeof (J)!="undefined"){if(typeof (J[0])!="undefined"){J=J[0]}vBulletin.error("group_icon",J)}else{if(K.result.imageUrl){if(F.length>0){A(".groupIconImg").attr("src",K.result.thumbUrl);A(sgIconEditDlg).dialog("close")}else{A(".sgIconPreview").html('<img src="'+K.result.thumbUrl+'" alt="">');A(".sGIconfiledataid").val(K.result.filedataid);A(sgIconUploadDlg).dialog("close")}}else{vBulletin.error("group_icon","unable_to_upload_file")}}}else{vBulletin.error("group_icon","invalid_server_response_please_try_again")}},fail:function(M,K){var L="error_uploading_image",J="error";if(K&&K.files.length>0){if(K.files[0].error=="acceptFileTypes"){L="invalid_image_allowed_filetypes_are";J="warning"}}vBulletin.alert("upload",L,J,function(){$editProfilePhotoDlg.find(".fileText").val("");$editProfilePhotoDlg.find(".browse-option").focus()})},always:function(){A(".sgIconUploader .js-upload-progress").addClass("h-hide")}});A(".sgAdminForm").submit(checkSgContentValid)})})(jQuery);removeMember=function(F){var A=$(F.target),E=A.closest(".manage_moderators_row"),C=A.attr("data-userid"),B=A.attr("data-groupid"),D=A.attr("data-nodeid");if(C&&(C>0)&&B&&(B>0)&&D&&(D>0)){vBulletin.AJAX({call:"/ajax/api/blog/removeChannelMember",data:{userId:C,channelId:D},success:function(G){E.remove()},complete:function(){$("body").css("cursor","default")},title_phrase:"manage_subscribers",})}};removeSubscriber=function(E){var A=$(E.target),D=A.closest(".manage_moderators_row"),B=A.attr("data-userid"),C=A.attr("data-nodeid");if(B&&(B>0)&&C&&(C>0)){vBulletin.AJAX({call:"/ajax/api/follow/delete",data:{follow_item:C,type:"follow_channel",userid:B},success:function(F){D.remove()},complete:function(){$("body").css("cursor","default")},title_phrase:"manage_subscribers",})}};var pageno=1;subscriberChangePage=function(D){var A=$(D.target),C;pageCount=parseInt(A.closest(".groupAdminSubscriberPaging").attr("data-pagecount"));if(A.hasClass("left-arrow")||A.parent().hasClass("left-arrow")){C=pageno-1;if(C<1){A.addClass("h-disabled");return }}else{if(A.hasClass("right-arrow")||A.parent().hasClass("right-arrow")){C=pageno+1;if(C>pageCount){A.addClass("h-disabled");return }}else{if(A.hasClass("textbox")){C=parseInt(A.val());if(isNaN(C)||(C>pageCount)||(C<1)){A.val(this.pageno);return }}else{return }}}var B=A.closest(".groupAdminEditPage");vBulletin.AJAX({call:"/ajax/render/sgadmin_subscriberlist",data:{pageno:C,nodeid:$(D.target).closest(".groupAdminEditPage").attr("data-nodeid")},success:function(E){B.find(".subscriberList").html(E);B.find(".right-arrow").toggleClass("h-disabled",(C>=pageCount));B.find(".left-arrow").toggleClass("h-disabled",(C<=1));B.find(".pagenav .textbox").val(C);pageno=C;$(".groupRemoveSubscriber").off("click").on("click",removeSubscriber)},})};uploadIconFromUrl=function(C){var B=$(".groupIconImg"),D=$(C.target).parent(".sgIconUrlInput").find("#imgUrl").val(),A=$(".sgIconUploader .js-upload-progress");A.removeClass("h-hide");vBulletin.AJAX({call:"/uploader/"+(B.length>0?"uploadSGIcon":"url"),error_phrase:"upload_errors",data:{nodeid:$(C.target).closest(".sgicon-upload-form").data("nodeid"),url:D,urlupload:D},success:function(E){if(B.length>0){B.attr("src",E.imageUrl);$(sgIconEditDlg).dialog("close")}else{$(".sgIconPreview").html('<img src="'+E.imageUrl+'" alt="">');$(".sGIconfiledataid").val(E.filedataid);$(sgIconUploadDlg).dialog("close")}},complete:function(){A.addClass("h-hide")}})};checkSgContentValid=function(C){category=$(C.target).find(".sgCategory");if(parseInt(category.val())<=0){vBulletin.error("groups","please_select_category");return false}title=$(C.target).find(".sGtitle");if(title.length&&title.val().trim().length<=0){vBulletin.error("groups","please_enter_title");return false}var B=$(C.target).find(".blogAdminNodeId").val();if(B>0){$.ajax({url:vBulletin.getAjaxBaseurl()+"/ajax/api/user/hasPermissions",data:{nodeid:B,group:"forumpermissions2",permission:"canconfigchannel",},type:"POST",dataType:"json",async:false,success:function(D){if(!D.errors){if(D===false){C.preventDefault();vBulletin.warning("social_groups","cannot_edit_group_info");return false}}else{vBulletin.warning("social_groups",D.errors[0][0]);return false}}})}var A=$(C.target);if(A.find('input[name="securitytoken"]').length){A.find('input[name="securitytoken"]').val(pageData.securitytoken)}else{A.append('<input type="hidden" name="securitytoken" value="'+pageData.securitytoken+'" />')}};;

// ***************************
// js.compressed/group_summary.js
// ***************************
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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,["group_subscribers_list","group_subscribers","unable_to_contact_server_please_try_again"]);(function(A){var B=[".summary-widget"];if(!vBulletin.pageHasSelectors(B)){return false}vBulletin.group=vBulletin.group||{};vBulletin.group.initSeeAllSubscribers=function(){var C=A("#groupSubscribersSeeAll");if(C.length>0){C.click(function(D){vBulletin.group.showSubscribers(A(this).attr("data-node-id"));D.stopPropagation();return false})}};vBulletin.group.showSubscribers=function(E,D,C){if(!vBulletin.group.groupSubscribersAllOverlay){vBulletin.group.groupSubscribersAllOverlay=A("#groupSubscribersAll").dialog({title:vBulletin.phrase.get("group_subscribers_list"),autoOpen:false,modal:true,resizable:false,closeOnEscape:false,showCloseButton:false,width:450,dialogClass:"dialog-container dialog-box group-subscribers-dialog"});vBulletin.pagination({context:"#groupSubscribersAll",onPageChanged:function(F,G){vBulletin.group.showSubscribers(E,F)}});A(document).off("click",".group-subscribers-close").on("click",".group-subscribers-close",function(){vBulletin.group.groupSubscribersAllOverlay.dialog("close")});A(document).off("click","#groupSubscribersAll .action_button").on("click","#groupSubscribersAll .action_button",function(){if(!A(this).hasClass("subscribepending_button")){var F=A(this);var H=parseInt(F.attr("data-userid"));var G="";if(F.hasClass("subscribe_button")){G="add"}else{if(F.hasClass("unsubscribe_button")){G="delete"}}if((typeof (H)=="number")&&G){A.ajax({url:vBulletin.getAjaxBaseurl()+"/profile/follow-button?do="+G+"&follower="+H+"&type=follow_members",type:"POST",dataType:"json",success:function(J){if(J==1||J==2){if(G=="add"){var I=(J==1)?"subscribed":"subscribepending";var K=(J==1)?"following":"following_pending";F.removeClass("subscribe_button b-button b-button--special").addClass(I+"_button b-button b-button--secondary").text(vBulletin.phrase.get(K))}else{if(G=="delete"){F.removeClass("subscribed_button unsubscribe_button b-button b-button--special").addClass("subscribe_button b-button b-button--secondary").text(vBulletin.phrase.get("follow"))}}}else{if(J.errors){openAlertDialog({title:vBulletin.phrase.get("profile_guser"),message:vBulletin.phrase.get("error_x",J.errors[0][0]),iconType:"error"})}}},error:function(){openAlertDialog({title:vBulletin.phrase.get("profile_guser"),message:vBulletin.phrase.get("unable_to_contact_server_please_try_again"),iconType:"error"})}})}}})}if(!D){D=1}if(!C){C=10}A.ajax({url:vBulletin.getAjaxBaseurl()+"/ajax/render/subscribers_list",type:"POST",data:{nodeid:E,page:D,perpage:C},dataType:"json",success:function(F){if(F&&F.errors){openAlertDialog({title:vBulletin.phrase.get("group_subscribers"),message:vBulletin.phrase.get(F.errors[0]),iconType:"error"})}else{A(".group-subscribers-content",vBulletin.group.groupSubscribersAllOverlay).html(F)}},error:function(){openAlertDialog({title:vBulletin.phrase.get("group_subscribers"),message:vBulletin.phrase.get("unable_to_contact_server_please_try_again"),iconType:"error"})}});vBulletin.group.groupSubscribersAllOverlay.dialog("open")};A(document).ready(function(){vBulletin.group.initSeeAllSubscribers()})})(jQuery);;

