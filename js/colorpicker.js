(function(B){var A=function(){var v={},C,q=65,V,s='<div class="colorpicker"><div class="colorpicker_color"><div><div></div></div></div><div class="colorpicker_hue"><div></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_cancel"></div><div class="colorpicker_submit"></div></div>',e={eventName:"click",onShow:function(){},onBeforeShow:function(){},onHide:function(){},onChange:function(){},onSubmit:function(){},color:"ff0000",livePreview:true,flat:false},m=function(w,y){var x=J(w);B(y).data("colorpicker").fields.eq(1).val(x.r).end().eq(2).val(x.g).end().eq(3).val(x.b).end()},W=function(w,x){B(x).data("colorpicker").fields.eq(4).val(w.h).end().eq(5).val(w.s).end().eq(6).val(w.b).end()},G=function(w,x){B(x).data("colorpicker").fields.eq(0).val(u(w)).end()},M=function(w,x){B(x).data("colorpicker").selector.css("backgroundColor","#"+u({h:w.h,s:100,b:100}));B(x).data("colorpicker").selectorIndic.css({left:parseInt(150*w.s/100,10),top:parseInt(150*(100-w.b)/100,10)})},j=function(w,x){B(x).data("colorpicker").hue.css("top",parseInt(150-150*w.h/360,10))},H=function(w,x){B(x).data("colorpicker").currentColor.css("backgroundColor","#"+u(w))},h=function(w,x){B(x).data("colorpicker").newColor.css("backgroundColor","#"+u(w))},P=function(w){var y=w.charCode||w.keyCode||-1;if((y>q&&y<=90)||y==32){return false}var x=B(this).parent().parent();if(x.data("colorpicker").livePreview===true){E.apply(this)}},E=function(x){var y=B(this).parent().parent(),w;if(this.parentNode.className.indexOf("_hex")>0){y.data("colorpicker").color=w=N(b(this.value))}else{if(this.parentNode.className.indexOf("_hsb")>0){y.data("colorpicker").color=w=F({h:parseInt(y.data("colorpicker").fields.eq(4).val(),10),s:parseInt(y.data("colorpicker").fields.eq(5).val(),10),b:parseInt(y.data("colorpicker").fields.eq(6).val(),10)})}else{y.data("colorpicker").color=w=I(o({r:parseInt(y.data("colorpicker").fields.eq(1).val(),10),g:parseInt(y.data("colorpicker").fields.eq(2).val(),10),b:parseInt(y.data("colorpicker").fields.eq(3).val(),10)}))}}if(x){m(w,y.get(0));G(w,y.get(0));W(w,y.get(0))}M(w,y.get(0));j(w,y.get(0));h(w,y.get(0));y.data("colorpicker").onChange.apply(y,[w,u(w),J(w)])},Q=function(w){var x=B(this).parent().parent();x.data("colorpicker").fields.parent().removeClass("colorpicker_focus")},n=function(){q=this.parentNode.className.indexOf("_hex")>0?70:65;B(this).parent().parent().data("colorpicker").fields.parent().removeClass("colorpicker_focus");B(this).parent().addClass("colorpicker_focus")},l=function(w){var y=B(this).parent().find("input").focus();var x={el:B(this).parent().addClass("colorpicker_slider"),max:this.parentNode.className.indexOf("_hsb_h")>0?360:(this.parentNode.className.indexOf("_hsb")>0?100:255),y:w.pageY,field:y,val:parseInt(y.val(),10),preview:B(this).parent().parent().data("colorpicker").livePreview};B(document).bind("mouseup",x,U);B(document).bind("mousemove",x,p)},p=function(w){w.data.field.val(Math.max(0,Math.min(w.data.max,parseInt(w.data.val+w.pageY-w.data.y,10))));if(w.data.preview){E.apply(w.data.field.get(0),[true])}return false},U=function(w){E.apply(w.data.field.get(0),[true]);w.data.el.removeClass("colorpicker_slider").find("input").focus();B(document).unbind("mouseup",U);B(document).unbind("mousemove",p);return false},Y=function(w){var x={cal:B(this).parent(),y:B(this).offset().top};x.preview=x.cal.data("colorpicker").livePreview;B(document).bind("mouseup",x,T);B(document).bind("mousemove",x,K)},K=function(w){E.apply(w.data.cal.data("colorpicker").fields.eq(4).val(parseInt(360*(150-Math.max(0,Math.min(150,(w.pageY-w.data.y))))/150,10)).get(0),[w.data.preview]);return false},T=function(w){m(w.data.cal.data("colorpicker").color,w.data.cal.get(0));G(w.data.cal.data("colorpicker").color,w.data.cal.get(0));B(document).unbind("mouseup",T);B(document).unbind("mousemove",K);return false},Z=function(w){var x={cal:B(this).parent(),pos:B(this).offset()};x.preview=x.cal.data("colorpicker").livePreview;B(document).bind("mouseup",x,d);B(document).bind("mousemove",x,S);w.data=x;S(w)},O={x:-1,y:-1},S=function(w){if(O.x==w.pageX&&O.y==w.pageY){return }O.x=w.pageX;O.y=w.pageY;E.apply(w.data.cal.data("colorpicker").fields.eq(6).val(parseInt(100*(150-Math.max(0,Math.min(150,(w.pageY-w.data.pos.top))))/150,10)).end().eq(5).val(parseInt(100*(Math.max(0,Math.min(150,(w.pageX-w.data.pos.left))))/150,10)).get(0),[w.data.preview]);return false},d=function(w){m(w.data.cal.data("colorpicker").color,w.data.cal.get(0));G(w.data.cal.data("colorpicker").color,w.data.cal.get(0));B(document).unbind("mouseup",d);B(document).unbind("mousemove",S);return false},X=function(w){B(this).addClass("colorpicker_focus")},t=function(w){B(this).removeClass("colorpicker_focus")},L=function(w){B(this).addClass("colorpicker_focus")},a=function(w){B(this).removeClass("colorpicker_focus")},R=function(x){var y=B(this).parent();var w=y.data("colorpicker").color;y.data("colorpicker").origColor=w;H(w,y.get(0));y.data("colorpicker").onSubmit(w,u(w),J(w),y.data("colorpicker").el);B(y.data("colorpicker").el).ColorPickerHide()},g=function(w){var AA=B("#"+B(this).data("colorpickerId"));AA.data("colorpicker").onBeforeShow.apply(this,[AA.get(0)]);var AB=B(this).offset();var z=c();var y=AB.top+this.offsetHeight;var x=AB.left;if(y+176>z.t+z.h){y-=this.offsetHeight+176}if(x+356>z.l+z.w){x-=356}AA.css({left:x+"px",top:y+"px"});if(AA.data("colorpicker").onShow.apply(this,[AA.get(0)])!=false){AA.show()}B(document).bind("mousedown",{cal:AA},r);return false},r=function(w){if(!k(w.data.cal.get(0),w.target,w.data.cal.get(0))){if(w.data.cal.data("colorpicker").onHide.apply(this,[w.data.cal.get(0)])!=false){w.data.cal.hide()}B(document).unbind("mousedown",r)}},k=function(y,x,w){if(y==x){return true}if(y.contains){return y.contains(x)}if(y.compareDocumentPosition){return !!(y.compareDocumentPosition(x)&16)}var z=x.parentNode;while(z&&z!=w){if(z==y){return true}z=z.parentNode}return false},c=function(){var w=document.compatMode=="CSS1Compat";return{l:window.pageXOffset||(w?document.documentElement.scrollLeft:document.body.scrollLeft),t:window.pageYOffset||(w?document.documentElement.scrollTop:document.body.scrollTop),w:window.innerWidth||(w?document.documentElement.clientWidth:document.body.clientWidth),h:window.innerHeight||(w?document.documentElement.clientHeight:document.body.clientHeight)}},F=function(w){return{h:Math.min(360,Math.max(0,w.h)),s:Math.min(100,Math.max(0,w.s)),b:Math.min(100,Math.max(0,w.b))}},o=function(w){return{r:Math.min(255,Math.max(0,w.r)),g:Math.min(255,Math.max(0,w.g)),b:Math.min(255,Math.max(0,w.b))}},b=function(y){var w=6-y.length;if(w>0){var z=[];for(var x=0;x<w;x++){z.push("0")}z.push(y);y=z.join("")}return y},D=function(w){var w=parseInt(((w.indexOf("#")>-1)?w.substring(1):w),16);return{r:w>>16,g:(w&65280)>>8,b:(w&255)}},N=function(w){return I(D(w))},I=function(y){var x={h:0,s:0,b:0};var z=Math.min(y.r,y.g,y.b);var w=Math.max(y.r,y.g,y.b);var AA=w-z;x.b=w;if(w!=0){}x.s=w!=0?255*AA/w:0;if(x.s!=0){if(y.r==w){x.h=(y.g-y.b)/AA}else{if(y.g==w){x.h=2+(y.b-y.r)/AA}else{x.h=4+(y.r-y.g)/AA}}}else{x.h=-1}x.h*=60;if(x.h<0){x.h+=360}x.s*=100/255;x.b*=100/255;return x},J=function(w){var y={};var AC=Math.round(w.h);var AB=Math.round(w.s*255/100);var x=Math.round(w.b*255/100);if(AB==0){y.r=y.g=y.b=x}else{var AD=x;var AA=(255-AB)*x/255;var z=(AD-AA)*(AC%60)/60;if(AC==360){AC=0}if(AC<60){y.r=AD;y.b=AA;y.g=AA+z}else{if(AC<120){y.g=AD;y.b=AA;y.r=AD-z}else{if(AC<180){y.g=AD;y.r=AA;y.b=AA+z}else{if(AC<240){y.b=AD;y.r=AA;y.g=AD-z}else{if(AC<300){y.b=AD;y.g=AA;y.r=AA+z}else{if(AC<360){y.r=AD;y.g=AA;y.b=AD-z}else{y.r=0;y.g=0;y.b=0}}}}}}}return{r:Math.round(y.r),g:Math.round(y.g),b:Math.round(y.b)}},f=function(w){var x=[w.r.toString(16),w.g.toString(16),w.b.toString(16)];B.each(x,function(y,z){if(z.length==1){x[y]="0"+z}});return x.join("")},u=function(w){return f(J(w))},i=function(){var x=B(this).parent();var w=x.data("colorpicker").origColor;x.data("colorpicker").color=w;m(w,x.get(0));G(w,x.get(0));W(w,x.get(0));M(w,x.get(0));j(w,x.get(0));h(w,x.get(0));x.data("colorpicker").onChange.apply(x,[w,u(w),J(w)])};return{init:function(w){w=B.extend({},e,w||{});if(typeof w.color=="string"){if(w.color.indexOf("rgb(")!=-1){var x=w.color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);w.color=I({r:parseInt(x[1]),g:parseInt(x[2]),b:parseInt(x[3])})}else{w.color=N(w.color)}}else{if(w.color.r!=undefined&&w.color.g!=undefined&&w.color.b!=undefined){w.color=I(w.color)}else{if(w.color.h!=undefined&&w.color.s!=undefined&&w.color.b!=undefined){w.color=F(w.color)}else{return this}}}return this.each(function(){if(!B(this).data("colorpickerId")){var y=B.extend({},w);y.origColor=w.color;var AA="collorpicker_"+parseInt(Math.random()*1000);B(this).data("colorpickerId",AA);var z=B(s).attr("id",AA);if(y.flat){z.appendTo(this).show()}else{z.appendTo(document.body)}y.fields=z.find("input").bind("keyup",P).bind("change",E).bind("blur",Q).bind("focus",n);z.find("span").bind("mousedown",l).end().find(">div.colorpicker_current_color").bind("click",i);y.selector=z.find("div.colorpicker_color").bind("mousedown",Z);y.selectorIndic=y.selector.find("div div");y.el=this;y.hue=z.find("div.colorpicker_hue div");z.find("div.colorpicker_hue").bind("mousedown",Y);y.newColor=z.find("div.colorpicker_new_color");y.currentColor=z.find("div.colorpicker_current_color");z.data("colorpicker",y);z.find("div.colorpicker_submit").bind("mouseenter",X).bind("mouseleave",t).bind("click",R);z.find("div.colorpicker_cancel").bind("mouseenter",L).bind("mouseleave",a).bind("click",function(){var AB=B(this).parent();i.apply(this);B(AB.data("colorpicker").el).ColorPickerHide()});m(y.color,z.get(0));W(y.color,z.get(0));G(y.color,z.get(0));j(y.color,z.get(0));M(y.color,z.get(0));H(y.color,z.get(0));h(y.color,z.get(0));if(y.flat){z.css({position:"relative",display:"block"})}else{B(this).bind(y.eventName,g)}}})},showPicker:function(){return this.each(function(){if(B(this).data("colorpickerId")){g.apply(this)}})},hidePicker:function(){return this.each(function(){if(B(this).data("colorpickerId")){B("#"+B(this).data("colorpickerId")).hide()}})},setColor:function(w){if(typeof w=="string"){if(w.indexOf("rgb(")!=-1){var x=w.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);w=I({r:parseInt(x[1]),g:parseInt(x[2]),b:parseInt(x[3])})}else{w=N(w)}}else{if(w.r!=undefined&&w.g!=undefined&&w.b!=undefined){w=I(w)}else{if(w.h!=undefined&&w.s!=undefined&&w.b!=undefined){w=F(w)}else{return this}}}return this.each(function(){if(B(this).data("colorpickerId")){var y=B("#"+B(this).data("colorpickerId"));y.data("colorpicker").color=w;y.data("colorpicker").origColor=w;m(w,y.get(0));W(w,y.get(0));G(w,y.get(0));j(w,y.get(0));M(w,y.get(0));H(w,y.get(0));h(w,y.get(0))}})},getColor:function(){if(B(this).data("colorpickerId")){var x=B("#"+B(this).data("colorpickerId"));var w=x.data("colorpicker").color;var y=J(w);return{rgb:y,hex:f(y),hsb:w}}}}}();B.fn.extend({ColorPicker:A.init,ColorPickerHide:A.hidePicker,ColorPickerShow:A.showPicker,ColorPickerSetColor:A.setColor,ColorPickerGetColor:A.getColor})})(jQuery);