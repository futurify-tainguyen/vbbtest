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
(function(A){window.vBulletin=window.vBulletin||{};vBulletin.hv=vBulletin.hv||{};vBulletin.hv.resetOnError=function(B){if(vBulletin.ajaxtools.hasError(B,/humanverify_.*_wronganswer/)){vBulletin.hv.reset()}return true};vBulletin.hv.reset=function(B){var C;if($(".humanverify.humanverify_image").length>0){$(".humanverify.humanverify_image .refresh_imagereg").click();C=$(".humanverify.humanverify_image .imageregt")}else{if($(".js-humanverify-recaptcha2").length>0&&typeof grecaptcha!="undefined"&&typeof grecaptcha.reset=="function"){grecaptcha.reset()}else{if($(".humanverify.humanverify_question").length>0){vBulletin.AJAX({call:"/ajax/render/humanverify",data:{action:"register"},success:function(D){$(".humanverify.humanverify_question").replaceWith(D);if(B){$(".humanverify.humanverify_question .answer").focus()}}});C=$(".humanverify.humanverify_question .answer")}}}if(B&&C&&C.length>0){C.focus()}};vBulletin.hv.imagereg=vBulletin.hv.imagereg||{};vBulletin.hv.imagereg.init=function(B){if(typeof B=="undefined"||B.length==0){B=$(document)}$(".humanverify_image",B).each(function(){var E=$(this),D=$(".refresh_imagereg",E),C=vBulletin.hv.imagereg.fetch_image;D.off("click").on("click",E,C);$(".imagereg",E).off("click").on("click",E,C);D.removeClass("h-hide")})};vBulletin.hv.imagereg.fetch_image=function(C){var B=C.data;$(".progress_imagereg",B).removeClass("h-hide");vBulletin.AJAX({call:"/ajax/api/hv/generateToken",success:function(D){var E=D.hash;$("input.hash",B).val(E);$(".imagereg",B).attr("src",pageData.baseurl+"/hv/image?hash="+E);$(".imageregt",B).val("")},api_error:function(){},complete:function(){$(".progress_imagereg",B).addClass("h-hide")}});return false};vBulletin.hv.show=function(C){var B=C.find(".imagereg");C.removeClass("h-hide");if(B.height()!=B.attr("height")){C.find(".refresh_imagereg").click()}};vBulletin.hv.init=function(B){vBulletin.hv.imagereg.init(B)};window.recaptcha2callback=function(B){$(".js-humanverify-recaptcha2").closest("form").find(".js-humanverify-recaptcha2-response").val(B)};$(document).ready(function(){vBulletin.hv.imagereg.init()})})(jQuery);