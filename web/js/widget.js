function addCSS(e){var n=document.getElementsByTagName("head")[0],l=document.createElement("link");l.type="text/css",l.rel="stylesheet",l.href=e,l.media="screen",n.appendChild(l)}function el(e){return document.getElementById(e)}function elcl(e,n){n||(n=document);for(var l,t=n.getElementsByTagName("*"),i=-1,a=[];l=t[++i];)(" "+(l.class||l.className)+" ").indexOf(" "+e+" ")>-1&&a.push(l);return a}var host="http://localhost:1234";!function(){addCSS(host+"/css/widget-overlay.css");var e=el("tuneefy_overlay");if(e)n=e;else{var n=document.createElement("div");n.id="tuneefy_overlay"}var l='<div class="closeButton" onClick="document.body.removeChild(document.getElementById(\'tuneefy_overlay\'));"></div>',t='<img src="'+host+'/img/ajax-loader-widget.gif" class="middleBox"/>';n.innerHTML=l+t,n.style.opacity=1;var i=encodeURIComponent(decodeURIComponent(document.location.href)),a=null,r=null,d="";try{-1!=i.indexOf(".deezer.")&&null!=el("player_track_title")?(r=el("player_track_artist").innerHTML,a=el("player_track_title").innerHTML):-1!=i.indexOf(".deezer.")&&null!=elcl("player-track-title")?(r=elcl("player-track-artist")[0].childNodes[1].innerHTML,a=elcl("player-track-title")[0].firstChild.innerHTML):-1!=i.indexOf(".grooveshark.")&&null!=el("now-playing-metadata")?(r=el("now-playing-metadata").childNodes[4].innerHTML,a=el("now-playing-metadata").firstChild.innerHTML):-1!=i.indexOf(".radionomy.")&&null!=el("track-name")?(r=el("artist-name").innerHTML,a=el("track-name").innerHTML):-1!=i.indexOf(".stereomood.")&&null!=el("info_track_title")?(r=el("info_track_artist").innerHTML,a=el("info_track_title").innerHTML):-1!=i.indexOf(".musicmaze.")&&null!=el("song-title")?(r=el("artist-name").firstChild.innerHTML,a=el("song-title").firstChild.innerHTML):-1!=i.indexOf(".myspace.com/music/player")&&null!=el("mainContent")?(r=el("mainContent").childNodes[3].childNodes[11].childNodes[1].childNodes[5].firstChild.innerHTML,a=el("mainContent").childNodes[3].childNodes[11].childNodes[1].childNodes[3].firstChild.innerHTML):-1!=i.indexOf(".myspace.")&&null!=document.getElementsByTagName("h1")[1]?(r=document.getElementsByTagName("h1")[1].firstChild.innerHTML,a=document.getElementsByTagName("h6")[0].firstChild.firstChild.innerHTML):-1!=i.indexOf("player.qobuz.")&&null!=el("now-playing")?i=encodeURIComponent(el("now-playing").childNodes[2].childNodes[3].childNodes[0].href):-1!=i.indexOf("music.xbox.")&&null!=el("player")?(a=$("#player").find(".playerNowPlaying .playerNowPlayingMetadata .primaryMetadata a").html(),r=$("#player").find(".playerNowPlaying .playerNowPlayingMetadata .secondaryMetadata a:first-child").html()):-1!=i.indexOf("radiooooo.")&&null!=elcl("songinfo--box")[0]&&(r=elcl("song__artist")[0].innerHTML,a=elcl("song__title")[0].innerHTML)}catch(e){r=null,a=null}null!=r&&void 0!==r&&(d+=r+"+"),null!=a&&void 0!==a&&(d+=a),d=""==d?i:encodeURIComponent(d);var o='<iframe border="none" allowtransparency="true" class="tuneefyResults" scrolling="no" src="'+host+"/?q="+d+'&widget=42"></iframe>';n.innerHTML=l+o,document.body.appendChild(n)}();