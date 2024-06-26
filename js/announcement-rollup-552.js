// ***************************
// js.compressed/jquery/jquery.condense.custom.min.js
// ***************************
/**
* Condense 0.1 - Condense and expand text heavy elements
*
* (c) 2008 Joseph Sillitoe
* Dual licensed under the MIT License (MIT-LICENSE) and GPL License,version 2 (GPL-LICENSE). 
*/
 
/*
*  Modified for vBulletin:
*  - Added callbacks when initializing condense plugin is done (onInit), 
*  		when condensing is done (onCondense), 
*  		when expanding is done (onExpand) and
*  		when condensing or expanding is done (onToggle).
*  - Added patch for infinite loop when condense length cutoff occurs in consecutive spaces (https://github.com/jsillitoe/jquery-condense-plugin/issues/5)
*/
(function(a){function b(b,d){if(a.trim(b.text()).length<=d.condensedLength+d.minTrail){g("element too short: skipping.");return false}var e=a.trim(b.html());var f=a.trim(b.text());var h=d.delim;var i=b.clone();var j=0;do{var k=c(e,d.delim,d.condensedLength+j);i.html(e.substring(0,k+1));var l=i.text().length;var m=i.html().length;j=i.html().length-l;g("condensing... [html-length:"+m+" text-length:"+l+" delta: "+j+" break-point: "+k+"]")}while(j&&i.text().length<d.condensedLength);if(f.length-l<d.minTrail){g("not enough trailing text: skipping.");return false}g("clone condensed. [text-length:"+l+"]");return i}function c(a,b,c){var e=false;var f=c;do{var f=a.indexOf(b,f);if(f<0){g("No delimiter found.");return a.length}e=true;while(d(a,f)){f++;e=false}}while(!e);g("Delimiter found in html at: "+f);return f}function d(a,b){return a.indexOf(">",b)<a.indexOf("<",b)}function e(a,b){g("Condense Trigger: "+a.html());var c=a.parent();var d=c.next();d.show();var e=d.width();var f=d.height();d.hide();var h=c.width();var i=c.height();c.animate({height:f,width:e,opacity:1},b.lessSpeed,b.easing,function(){c.height(i).width(h).hide();d.show();if(typeof b.onCondense=="function"){b.onCondense.apply(a,[d])}if(typeof b.onToggle=="function"){b.onToggle.apply(a,[c,d,true])}})}function f(a,b){g("Expand Trigger: "+a.html());var c=a.parent();var d=c.prev();d.show();var e=d.width();var f=d.height();d.width(c.width()+"px").height(c.height()+"px");c.hide();d.animate({height:f,width:e,opacity:1},b.moreSpeed,b.easing,function(){if(typeof b.onExpand=="function"){b.onExpand.apply(a,[d])}if(typeof b.onToggle=="function"){b.onToggle.apply(a,[d,c,false])}});if(c.attr("id")){var h=c.attr("id");c.attr("id","condensed_"+h);d.attr("id",h)}}function g(a){if(window.console&&window.console.log){window.console.log(a)}}a.fn.condense=function(c){a.metadata?g("metadata plugin detected"):g("metadata plugin not present");var d=a.extend({},a.fn.condense.defaults,c);return this.each(function(){$this=a(this);var c=a.metadata?a.extend({},d,$this.metadata()):d;g("Condensing ["+$this.text().length+"]: "+$this.text());var h=b($this,c);if(h){$this.attr("id")?$this.attr("id","condensed_"+$this.attr("id")):false;var i=" <span class='condense_control condense_control_more' style='cursor:pointer;'>"+c.moreText+"</span>";var j=" <span class='condense_control condense_control_less' style='cursor:pointer;'>"+c.lessText+"</span>";h.append(c.ellipsis+i);$this.after(h).hide().append(j);a(".condense_control_more",h).click(function(){g("moreControl clicked.");f(a(this),c)});a(".condense_control_less",$this).click(function(){g("lessControl clicked.");e(a(this),c)})}if(typeof c.onInit=="function"){c.onInit.apply(this,[h])}})};a.fn.condense.defaults={condensedLength:200,minTrail:20,delim:" ",moreText:"[more]",lessText:"[less]",ellipsis:" ( ... )",moreSpeed:"normal",lessSpeed:"normal",easing:"linear",onInit:null,onCondense:null,onExpand:null,onToggle:null}})(jQuery);

// ***************************
// js.compressed/announcement.js
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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,[]);window.vBulletin.options=window.vBulletin.options||{};window.vBulletin.options.precache=window.vBulletin.options.precache||[];window.vBulletin.options.precache=$.merge(window.vBulletin.options.precache,[]);(function(A){A(function(){if(A(".announcement-tabs").length>0){A(".announcement-tabs").tabs({fx:{opacity:"toggle"},show:function(D,E){var B=A(E.panel).attr("data-id"),C=vBulletin.cookie.fetchBbarrayCookie("announcements_displayed",B);if(!C){vBulletin.cookie.setBbarrayCookie("announcements_displayed",B,1,true)}}}).each(function(){var B=A(this),C=A("> .tab",B).length;A(".tab",B).each(function(){var F=parseInt(A(this).attr("data-index")),E=A(this);A("span.prev",E)[F==0?"hide":"show"]();A("span.next",E)[F==C-1?"hide":"show"]();A("span.prev",E).on("click",function(G){B.tabs("option","active",F-1)});A("span.next",E).on("click",function(G){B.tabs("option","active",F+1)});var D={condensedLength:250,minTrail:20,delim:" ",moreText:A(".condense-text .see-more",B).html(),lessText:A(".condense-text .see-less",B).html(),ellipsis:"...",moreSpeed:"fast",lessSpeed:"fast",easing:"linear"};A(".announcementtext",E).removeClass("h-hide").condense(D)})})}})})(jQuery);;

