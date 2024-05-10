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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,["error","error_adding_search_tips_code_x","invalid_json_string","invalid_search_syntax","invalid_server_response_please_try_again","search_tag_cloud","please_select_past_date","please_select_valid_date_range","content_type_Text","content_type_Gallery","content_type_Link","content_type_Photo","content_type_Poll","content_type_PrivateMessage","content_type_Video","save_as_search_module","saved_search_module_as_x","search_module_already_exists","search_module_not_found","please_enter_search_module_name","error_saving_search_module_x"]);(function(A){var B=[".search-fields-widget",".search-results-widget","#search-config-dialog"];if(!vBulletin.pageHasSelectors(B)){return false}window.vBulletin.search=window.vBulletin.search||{};window.vBulletin.search.SearchControl=function(G){var C=A(G),F=false,D=false;function K(){var Q=C.find(".searchSwitchToAdvanced"),O=C.find(".searchSwitchToForm"),M=Q.is(":visible"),P=O.is(":visible");if(M==P){Q.toggle(true);O.toggle(false);C.find(".form_row.form-row-json").addClass("h-hide-imp").nextAll().show()}Q.off("click").on("click",function(){var R=J();if(!R){return false}var S=JSON.stringify(R);C.find(".searchFields_searchJSON").val(S);C.find(".form_row.form-row-json").removeClass("h-hide-imp").nextAll().hide();Q.toggle(false);O.toggle(true)});O.off("click").on("click",function(){if(I(C.find(".searchFields_searchJSON").val())){Q.toggle(true);O.toggle(false);C.find(".form_row.form-row-json").addClass("h-hide-imp").nextAll().show()}});var N=C.find(".advSearchForm");if(N.length>0){N.submit(H)}F=new vBulletin.tagEditor.instance("{0}-searchFields_tag".format(G.replace(/\#/g,".")),true);var L=C.find(".searchFields_author");if(L.length>0){D=new vBulletin_Autocomplete(L,{apiClass:"user",containerClass:"entry-field h-clearfix"})}C.find(".js-search-keywords").off("keydown").on("keydown",function(R){if(R.which==13){if(N.length>0){N.submit()}}});C.find(".searchFields_last_visit").off("click").on("click",function(R){C.find(".datefield").prop("disabled",A(this).prop("checked"));if(A(this).is(":checked")){vBulletin.flatpickr.disablePicker(C.find(".searchFields_from_date"));vBulletin.flatpickr.disablePicker(C.find(".searchFields_to_date"))}else{vBulletin.flatpickr.enablePicker(C.find(".searchFields_from_date"));vBulletin.flatpickr.enablePicker(C.find(".searchFields_to_date"))}});C.find(".searchFields_channel_param").off("click").on("click",function(R){if(A(this).prop("checked")){C.find(".searchFields_channel").selectBox("disable")}else{C.find(".searchFields_channel").selectBox("enable")}});C.find(".search-tips").off("click").on("click",function(R){A(".search-tips-dialog").first().dialog({title:vBulletin.phrase.get("search_tips"),autoOpen:false,modal:true,resizable:false,closeOnEscape:true,showCloseButton:true,width:500,dialogClass:"dialog-container search-tips-dialog-container dialog-box"}).dialog("open");R.stopPropagation();return false});C.find(".searchFields_channel").selectBox();C.find(".searchFields_order_field").selectBox();C.find(".searchFields_order_direction").selectBox();C.find(".searchFields_view").selectBox()}function H(M){if(!C.find(".searchAdvancedFields").is(":visible")){var L=J();if(!L){return false}if(!L.author&&!L.channel&&!L.keywords&&!L.tag&&!L.type&&!L.date&&!L.last_visit&&!L.my_following&&!L.eventstartdate){vBulletin.warning("error","error_no_criteria");return false}C.find(".searchFields_searchJSON").val(JSON.stringify(L));C.find("form.advSearchForm").append(A('input[name^="humanverify"]').clone().attr("type","hidden"))}}function J(){var P={};if(C.find(".searchSwitchToForm").is(":visible")){P=JSON.parse(C.find(".searchFields_searchJSON").val()||"{}");if(P.length==0){vBulletin.warning("error","invalid_json_string");return }return P}C.find("input[placeholder].placeholder").each(function(){if(A(this).val()==A(this).attr("placeholder")){A(this).val("")}});var q=C.find(".js-search-keywords");if(q.length>0&&A.trim(q.val()).length>0){P.keywords=A.trim(q.val())}if(C.find(".searchFields_title_only:checked").length>0){P.title_only=1}if(C.find(".searchFields_starter_only:checked").length>0){P.starter_only=1}if(C.find(".searchFields_myFriends:checked").length>0){P.author="myFriends"}else{if(C.find(".searchFields_iFollow:checked").length>0){P.author="iFollow"}else{if(D){var p=D.getLabels();if(p.length>0){P.author=p}}}}var Z=C.find(".tag-input");if(Z.length>0){var N=C.find(".tag-input").val();if(N.length>0){var c=N.split(",");if(c.length>0){P.tag=c}}}if(C.find(".searchFields_last_visit:checked").length>0){P.date="lastVisit"}else{var M={},R=C.find(".searchFields_from_days");if(R.length>0&&A.trim(R.val()).length>0){M.from=A.trim(R.val())}else{var k=C.find(".searchFields_from_date"),l=Date.now();if(k.length>0&&A.trim(k.val()).length>0){var a=Date.parse(k.val());if(a<=0){vBulletin.warning("error","invalid_start_date");return false}else{if(a>l){vBulletin.warning("error","please_select_past_date");return false}}M.from=A.trim(k.val())}var d=C.find(".searchFields_to_date");if(d.length>0&&A.trim(d.val()).length>0){var X=Date.parse(d.val());if(X<=0){vBulletin.warning("error","invalid_end_date");return false}else{if(X>l){vBulletin.warning("error","please_select_past_date");return false}}if(a&&a>X){vBulletin.warning("error","please_select_valid_date_range");return false}M.to=A.trim(d.val())}}if(!A.isEmptyObject(M)){P.date=M}}var f=["eventstartdate","eventenddate"],j=f.length;for(var h=0;h<j;h++){var O=f[h],W=C.find('[name="searchFields['+O+'][from]"]'),r=W.length&&W.val().trim()||"",m=C.find('[name="searchFields['+O+'][to]"]'),L=m.length&&m.val().trim()||"",Y={};if(r){Y.from=r}if(L){Y.to=L}if(!A.isEmptyObject(Y)){P.eventstartdate=Y}}var e=C.find(".searchFields_featured");if(e.length>0&&e.prop("checked")){P.featured=1}var V=C.find(".searchFields_my_following");if(V.length>0&&V.prop("checked")){P.my_following=1}var b=[];C.find(".searchFields_type:checked").each(function(i,s){b.push(A(s).val())});if(A(b).length>0){P.type=b}if(C.find(".searchFields_channel_param:checked").length>0){P.channel={param:"channelid"}}else{var n=C.find("select.searchFields_channel");if(n.length>0){var S=n.val();if(S){P.channel=S}}}var Q=C.find(".searchFields_order_field"),U=C.find(".searchFields_order_direction");if(Q.length>0&&U.length>0&&Q.val()!=""){P.sort=Q.val();if(U.val()!=""){P.sort={};P.sort[Q.val()]=U.val()}}var T=C.find(".searchFields_view");if(T.length>0){P.view=T.val()}var o=C.find(".searchFields_exclude");if(o.length>0&&A.trim(o.val()).length>0){P.exclude=A.trim(o.val()).split(",")}var g=C.find(".searchFields_exclude_type");if(g.length>0&&A.trim(g.val()).length>0){P.exclude_type=A.trim(g.val()).split(",")}return P}function I(O,a,L,p){var m=O;if(typeof O=="string"){try{O=JSON.parse(O||"{}");if(O.length==0){vBulletin.warning("error","invalid_json_string");return false}}catch(R){vBulletin.warning("error","invalid_json_string");return false}}else{m=JSON.stringify(O)}var Y=C.find(".js-search-keywords");if(Y.length>0){Y.val(O.keywords||"");delete O.keywords}var S={".searchFields_myFriends":"myFriends",".searchFields_iFollow":"iFollow"};A.each(S,function(i,v){var w=C.find(i);if(w.length>0){var x=(O.author&&O.author==v);if(x){delete O.author}w.prop("checked",x)}});var P=C.find(".searchFields_author");if(P.length>0){var t=false;if(A.isArray(O.author)){t=O.author}else{if(O.exactname){t=[O.author]}}if(t){D.setElements(t);delete O.author;delete O.exactname}}var u=C.find(".tag-input");if(u.length>0){var c=O.tag||[];if(!Array.isArray(c)){c=[c]}u.val(c);F.setTags(c);C.find(".tag-list span").html(c.join(", "));delete O.tag}var j=C.find(".searchFields_last_visit"),Z=(O.date&&O.date&&O.date=="lastVisit");if(j.length>0){j.prop("checked",Z);if(Z){delete O.date}}var e={".searchFields_from_date":"from",".searchFields_to_date":"to",".searchFields_from_days":"from"};A.each(e,function(i,x){var y=C.find(i);if(y.length>0){if(vBulletin.flatpickr){var w=vBulletin.flatpickr.getInstance(y);if(w){if(j.is(":checked")){vBulletin.flatpickr.disablePicker(y)}else{vBulletin.flatpickr.enablePicker(y)}}}if(!Z){var v="";if(O.date&&O.date[x]){v=O.date[x];delete O.date[x]}y.val(v)}}});if(A.isEmptyObject(O.date)){delete O.date}var h=["eventstartdate","eventenddate"],l=h.length;for(var k=0;k<l;k++){var N=h[k],V=C.find('[name="searchFields['+N+'][from]"]'),q=C.find('[name="searchFields['+N+'][to]"]');if(O[N]){if(O[N]["to"]&&q.length){q.val(O[N]["to"])}if(O[N]["from"]&&V.length){V.val(O[N]["from"])}}delete O[N]}var U={".searchFields_featured":"featured",".searchFields_my_following":"my_following",".searchFields_starter_only":"starter_only",".searchFields_title_only":"title_only"};A.each(U,function(i,v){var w=C.find(i);if(w.length>0){w.prop("checked",(O[v]?true:false));delete O[v]}});var W=C.find(".searchFields_type_container");if(W.length>0){if(a){var d=W.find(".field-desc");d.prevAll().remove();A.each(a,function(v,i){if(v!="vBForum_PrivateMessage"){d.before(A('<label><input type="checkbox" name="searchFields[type][]" class="searchFields_type" value="'+v+'" /><span>'+vBulletin.phrase.get("content_type_"+i["class"])+"</span></label>"))}})}C.find(".searchFields_type").val(O.type||[]);delete O.type;delete O.exclude_type}var b=C.find(".searchFields_channel_param"),o=false;if(b.length>0){if(typeof O.channel=="object"&&O.channel.param){o=true;delete O.channel}b.prop("checked",o)}function f(i){i.selectBox("destroy");i.removeData("selectBoxControl");i.removeData("selectBoxSettings");i.selectBox()}var s=C.find(".searchFields_channel");if(s.length>0){var Q=false;if(b.length>0){s.prop("disabled",o)}if(L){s.children().remove();E(L,s);Q=true}if(!o){s.val(O.channel||"");delete O.channel;Q=true}if(Q){f(s)}}var n=C.find("select.searchFields_order_direction"),T=C.find("select.searchFields_order_field"),M="",g="";if(O.sort){if(typeof O.sort=="string"){M=O.sort}else{if(typeof O.sort=="object"){A.each(O.sort,function(i,v){M=i;g=v})}}delete O.sort}if(n.length>0){n.val(g);f(n)}if(T.length>0){T.val(M);f(T)}var X=C.find("select.searchFields_view");if(X.length>0){X.val(O.view||"");f(X);delete O.view}if(!A.isEmptyObject(O)){var r=C.find(".searchSwitchToAdvanced");if(r.length>0){C.find(".form_row.form-row-json").removeClass("h-hide-imp").nextAll().hide();r.hide();C.find(".searchSwitchToForm").show();C.find(".searchFields_searchJSON").val(m)}return false}return true}function E(L,M){A.each(L,function(O,N){M.append(A("<option></option>").val(O).html(str_repeat("&nbsp;",N.depth*3).concat(N.htmltitle)));if(N.channels){E(N.channels,M)}})}this.load=function(){return J()};this.set=function(O,M,L,N){return I(O,M,L,N)};K()};A(document).ready(function(){var D=A("#advancedSearchFields"),J=A(".search-results-widget");vBulletin.truncatePostContent(J);if(D.length>0){var K=D.find(".searchFields_from_date"),C=D.find(".searchFields_to_date"),M=(new Date());if(K.data("vb-flatpicker-initialized")){vBulletin.flatpickr.getInstance(K).set("maxDate",M)}else{K.one("vb-flatpicker-initialized",function(){vBulletin.flatpickr.getInstance(A(this)).set("maxDate",M)})}if(C.data("vb-flatpicker-initialized")){vBulletin.flatpickr.getInstance(C).set("maxDate",M)}else{C.one("vb-flatpicker-initialized",function(){vBulletin.flatpickr.getInstance(A(this)).set("maxDate",M)})}var E=new vBulletin.search.SearchControl(D.selector);searchJSONStr=D.find(".searchFields_searchJSON").val();if(searchJSONStr.length>0&&searchJSONStr!=D.find(".searchFields_searchJSON").attr("placeholder")){E.set(searchJSONStr,false,false,true)}}var L=A(".sort-controls",J);if(L.length>0){L.find(".searchFields_order_field").selectBox().change(function(){var R=O.find(".searchResults_searchJSON").val()||"{}",Q=JSON.parse(R);Q.sort={};Q.sort[A(this).val()]=L.find(".searchFields_order_direction").selectBox().val();O.find(".searchResults_searchJSON").val(JSON.stringify(Q));A(".resultSearchForm").submit()});L.find(".searchFields_order_direction").selectBox().change(function(){var R=O.find(".searchResults_searchJSON").val()||"{}",Q=JSON.parse(R);Q.sort={};Q.sort[L.find(".searchFields_order_field").selectBox().val()]=A(this).val();O.find(".searchResults_searchJSON").val(JSON.stringify(Q));A(".resultSearchForm").submit()});L.removeClass("h-invisible")}var O=A(".search-controls",J);if(O.length>0){var I=false,P=false,F=O.find(".searchFields_author");if(F.length>0){I=new vBulletin_Autocomplete(F,{apiClass:"user",containerClass:"entry-field h-clearfix"});O.find(".search-controls-members .removable-element .element-text").each(function(R,Q){I.addElement(A(Q).html())})}if(O.find(".searchResultTagEditor").length>0){P=new vBulletin.tagEditor.instance(".search-controls .searchResultTagEditor",true);O.find(".search-controls-tags .removable-element .element-text").each(function(R,Q){P.addTag(A(Q).html())})}O.find("button").off("click").on("click",function(S){var T=O.find(".searchResults_searchJSON").val()||"{}",Q=JSON.parse(T),R=A(this).val();switch(R){case"keywords":Q.keywords=O.find(".js-search-keywords").val();Q.title_only=O.find(".searchFields_title_only").is(":checked");break;case"author":Q.author=I.getLabels();Q.starter_only=O.find(".searchFields_starter_only").is(":checked");break;case"tags":Q.tag=P.getTags();break;default:return ;break}O.find(".searchResults_searchJSON").val(JSON.stringify(Q));A(".resultSearchForm").submit()});O.find(".removable-element .element-x").off("click").on("click",function(U){var V=O.find(".searchResults_searchJSON").val()||"{}",R=JSON.parse(V),T=A(this).parents(".search-control-popup");if(T.hasClass("search-controls-members")){A(this).parents(".removable-element").remove();var Q=[];T.find(".removable-element .element-text").each(function(X,W){Q.push(A(W).html())});R.author=Q;if(Q.length==0){delete R.author}}if(T.hasClass("search-controls-tags")){A(this).parents(".removable-element").remove();var S=[];T.find(".removable-element .element-text").each(function(X,W){S.push(A(W).html())});R.tag=S;if(S.length==0){delete R.tag}}O.find(".searchResults_searchJSON").val(JSON.stringify(R));A(".resultSearchForm").submit()})}if(vBulletin.inlinemod&&typeof vBulletin.inlinemod.init=="function"){vBulletin.inlinemod.init(J)}J.off("click",".js-post-control__ip-address").on("click",".js-post-control__ip-address",vBulletin.conversation.showIp);J.off("click",".js-post-control__vote").on("click",".js-post-control__vote",function(Q){if(A(Q.target).closest(".js-bubble-flyout").length==1){vBulletin.conversation.showWhoVoted.apply(Q.target,[Q])}else{vBulletin.conversation.votePost.apply(this,[Q])}return false});J.off("click",".js-post-control__edit").on("click",".js-post-control__edit",vBulletin.conversation.editPost);J.off("click",".js-post-control__flag").on("click",".js-post-control__flag",vBulletin.conversation.flagPost);J.off("click",".js-post-control__comment").on("click",".js-post-control__comment",vBulletin.conversation.toggleCommentBox);J.off("click",".js-comment-entry__post").on("click",".js-comment-entry__post",function(Q){vBulletin.conversation.postComment.apply(this,[Q,function(R){location.reload()}])});var G=(typeof J.data("keywords")!="undefined")?J.data("keywords").toString():"";if(G.length>0){var N;A(G.split(" ")).each(function(R,Q){N=Q.toUpperCase();if(N=="OR"||N=="AND"){return }A(".post-header",J).highlight(Q);if(!A(".searchFields_title_only",J).attr("checked")){A(".post-content",J).highlight(Q)}})}if(J.length==1){var H=A(".pagenav-form",J).attr("action");if(H){A(".js-pagenav .js-pagenav-button",J).each(function(){var R=A(this),Q=R.prop("href");if(!Q||(pageData.baseurl&&Q==pageData.baseurl)){R.prop("href",H+"&p="+parseInt(R.data("page"),10))}})}}})})(jQuery);