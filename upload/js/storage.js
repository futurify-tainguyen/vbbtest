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
vBulletin=vBulletin||{};vBulletin.createVBArrayStore=function(D){var B={};B.cache={};B.prefix=D;var C=function(F){if(!B.cache[F]){B.cache[F]={};try{var E=localStorage.getItem(B.prefix+F);if(E){B.cache[F]=JSON.parse(E)}}catch(G){}}},A=function(E){try{localStorage.setItem(B.prefix+E,JSON.stringify(B.cache[E]))}catch(F){}};B.get=function(E,F){C(E);return(B.cache[E][F]?B.cache[E][F]:null)};B.getAll=function(E){C(E);return B.cache[E]};B.set=function(E,F,G){C(E);B.cache[E][F]=G;A(E)};B.unset=function(E,F){C(E);delete B.cache[E][F];A(E)};return B};