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
window.vBulletin=window.vBulletin||{};window.vBulletin.phrase=window.vBulletin.phrase||{};window.vBulletin.phrase.precache=window.vBulletin.phrase.precache||[];window.vBulletin.phrase.precache=$.merge(window.vBulletin.phrase.precache,[]);window.vBulletin.options=window.vBulletin.options||{};window.vBulletin.options.precache=window.vBulletin.options.precache||[];window.vBulletin.options.precache=$.merge(window.vBulletin.options.precache,[]);(function(G,J,L){var B=false;function K(P){var S=P.find(".js-widget-search2-nodelist-item"),O=0,R=P.find(".js-fixed-event-year-header"),Q=P.find(".js-year-header");if(R.length==0){R=G("<li />").addClass("js-fixed-event-year-header b-event__listyear b-event__listyear--fixed h-hide").prependTo(P)}G.each(Q,function(T,V){var U=G(V),W=U.next();if(!W.hasClass("js-widget-search2-nodelist-item")){U.remove()}});G.each(S,function(T,X){var V=G(X),U=parseInt(V.data("start-year"),10),W=V.prev(".js-year-header");if(U>O){if(!W.length){G("<li />").addClass("js-year-header b-event__listyear").text(U).data("start-year",U).insertBefore(V)}O=U}})}function F(R){var U=R.find(".js-widget-search2-nodelist-item"),Q=R.data("search-max-page")||false,O=R.data("loaded-pages")||{};U.removeClass("b-event--lastchild h-hide");K(R);R.find(".js-loading").appendTo(R);R.find(".js-no-more-items").appendTo(R);if(Q&&O.hasOwnProperty(Q)){R.find(".js-no-more-items").removeClass("h-hide")}var S=R.height(),T=R.scrollTop();R.height("auto");var P=R.height();R.height(S);R.scrollTop(T);R.data("full-height",P)}function M(P){if(P.data("search-has-more")||P.find(".js-widget-search2-nodelist-item.h-hide").length){K(P);var O=P.parents(".js-show-on-tabs-create.h-hide");if(O.length){O.removeClass("h-hide")}P.css({height:P.height(),overflowY:"scroll",});if(O.length){O.addClass("h-hide")}}}function C(O,S){var R=S["$anchorItem"]&&S["$anchorItem"].offset().top||0,Q=S.anchorItemPreviousTop-R,P=O.scrollTop();O.scrollTop(O.scrollTop()-Q)}function I(V){var U=V.find(".js-widget-search2-nodelist-item"),Z={},R=V.offset().top,W=R+V.height(),O=false,Q=false,a,T=Number.POSITIVE_INFINITY,Y,S=0,P,X=0;G.each(U,function(b,f){var e=G(f),h=e.hasClass("h-hide"),d=e.offset().top,c=d+e.height(),g=e.data("nodeid");Z["nodeid"+g]=true;O=(!h&&!(c<=R||W<=d));Q=(R<=d);if(typeof a=="undefined"&&O&&Q){a=e;T=a.data("page")}if(O){Y=e;S=Y.data("page")}});return{"$items":U,haveNodeids:Z,"$firstVisibleItem":a,firstVisiblePage:T,"$lastVisibleItem":Y,lastVisiblePage:S,"$anchorItem":Y,anchorItemPreviousTop:(Y&&Y.offset().top||0),}}function H(X){var Y=G(X.target),Q=Y.scrollTop(),P=Y.data("full-height"),U=5,R=Y.position().top,a=Y.height(),W=Y.find(".js-widget-search2-nodelist-item"),O=W.slice(-U).first(),d=O.position().top-R,T=d<a,Z=W.slice(0,U).last(),c=Z.position().top-R+Z.height(),b=c>R,S=Y.find(".js-fixed-event-year-header");if(Q>0){var V=0;G.each(W,function(f,j){var i=G(j),h=i.position().top-R,g=i.height(),e=h+g;if(e>0){V=i.data("start-year");return false}});S.css({top:R,width:W.first().width(),}).text(V).removeClass("h-hide")}else{S.addClass("h-hide")}if(T){D(Y,"bottom")}else{if(b){D(Y,"top")}}}function E(){G(".js-widget-search2").each(function(O,Q){var R=G(Q),S=R.find(".js-fixed-event-year-header"),P=R.find(".js-widget-search2-nodelist-item").first();S.css("width",P.width())})}function D(V,S){if(B){return }B=true;var O=V.closest(".js-widget-search2"),U=O.data("widget-id"),Z=O.data("widget-instance-id"),P=V.data("search-max-page")||Number.POSITIVE_INFINITY,a=V.data("loaded-pages")||{};if(G.isEmptyObject(a)){var W=O.data("current-page");a={};a[W]=W;V.data("loaded-pages",a)}var Y=function(d,c){return(parseInt(d,10)-parseInt(c,10))};var Q=Object.keys(a).sort(Y),R;if(S=="bottom"){R=a[Q[Q.length-1]]+1;if(P<R){B=false;return }}else{if(S=="top"){R=a[Q[0]]-1;if(R<1){B=false;return }}else{B=false;return }}V.find(".js-loading").removeClass("h-hide");var X=O.data("search-json"),T={widgetinstanceid:Z,widgetid:U,currentPage:R,};if(X){T.widgetConfig=X}else{T.page=J.pageData}vBulletin.AJAX({call:"/ajax/render/widget_search2",data:T,success:function(e){var m=G(e),b=m.find(".js-widget-search2-nodelist"),k=b.find(".js-widget-search2-nodelist-item"),l=I(V),i=l["$items"],f=i.first(),o=i.length,n=l.haveNodeids,h=b.data("search-has-more");var j=m.data("current-page");a[j]=j;V.data("loaded-pages",a);if(!h){V.data("search-max-page",j)}G.each(k,function(p,r){var q=G(r),s=q.data("nodeid");if(typeof n["nodeid"+s]=="undefined"){if(S=="bottom"){V.append(q)}else{if(S=="top"){f.before(q)}}o++}});if(o>200){var g,c,d=V.find(".js-widget-search2-nodelist-item");if(S=="bottom"){g=a[Q[0]]}else{if(S=="top"){g=a[Q[Q.length-1]]}}c=d.filter("[data-page='"+g+"']");if(g<=l.firstVisiblePage||g>=l.lastVisiblePage){c.remove();delete a[g];V.data("loaded-pages",a)}}F(V);C(V,l)},complete:function(){V.find(".js-loading").addClass("h-hide");B=false}})}function A(P){if(P.data("vb-continuous-scroll-initialized")=="1"){return }var O=P.find(".js-widget-search2-nodelist");M(O);F(O);O.off("scroll",H).on("scroll",H);G(J).off("resize",E).on("resize",E);P.attr("vb-continuous-scroll-initialized","1")}function N(){G(function(){var O=G(".js-widget-search2");if(!O.length){return }G.each(O,function(P,Q){A(G(Q))})})}N()})(jQuery,window,window.document);