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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,["saving"]);window.vBulletin.options=window.vBulletin.options||{};window.vBulletin.options.precache=window.vBulletin.options.precache||[];window.vBulletin.options.precache=$.merge(window.vBulletin.options.precache,["postminchars","commentminchars"]);window.vBulletin.media=window.vBulletin.media||{};(function(H){var M=["#media-tab",".album-widget"];if(!vBulletin.pageHasSelectors(M)){return false}var R={mediaTypeFilter:false,currentMediaPage:Number(H("#mediaCurrentPage").val())||1,currentGalleryPage:1,currentNodeId:0,currentUserId:0,profileMediaDetailContainer:false,currentDateFilter:"time_lastmonth"};var O=H("#media-tab"),B,K,E;window.vBulletin.media.calculatePhotosPerPage=function(W){W=W||50;var V=H("#profileMediaDetailContainer").is(":visible");if(!V){H("#profileMediaDetailContainer").removeClass("h-hide")}var X=Math.floor(H("#profileMediaDetailContainer").width()/128);if(!V){H("#profileMediaDetailContainer").addClass("h-hide")}return(W%X==0||W<=X)?W:W-(W%X)};function N(V){H(".profMediaFilterRow").removeClass("filterSelected");H(V.target).addClass("filterSelected");if(H(V.target).hasClass("profMediaFilterAllTypes")){R.mediaTypeFilter=false}else{if(H(V.target).hasClass("profMediaFilterGallery")){R.mediaTypeFilter="gallery"}else{if(H(V.target).hasClass("profMediaFilterVideo")){R.mediaTypeFilter="video"}}}C(1)}function C(V){H("body").css("cursor","wait");var W=H(".js-profile-media-container",O);H.ajax({url:vBulletin.getAjaxBaseurl()+"/ajax/render/profile_media_content",data:({userid:W.data("user-id"),pageno:V,perpage:W.data("perpage")}),type:"POST",dataType:"json",success:function(X){if(X.errors){openAlertDialog({title:vBulletin.phrase.get("profile_media"),message:vBulletin.phrase.get(X.errors[0][0]),iconType:"error"});console.log("/ajax/render/profile_media_content failed, error: "+JSON.stringify(X))}else{console.log("/ajax/render/profile_media_content successful");H("#mediacontent").html(X);H("#profileMediaDetailContainer").empty();H("#mediacontent").removeClass("h-hide");H("#profileMediaContainer").removeClass("h-hide");H(".media-tab .profile-toolbar .toolset-left > li").addClass("h-hide").has(".profile-media-upload, .profile-media-createvideo").removeClass("h-hide");H("#mediaPreviousPage").closest("pagenav-controls").removeClass("h-hide");H("#profileMediaDetailContainer").addClass("h-hide");H("#profileMediaContainer").removeClass("h-hide");R.currentMediaPage=V;var Y={totalpages:Number(H("#mediaList").data("totalpages")),totalcount:Number(H("#mediaList").data("totalcount")),currentpage:V};G(Y);if(!vBulletin.isScrolledIntoView(H("#profileTabs .profile-tabs-nav"))){H("html,body").animate({scrollTop:H("#profileTabs .profile-tabs-nav").offset().top},"slow")}}},error:function(Z,Y,X){console.log("/ajax/render/profile_media_content failed, error: "+X);console.log("response:"+Z.responseText);console.log("status:"+Y);console.log("code:"+Z.status);openAlertDialog({title:vBulletin.phrase.get("profile_media"),message:vBulletin.phrase.get("unable_to_contact_server_please_try_again"),iconType:"error"})},complete:function(){H("body").css("cursor","auto")}})}function U(X){var W=this.id;if(H("#profileMediaDetailContainer").is(":visible")){L(X);return false}if(W=="mediaCurrentPage"){var Y=parseInt(H("#mediaCurrentPage").val());if(Y>0&&Y<=parseInt(H("#mediaPageCount").html())){C(Y);S(Y)}else{H("#mediaCurrentPage").val(R.currentMediaPage)}}else{if(W=="mediaPreviousPage"){if(R.currentMediaPage>1){var V=R.currentMediaPage-1;C(V);S(V)}else{H("#mediaPreviousPage").addClass("h-disabled")}}else{if(W=="mediaNextPage"){if(R.currentMediaPage<parseInt(H("#mediaPageCount").html())){var V=R.currentMediaPage+1;C(V);S(V)}else{H("#mediaNextPage").addClass("h-disabled")}}}}return false}function S(V){var X=vBulletin.makePaginatedUrl(location.href,V);if(K.isEnabled()){var W={from:"media_filter",page:V||1,tab:O.data("url-path")?O.data("url-path"):"#"+O.attr("id")};K.pushState(W,document.title,X)}else{if(B){location.href=X}}}vBulletin.media.setHistoryStateChange=function(V){if(V){B=O.find(".conversation-toolbar-wrapper").data("allow-history")=="1";K=new vBulletin.history.instance(B)}if(K.isEnabled()){K.setStateChange(function(a){var Z=K.getState();if(Z.data.from=="media_filter"){K.log(Z.data,Z.title,Z.url);var W=O.closest(".ui-tabs"),X=W.find(".ui-tabs-nav > li").filter('li:has(a[href*="#{0}"])'.format(O.attr("id")));if(X.hasClass("ui-tabs-active")){C(Z.data.page)}else{var Y=X.index();vBulletin.selectTabByIndex.call(W,Y)}}},"media_filter")}};function L(X){if(!R.currentNodeId&&!R.currentUserId){return false}var V=X.target.id;if(V=="mediaCurrentPage"){targetPage=parseInt(H("#mediaCurrentPage").val());if(targetPage>0&&targetPage<=parseInt(H("#mediaPageCount").html())){var W={nodeid:R.currentNodeId,userid:R.currentUserId,pageno:targetPage,perpage:vBulletin.media.calculatePhotosPerPage(vBulletin.media.TARGET_PHOTOS_PERPAGE),datefilter:R.currentDateFilter};F(W)}else{H("#mediaCurrentPage").val(R.currentGalleryPage)}}else{if(V=="mediaPreviousPage"){if(R.currentGalleryPage>1){var W={nodeid:R.currentNodeId,userid:R.currentUserId,pageno:R.currentGalleryPage-1,perpage:vBulletin.media.calculatePhotosPerPage(vBulletin.media.TARGET_PHOTOS_PERPAGE),datefilter:R.currentDateFilter};F(W)}else{H("#mediaPreviousPage ").addClass("h-disabled")}}else{if(V=="mediaNextPage"){if(R.currentGalleryPage<parseInt(H("#mediaPageCount").html())){var W={nodeid:R.currentNodeId,userid:R.currentUserId,pageno:R.currentGalleryPage+1,perpage:vBulletin.media.calculatePhotosPerPage(vBulletin.media.TARGET_PHOTOS_PERPAGE),datefilter:R.currentDateFilter};F(W)}else{H("#mediaNextPage").addClass("h-disabled")}}}}return false}function G(V){var W=H(".media-tab .pagenav-controls-container").toggleClass("h-hide",(V.totalpages<=1));if(W.is(":visible")){H("#mediaPreviousPage").toggleClass("h-disabled",(V.currentpage<=1));H("#mediaNextPage").toggleClass("h-disabled",(V.currentpage>=V.totalpages));H("#mediaCurrentPage").val(V.currentpage);H("#mediaPageCount").text(V.totalpages)}}function F(W,Z,a,c){var X=H("#profileMediaDetailContainer"),Y={nodeid:R.currentNodeId,userid:R.currentUserId,channelid:0,pageno:1,dateFilter:R.currentDateFilter,albumid:0};W=H.extend({},Y,W);if(!isNaN(W.nodeid)){H("body").css("cursor","wait");var d;if(W.nodeid==-1){d="profile_media_videolist"}else{d="profile_textphotodetail"}if(X.closest(".media-tab").length==0){H(".media-tab").append(R.profileMediaDetailContainer)}H("#profileMediaContainer").closest(".tab").find("li.list-item-gallery").remove();var V=vBulletin.getAjaxBaseurl()+"/ajax/render/"+d;var b={nodeid:W.nodeid,userid:W.userid,channelid:W.channelid,pageno:W.pageno,albumid:W.albumid,viewMore:a};if(W.dateFilter){b.dateFilter=W.dateFilter}if(W.perpage){b.perpage=W.perpage}H.ajax({url:V,type:"POST",data:b,dataType:"json",success:function(e){if(e.errors){openAlertDialog({title:vBulletin.phrase.get("profile_media"),message:vBulletin.phrase.get(e.errors[0][0]),iconType:"error"});if(c&&typeof c.error=="function"){c.error()}}else{R.currentNodeId=W.nodeid;R.currentUserId=W.userid;R.currentGalleryPage=W.pageno;if(a){H(".js-album-detail .js-photo-preview").append(e);H(".more-gallery",X).toggleClass("h-hide-imp",(R.currentGalleryPage>=H(".js-album-detail").data("totalpages")))}else{X.html(e);X.addClass("list-item").attr("data-nodeid",W.nodeid);H("#profileMediaContainer").addClass("h-hide");H(".media-tab .profile-toolbar .toolset-left > li").removeClass("h-hide").has(".profile-media-upload, .profile-media-createvideo").addClass("h-hide");X.removeClass("h-hide");H("#mediaPreviousPage").closest(".pagenav-controls-container").addClass("h-hide");H(".profile-media-uploadphotos",X).click(I);if(W.perpage){H(".media-tab").data("perpage",W.perpage).data("callbacks",c)}}vBulletin.initFlexGridFixLastRowAll()}if(W.nodeid==-1){var f={totalpages:Number(H(".media-video-list",X).data("totalpages")),totalcount:Number(H(".media-video-list",X).data("totalcount")),currentpage:R.currentGalleryPage};G(f)}if(!Z){}H(".profile-toolbar .media-toolbar-filter").removeClass("h-hide");if(c&&typeof c.success=="function"){c.success()}},error:function(g,f,e){console.log("/ajax/render/{0} failed, error: {1}".format(d,e));openAlertDialog({title:vBulletin.phrase.get("profile_media"),message:vBulletin.phrase.get("unable_to_contact_server_please_try_again"),iconType:"error"});if(c&&typeof c.error=="function"){c.error()}},complete:function(){H("body").css("cursor","auto");if(c&&typeof c.complete=="function"){c.complete()}}})}}function I(X){H(document).data("gallery-container",H(this).closest(".js-album-detail"));var V=vBulletin.upload.getUploadedPhotosDlg(false);var W=V.data("nodeid");if(!W||isNaN(parseInt(W))){return }H.ajax({url:vBulletin.getAjaxBaseurl()+"/ajax/render/media_addphotos",data:{nodeid:W},type:"POST",dataType:"json",success:function(Y){if(Y.errors){openAlertDialog({title:vBulletin.phrase.get("profile_media"),message:vBulletin.phrase.get(Y.errors[0][0]),iconType:"warning",onAfterClose:function(){V.dialog("close")}})}else{V.html(Y);vBulletin.upload.initializePhotoUpload(V.parent());V.find(".js-save-button").off("click").on("click",A);V.find(".js-cancel-button").off("click").on("click",function(Z){V.dialog("close")});vBulletin.upload.relocateLastInRowClass(V.find(".photo-item-wrapper"));if(H(".photo-display .photo-item-wrapper:not(.h-hide)",V).length>0){vBulletin.upload.changeButtonText(H(".b-button--upload .js-upload-label",V),vBulletin.phrase.get("upload_more"));H(".js-save-button",V).show()}else{H(".js-save-button",V).hide()}V.dialog("open");vBulletin.upload.adjustPhotoDialogForScrollbar(V)}},error:function(a,Z,Y){console.log("/ajax/render/media_addphotos failed, error: "+Y);openAlertDialog({title:vBulletin.phrase.get("profile_media"),message:vBulletin.phrase.get("unable_to_contact_server_please_try_again"),iconType:"error",onAfterClose:function(){V.dialog("close")}})}})}function J(W){var V=T(H(this));V.pageno=1;V.perpage=vBulletin.media.calculatePhotosPerPage(vBulletin.media.TARGET_PHOTOS_PERPAGE);R.currentDateFilter=H(this).closest(".js-profile-media-container").find(".toolbar-filter-overlay input[name=filter_time]:checked").val();R.currentFilter=V;F(V)}function D(Y){var X=H(".media-tab").data("perpage");var W=H(".media-tab").data("callbacks");var V=T(H(this));V.pageno=R.currentGalleryPage+1;if(X){V.perpage=X}F(V,false,1,W)}function A(Y){var X=H(Y.target).closest(".profile-media-photoupload-dialog"),V=H(Y.target).closest("form");if(V.length>0){H(Y.target).closest(".photo-display-container").find(".photo-item-wrapper:not(.h-hide)").each(function(Z,b){var a=parseInt(H(b).find(".filedataid").val());if(!isNaN(a)){V.append('<input type="hidden" name="filedataid[]" value="'+a+'"/>');H('<input type="hidden" name="title_'+a+'" />').val(H(b).find("textarea").val()).appendTo(V)}});var W=H("button",V).prop("disabled",true);H.ajax({url:V.attr("action"),data:V.serializeArray(),type:"POST",success:function(Z){if(Z.errors){if(typeof (Z.errors[0])=="undefined"){openAlertDialog({title:vBulletin.phrase.get("media"),message:vBulletin.phrase.get(Z.errors),iconType:"error"})}else{openAlertDialog({title:vBulletin.phrase.get("media"),message:vBulletin.phrase.get(Z.errors[0]),iconType:"error"})}}else{X.dialog("close");C(R.currentMediaPage)}},error:function(b,a,Z){console.log(V.attr("action")+" failed, error: "+Z);openAlertDialog({title:vBulletin.phrase.get("profile_media"),message:vBulletin.phrase.get("invalid_server_response_please_try_again"),iconType:"error"})},complete:function(){W.prop("disabled",false)}})}else{X.dialog("close")}}function T(V){var X;if(H("#profileMediaContainer").is(":visible")){X=H("#profileMediaContainer")}else{if(H("#profileMediaDetailContainer").is(":visible")){X=H("#profileMediaDetailContainer")}}var W={nodeid:parseInt(V.data("nodeid"),10)||parseInt(X.attr("data-nodeid"),10),userid:parseInt(X.data("userid"),10),channelid:parseInt(X.data("channelid"),10),dateFilter:R.currentDateFilter,albumid:parseInt(V.data("albumid"),10)};if(isNaN(W.channelid)){W.channelid=0}if(isNaN(W.nodeid)){W.nodeid=0}if(W.nodeid>0){W.userid=0}else{if(isNaN(W.userid)){W.userid=0}}if(isNaN(W.albumid)){W.albumid=0}return W}function P(){H(document).off("click","#mediaList .albumLink").on("click","#mediaList .albumLink",J);var V=false;O.off("click",".profile-media-createvideo, .profile-media-upload").on("click",".profile-media-createvideo, .profile-media-upload",function(Y){var X=H(this).hasClass("profile-media-upload")?"gallery":"video";if(!V){V=H(Y.target).closest(".ui-widget-content").find(".profileMediaEditContainer").dialog({modal:true,autoOpen:false,width:800,title:vBulletin.phrase.get("profile_media"),resizable:false,closeOnEscape:false,showCloseButton:false,dialogClass:"dialog-container dialog-box edit-media-upload-dialog js-profile-media",close:function(){H(document).data("gallery-container",null)}});vBulletin.ckeditor.initEditor(V.find(".js-ckeditor-init-on-focus"));vBulletin.upload.initializePhotoUpload(V)}V.off("dialogopen").on("dialogopen",function(Z,a){V.find('.b-toolbar__item[data-panel="b-content-entry-panel__content--{0}"]:not(.b-toolbar__item--active)'.format(X)).trigger("click");vBulletin.ckeditor.initEditorComponents(V);H(document).data("gallery-container",H(this).find(".b-content-entry-panel__content--"+X))}).dialog("open")});O.off("click","#profileMediaDetailContainer .more-gallery").on("click","#profileMediaDetailContainer .more-gallery",D);O.off("click",".profile-media-backbtn").on("click",".profile-media-backbtn",function(Y){H("#profileMediaDetailContainer").addClass("h-hide");H("#mediaPreviousPage").closest(".pagenav-controls-container").removeClass("h-hide");H("#profileMediaContainer").removeClass("h-hide");H(".media-tab .profile-toolbar .toolset-left > li").addClass("h-hide").has(".profile-media-upload, .profile-media-createvideo").removeClass("h-hide");R.currentDateFilter=H(this).closest(".conversation-toolbar-wrapper").find(".toolbar-filter-overlay input[name=filter_time]:checked").val();H(".profile-toolbar .media-toolbar-filter").addClass("h-hide");H(this).closest(".conversation-toolbar-wrapper").find(".filtered-by").addClass("h-hide").find(".filter-text-wrapper").empty();var X={totalpages:Number(H("#mediaList").data("totalpages")),totalcount:Number(H("#mediaList").data("totalcount")),currentpage:R.currentMediaPage};G(X)});O.off("change","#mediaCurrentPage").on("change","#mediaCurrentPage",U);O.off("click","#mediaPreviousPage, #mediaNextPage").on("click","#mediaPreviousPage, #mediaNextPage",U);R.profileMediaDetailContainer=H("#profileMediaDetailContainer").clone();var W=".js-album-detail .js-post__content-wrapper .js-content-entry form";H(document).off("afterSave",W).on("afterSave",W,function(Y,X){F(R.currentFilter);return false});H(document).off("afterCancel",W).on("afterCancel",W,function(X){H(X.currentTarget).closest(".js-album-detail").find(".conversation-body, .profile-media-uploadphotos, .js-photo-preview").removeClass("h-hide-imp");return true});H(document).off("afterSave",".js-profile-media .js-content-entry form").on("afterSave",".js-profile-media  .js-content-entry form",function(a,Z){var c=H(".b-toolbar__item--active",this).data("panel");if(c=="b-content-entry-panel__content--gallery"){var X=H("#mediaList"),b=Number(X.data("totalpages")),d=Number(X.data("totalcount")),Y=(d%vBulletin.media.ALBUMS_PERPAGE)==0?b+1:b;if(Z.alert){openAlertDialog({title:vBulletin.phrase.get("media"),message:vBulletin.phrase.get(Z.alert),iconType:"alert"})}C(Y)}else{C(1)}return false});if(O.length){B=O.find(".conversation-toolbar-wrapper").data("allow-history")=="1";K=new vBulletin.history.instance(B);E=O.closest(".canvas-widget").find(".js-module-top-anchor").attr("id");O.off("click",".media-toolbar-filter").on("click",".media-toolbar-filter",function(X){H(".filter-wrapper",this).toggleClass("selected");H(".arrow .vb-icon",this).toggleClass("vb-icon-triangle-down-wide vb-icon-triangle-up-wide");H(X.target).closest(".conversation-toolbar-wrapper").find(".toolbar-filter-overlay").slideToggle("slow",function(){var Y="media_filter";if(H(this).is(":visible")){H("body").off("click."+Y).on("click."+Y,function(b){if(H(b.target).closest(".toolbar-filter-overlay").length==0&&H(b.target).closest(".toolbar-filter").length==0){H("body").off("click."+Y);H(".media-toolbar-filter").trigger("click")}});var a={};var Z=vBulletin.isScrolledIntoView(this,a);if(!Z){H("html,body").animate({scrollTop:"+="+Math.abs(a.bottom)},"fast")}}else{H("body").off("click."+Y)}})});H("form.media-filter-overlay",O).trigger("reset");O.off("change",".media-filter-overlay input[type=radio]").on("change",".media-filter-overlay input[type=radio]",function(f,c){var Y;if(!K.isEnabled()&&B){Y=vBulletin.makePaginatedUrl(location.href,1);location.href=vBulletin.makeFilterUrl(Y,this.name,this.value,b,E);return true}R.currentDateFilter=this.value;var Z=T(H(f.target));var b=H(".media-tab");if(b.data("perpage")){Z.perpage=b.data("perpage")}F(Z,true,0,b.data("callbacks"));if(!H(this).data("bypass-filter-display")){vBulletin.conversation.displaySelectedFilterText(this,this.value)}if(K.isEnabled()&&!c){var d=vBulletin.getSelectedFilters(H("form.toolbar-filter-overlay",b)),i=this.name,X=this.value,h=H(".conversation-toolbar-wrapper .filtered-by",b),a=b.data("url-path")?b.data("url-path"):"#"+b.attr("id"),g={from:"filter",page:1,tab:a,filters:d,filtervalue:X,filtername:i};if(!h.data("reset")){Y=vBulletin.makePaginatedUrl(location.href,1);Y=vBulletin.makeFilterUrl(Y,i,X,b)}else{Y=location.pathname.replace(/\/page[0-9]+/,"")}K.pushState(g,document.title,Y);h.data("reset",null)}});O.off("click",".filtered-by .x").on("click",".filtered-by .x",function(Z){var X=H(this).closest(".filtered-by"),b=H(this).closest(".filter-text"),Y=b.data("filter-name");if(!K.isEnabled()&&B){location.href=vBulletin.makeFilterUrl(location.href,Y,b.data("filter-value"),O,E);return false}$defaultSelectedFilter=H(".toolbar-filter-overlay .filter-options input[name={0}]".format(Y),O).prop("checked",false).filter(".js-default-checked");b.remove();var a=X.find(".filter-text").length;if(a==0){X.addClass("h-hide");X.data("reset",true)}else{if(a==1){X.find(".clear-all").addClass("h-hide")}}if($defaultSelectedFilter.length==1){$defaultSelectedFilter.data("bypass-filter-display",true);$defaultSelectedFilter.trigger("click");$defaultSelectedFilter.data("bypass-filter-display",null)}})}vBulletin.initFlexGridFixLastRowAll()}function Q(){H(document).ready(P);vBulletin.media.loadGalleryById=F}Q()})(jQuery);