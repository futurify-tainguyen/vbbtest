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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,["add_caption","attach_files","click_to_add_caption","error_uploading_image","invalid_image_allowed_filetypes_are","remove_all_photos_confirmation_message","remove_all_photos_q","upload","upload_more","uploading","you_are_already_editing_continue","you_must_be_logged_in_to_upload_photos","enter_url_file"]);(function(D){window.vBulletin=window.vBulletin||{};vBulletin.upload=vBulletin.upload||{};vBulletin.gallery=vBulletin.gallery||{};vBulletin.permissions=vBulletin.permissions||{};var C=3,E=2,G=[];vBulletin.gallery.onBeforeSerializeEditForm=function(H){H.find(".caption-box").each(function(){var I=D(this);if(I.hasClass("placeholder")&&I.val()==I.attr("placeholder")){I.val("")}});return true};vBulletin.upload.changeButtonText=function(H,I){if(!H.data("default-text")){H.data("default-text",H.text())}H.text(I)};vBulletin.upload.restoreButtonText=function(H){if(H.data("default-text")){H.text(H.data("default-text"))}};vBulletin.upload.initializePhotoUpload=function(J){console.log("Fileupload: vBulletin.upload.initializePhotoUpload");if(!J||J.length==0){J=D("body");if(D(".js-photo-display",J).length>1){console.log("Fileupload: multiple upload forms, abort");return false}}D(".js-continue-button",J).off("click").on("click",function(O){console.log("Fileupload: continue");var M=D(document).data("gallery-container"),L=M.find(".js-photo-postdata"),N={},K=D(this).closest(".js-photo-display"),P=[];L.empty();K.find(".photo-item-wrapper:not(.h-hide)").each(function(){var S=D(this).find(".filedataid"),R=S.val(),U=S.data("nodeid"),T=D.trim(D(this).find(".caption-box").val());D(this).removeClass("tmp-photo");if(typeof U!=="undefined"){D(this).removeClass("tmp-photo");var Q=D.inArray(U,G);if(Q==-1){G.push(U)}}L.append(D("<input />").attr({type:"hidden",name:"filedataid[]"}).val(R)).append(D("<input />").attr({type:"hidden",name:"title_"+R}).val(T));P.push({filedataid:R,title:T})});N.photocount=P.length;N.photos=P;K.find(".photo-item-wrapper:hidden").remove();K.dialog("close");if(N.photos.length>0){vBulletin.AJAX({call:"/ajax/render/editor_gallery_photoblock",data:N,type:"POST",dataType:"json",success:function(Q){var R=D(Q);R.find(".b-gallery-thumbnail-list__aside").addClass("h-invisible");M.find(".js-gallery-content").removeClass("h-hide").find(".js-panel-content").empty().append(R);setTimeout(function(){R.find(".b-gallery-thumbnail-list__aside").removeClass("h-invisible")},500);vBulletin.upload.initializePhotoUpload(M);D(document).data("gallery-container",null)},error:vBulletin.ajaxtools.logAjaxError,api_error:vBulletin.ajaxtools.logApiError});D(".js-edit-photos",M).removeClass("h-hide");vBulletin.upload.changeButtonText(D(".b-button--upload .js-upload-label",M),vBulletin.phrase.get("upload_more"))}else{D(".js-panel-content",M).empty();D(".js-edit-photos",M).addClass("h-hide");vBulletin.upload.restoreButtonText(D(".b-button--upload .js-upload-label",M))}});D(".js-edit-photos",J).off("click").on("click",function(L){console.log("Fileupload: edit photos");D(document).data("gallery-container",D(this).closest(".b-content-entry-panel__content--gallery"));var K=vBulletin.upload.getUploadedPhotosDlg(false);vBulletin.upload.relocateLastInRowClass(K.find(".photo-item-wrapper"));I(K);K.dialog("open");vBulletin.upload.adjustPhotoDialogForScrollbar(K);D(".b-button--upload",K).trigger("focus")});D(".js-cancel-button",J).off("click").on("click",function(M){console.log("Fileupload: cancel");var K=D(this).closest(".js-photo-display"),L=D(document).data("gallery-container");D(".photo-item-wrapper.tmp-photo",K).remove();D(".photo-item-wrapper",K).removeClass("h-hide");if(D(".js-panel-content",L).length>0){vBulletin.upload.changeButtonText(D(".b-button--upload .js-upload-label",L),vBulletin.phrase.get("upload_more"))}else{vBulletin.upload.restoreButtonText(D(".b-button--upload .js-upload-label",L))}K.dialog("close")});var I=function(K){vBulletin.upload.updateButtons(K,(D(".photo-display .photo-item-wrapper:not(.h-hide)",K).length>0))};D(".b-content-entry-panel__content--gallery, .js-profile-media-photoupload-dialog",J).fileupload({dropZone:null,dataType:"json",url:vBulletin.getAjaxBaseurl()+"/uploader/upload-photo",type:"POST",formData:function(L){console.log("Fileupload: gallery formData");var K=L.find(".b-content-entry-panel__content--gallery");if(K.length==0){K=D("<form>")}if(K.find('input[name="securitytoken"]').length){K.find('input[name="securitytoken"]').val(pageData.securitytoken)}else{K.append('<input type="hidden" name="securitytoken" value="'+pageData.securitytoken+'" />')}return K.find(":input").filter(function(){return !D(this).parent().hasClass("js-photo-postdata")}).serializeArray()},acceptFileTypes:/(gif|jpg|jpeg|jpe|png)$/i,add:function(N,M){console.log("Fileupload: gallery add");var L=D(this);D(document).data("gallery-container",L);var K=vBulletin.upload.getUploadedPhotosDlg(true);D(".js-upload-progress",K).removeClass("h-hide");vBulletin.upload.changeButtonText(D(".b-button--upload .js-upload-label",K),vBulletin.phrase.get("uploading"));M.submit();D(".b-button--upload",K).trigger("focus")},done:function(Q,P){console.log("Fileupload: gallery done");var N=D(this),S=(P&&P.result&&P.result.errors&&(P.result.errors.length>0)),R;D(document).data("gallery-container",N);var M=vBulletin.upload.getUploadedPhotosDlg(false);if(P&&P.result&&!S&&P.result.edit){var K=D(P.result.edit),O=D(".photo-display",M),L=M.parent(),T=D(".photo-item-wrapper:not(.h-hide)",O).length;K.addClass("tmp-photo");if((T+1)%C==0){K.addClass("last-in-row")}if((T+1)>vBulletin.contentEntryBox.ATTACHLIMIT){D(".js-attach-limit-warning",M).show()}vBulletin.upload.adjustPhotoDialogForScrollbar(M);O.append(K);K.fadeIn("fast",function(){A(O);M.dialog("option","position",{of:window});if(L.hasClass("has-scrollbar")){O.animate({scrollTop:O[0].scrollHeight-O.height()},"fast")}});D(".js-continue-button, .btnPhotoUploadSave",M).show();return }else{if(S){R=P.result.errors[0][0]||P.result.errors[0][1];switch(R){case"please_login_first":R="you_must_be_logged_in_to_upload_photos";break;default:R=P.result.errors[0];break}}else{R="unknown_error"}}I(M);vBulletin.warning("upload",R)},fail:function(O,N){console.log("Fileupload: gallery fail");var L="error_uploading_image",K="error";if(N&&N.files.length>0){switch(N.files[0].error){case"acceptFileTypes":L="invalid_image_allowed_filetypes_are";K="warning";break}}var M=vBulletin.upload.getUploadedPhotosDlg(false);I(M);vBulletin.alert("upload",L)},always:function(M,L){console.log("Fileupload: gallery always");var K=vBulletin.upload.getUploadedPhotosDlg(false);K.find(".js-upload-progress").addClass("h-hide");I(K)}});D(".b-content-entry-panel__content--attachment",J).off("click",".js-upload-from-url").on("click",".js-upload-from-url",function(L){var K=D(this);$promtDlg=openPromptDialog({title:vBulletin.phrase.get("enter_url_file"),message:"",buttonLabel:{okLabel:vBulletin.phrase.get("ok"),cancelLabel:vBulletin.phrase.get("cancel")},onClickOK:function(N){var M=K.parent().find(".js-upload-progress");M.removeClass("h-hide");vBulletin.AJAX({call:"/uploader/url",data:{urlupload:N,attachment:1,uploadFrom:D(".js-uploadFrom",J).val()},skipdefaultsuccess:true,complete:function(){M.addClass("h-hide")},success:function(O){if(O.imageUrl){$panel=D(".b-content-entry-panel__content--attachment");O.name=O.filename;H.call($panel,O)}},title_phrase:"error_uploading_image"})}});return false});D(".b-content-entry-panel__content--attachment",J).fileupload({dropZone:null,dataType:"json",url:vBulletin.getAjaxBaseurl()+"/uploader/upload",type:"POST",previewAsCanvas:false,autoUpload:true,formData:function(L){console.log("Fileupload: attachments formData");var K=L.find(".b-content-entry-panel__content--attachment");if(K.length==0){K=D("<form>")}if(K.find('input[name="securitytoken"]').length){K.find('input[name="securitytoken"]').val(pageData.securitytoken)}else{K.append('<input type="hidden" name="securitytoken" value="'+pageData.securitytoken+'" />')}return K.find(":input").filter(function(){return !D(this).parent().hasClass("js-attach-postdata")}).serializeArray()},add:function(L,K){console.log("Fileupload: attachments add");J.find(".js-upload-progress").removeClass("h-hide");K.submit()},done:function(O,N){console.log("Fileupload: attachments done");var L=vBulletin.phrase.get("error_uploading_image");var K="error";var P=[];var M=this;if(N&&N.result){if(N.result.error){P.push(vBulletin.phrase.get(N.result.error))}else{D.each(N.result,function(Q,R){if(!R.error){H.call(M,R);return }else{if(R.error[0]=="please_login_first"){P.push(vBulletin.phrase.get("you_must_be_logged_in_to_upload_photos"));return false}else{P.push(R.name);D.each(R.error,function(T,S){P.push(vBulletin.phrase.get(S))})}}})}}else{P.push(vBulletin.phrase.get("unknown_error"))}if(P.length>0){openAlertDialog({title:vBulletin.phrase.get("upload"),message:P.join("<br />\n"),iconType:"warning"})}},fail:function(N,M){console.log("Fileupload: attachments fail");var L=vBulletin.phrase.get("error_uploading_image");var K="error";if(M&&M.files.length>0){switch(M.files[0].error){case"acceptFileTypes":L=vBulletin.phrase.get("invalid_image_allowed_filetypes_are");K="warning";break}}openAlertDialog({title:vBulletin.phrase.get("upload"),message:L,iconType:K})},always:function(L,K){J.find(".js-upload-progress").addClass("h-hide")}});var H=function(L){console.log("Fileupload: attachDone");var S=(this instanceof D)?this:D(this),O=S.find(".js-attach-list"),N=D(".js-uploadFrom").val();if(!L.name){openAlertDialog({title:vBulletin.phrase.get("upload"),message:vBulletin.phrase.get("unknown_error"),iconType:"warning"})}var M,K,P=false;if(L.name.match(/\.(gif|jpg|jpeg|jpe|png|bmp)$/i)){K=D("<img />").attr("src",pageData.baseurl+"/filedata/fetch?type=thumb&filedataid="+L.filedataid).addClass("b-attach-item__image");P=true}else{K=D("<span />").addClass("b-icon b-icon__doc--gray h-margin-bottom-m")}var Q=O.find(".js-attach-item-sample").first().clone(true);if(N=="signature"){O.find(".js-attach-item").not(".js-attach-item-sample").remove();P=false;D('[data-action="insert"]',Q).data("action","insert_sigpic").attr("data-action","insert_sigpic")}Q.removeClass("js-attach-item-sample");Q.find(".js-attach-item-image").append(K);Q.find(".js-attach-item-filename").text(L.name);Q.append(D("<input />").attr({type:"hidden",name:"filedataids[]"}).val(L.filedataid)).append(D("<input />").attr({type:"hidden",name:"filenames[]"}).val(L.name));var R="";if(M=L.name.match(/\.([a-z]+)$/i)){R=M[1]}Q.data("fileext",R);Q.data("filename",L.name);Q.data("filedataid",L.filedataid);Q.data("attachnodeid",0);if(!P){Q.find(".js-attach-ctrl").filter(function(){return D(this).data("action")=="insert_image"||D(this).data("action")=="insert_label"}).addClass("h-hide");Q.find(".js-attach-ctrl").filter(function(){return D(this).data("action")=="insert"}).html(vBulletin.phrase.get("insert"))}if(!vBulletin.ckeditor.checkEnvironment()){Q.find(".js-attach-ctrl").filter(function(){return D(this).data("action")=="insert"||D(this).data("action")=="insert_image"||D(this).data("action")=="insert_label"}).addClass("h-hide")}Q.appendTo(O).removeClass("h-hide");O.removeClass("h-hide")};D(".gallery-submit-form",J).submit(function(){D(".js-photo-display .photo-display input",D(this).closest(".gallery-editor")).appendTo(D(this));var K=D("input[type=hidden][name=ret]",this);if(D.trim(K.val())==""){K.val(location.href)}});D(document).off("click.photoadd",".js-photo-selector-continue").on("click.photoadd",".js-photo-selector-continue",function(){console.log("Fileupload: continue 2");var K=D(this).closest(".js-photo-selector-container"),L={};K.find(".photo-item-wrapper").each(function(){var M=D(this).find(".filedataid"),P=M.data("nodeid");if(M.is(":checked")){var N=M.val(),O=D.trim(D(this).find(".photo-title").text());L[P]={imgUrl:vBulletin.getAjaxBaseurl()+"/filedata/fetch?filedataid="+N+"&thumb=1",filedataid:N,title:O}}});if(!D.isEmptyObject(L)){vBulletin.AJAX({call:"/ajax/render/photo_item",data:{items:L,wrapperClass:"tmp-photo"},success:function(N){var O=vBulletin.upload.getUploadedPhotosDlg(false),P=D(".photo-display",O),M;P.append(N);M=D(".photo-item-wrapper:not(.h-hide)",P);vBulletin.upload.relocateLastInRowClass(M);vBulletin.upload.updateButtons(O,(M.length>0));vBulletin.upload.adjustPhotoDialogForScrollbar(O);O.dialog("option","position",{of:window});O.dialog("open")}})}D(".photo-selector-galleries",K).tabs("destroy");K.dialog("close")});D(".js-photo-selector-cancel",J).off("click").on("click",function(){console.log("Fileupload: cancel 2");D(document).data("gallery-container",null);var K=D(this).closest(".js-photo-selector-container");D(".photo-selector-galleries",K).tabs("destroy");K.dialog("close")});console.log("Fileupload: vBulletin.upload.initializePhotoUpload finished")};vBulletin.upload.getUploadedPhotosDlg=function(H,J){var I;if(!J||J.length==0){J=D(document).data("gallery-container");if(!J){return D()}}if(J.hasClass("profile-media-photoupload-dialog")){I=J;if(H){I.dialog("open");vBulletin.upload.adjustPhotoDialogForScrollbar(I)}return I}I=J.find(".js-photo-display");if(I.length==0){D(".js-photo-display").each(function(){if(D(this).data("associated-editor")==J.get(0)){I=D(this);return false}})}else{I.dialog({modal:true,width:606,autoOpen:false,showCloseButton:false,closeOnEscape:false,resizable:false,showTitleBar:false,dialogClass:"dialog-container upload-photo-dialog-container dialog-box"});I.data("orig-width",I.dialog("option","width"));I.data("associated-editor",J.get(0));I.find(".b-form-input__file--hidden").prop("disabled",false)}if(H){I.dialog("open");vBulletin.upload.adjustPhotoDialogForScrollbar(I)}return I};vBulletin.upload.adjustPhotoDialogForScrollbar=function(K){var L=D(".photo-display",K);var J=K.parent();var I=D(".photo-item-wrapper:not(.h-hide)",L).length;if(!J.hasClass("has-scrollbar")&&I>=(C*E)){var H=window.vBulletin.getScrollbarWidth();J.addClass("has-scrollbar");K.dialog("option","width",K.dialog("option","width")+H+11)}};vBulletin.upload.relocateLastInRowClass=function(H){H.removeClass("last-in-row").filter(":not(.h-hide)").filter(function(I){return((I%C)==(C-1))}).addClass("last-in-row")};vBulletin.upload.updateButtons=function(K,I){var H=D(".b-button--upload .js-upload-label",K),J=D(".js-continue-button, .btnPhotoUploadSave",K);if(I){vBulletin.upload.changeButtonText(H,vBulletin.phrase.get("upload_more"))}else{vBulletin.upload.restoreButtonText(H)}J.toggle(I)};function F(){D(document).off("click.removephoto",".photo-display .photo-item .remove-icon").on("click.removephoto",".photo-display .photo-item .remove-icon",function(){var I=D(this).closest(".photo-item-wrapper"),M=D(".filedataid",I).data("nodeid"),L=I.parents(".js-photo-display").last();if(typeof M!=="undefined"){var K=D.inArray(M,G);if(K!=-1){G.splice(K,1)}}I.addClass("h-hide");var H=L.find(".photo-item-wrapper:not(.h-hide)"),J=H.length;if(J<=vBulletin.contentEntryBox.ATTACHLIMIT){D(".js-attach-limit-warning",L).hide()}vBulletin.upload.relocateLastInRowClass(H);if(H.length<=(C*E)){L.parent().removeClass("has-scrollbar");L.dialog("option","width",L.data("orig-width"))}vBulletin.upload.updateButtons(L,(H.length>0))});D(document).off("blur.photocaption",".photo-display .photo-caption .caption-box").on("blur.photocaption",".photo-display .photo-caption .caption-box",function(){D(this).scrollTop(0)});vBulletin.conversation.bindEditFormEventHandlers("gallery")}function A(I){var H=D(".photo-item .photo-caption .caption-box",I);H.filter("[placeholder]").placeholder()}function B(H){D(this).replaceWith(H);A(H)}D(document).ready(function(){vBulletin.upload.initializePhotoUpload();F()})})(jQuery);