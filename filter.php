<?php 
//////////////////////////////////////////////////////////////
//  MP4 plugin filtering
//
//  This filter will replace any links to a mp4 or m4v file with
//  a media plugin that plays that media inline
//
//  To activate this filter, add a line like this to your
//  list of filters in your Filter configuration:
//
//  filter/mp4plugin/filter.php
//
//////////////////////////////////////////////////////////////

/// This is the filtering function itself.  It accepts the
/// courseid and the text to be filtered (in HTML form).

require_once($CFG->libdir.'/filelib.php');


function mp4filter_filter($courseid, $text) {
    global $CFG;

    if (!is_string($text)) {
        // non string data can not be filtered anyway
        return $text;
    }
    $newtext = $text; // fullclone is slow and not needed here

        //Filter for M4V
        //$search = '/<a.*?href="([^<]+\.m4v)(\?d=([\d]{1,4}%?)x([\d]{1,4}%?))?"[^>]*>.*?<\/a>/is';
        //$newtext = preg_replace_callback($search, 'mediaplugin_filter_mp4_callback', $newtext);

        //Filter for MP4
        //$search = '/<a.*?href="([^<]+\.mp4)(\?d=([\d]{1,4}%?)x([\d]{1,4}%?))?"[^>]*>.*?<\/a>/is';
        //$search = '/<a.*?href="([^<]+\.mp4)"[^>]*>.*?<\/a>/is';
        //$search = '#\[mp4\](http://.*?\.mp4)\[mp4\]#i';
	$search = '#\[mp4\](http://.*?\.mp4)[\[w\]]*(\d*)[\[h\]]*(\d*)\[mp4\]#i';

        $newtext = preg_replace_callback($search, 'mediaplugin_filter_mp4_callback', $newtext);
	
    return $newtext;
}

///===========================
/// callback filter functions

function mediaplugin_filter_mp4_callback($link) {
    global $CFG;

    static $count = 0;
    $count++;
    $id = 'filter_mp4_'.time().$count; //we need something unique because it might be stored in text cache

    $width  = empty($link[2]) ? '480' : $link[2];
    $height = empty($link[3]) ? '272' : $link[3];

    // controller (toolbar) height
    $height = intval($height) + 25;

    $url = addslashes_js($link[1]);
    // our contract is that urls will always have accompanying images 
    $imageurl = str_replace('.mp4', '.jpg', $url);
    $qturl    = str_replace('.mp4', '.qtl', $url);

    $script = '
<center>
<script type="text/javascript" src="http://ccnmtl.columbia.edu/remote/flowplayer-3.2.2/flowplayer-3.2.2.%s"></script>
<div id="%s" class="mediaplugin mediaplugin_mp4 ccnmtl-embedded-player" style="width:%spx;height:%spx;background-color:white;">
<img src="'.$imageurl.'" />
</div>
<script type="text/javascript">
function init_ccnmtl_flowplayer_%s() {
  var id = "%s";
  var provider = "%s";
  var poster = "%s";
  var public_url = "%s";
  var autostart = ("%s"=="true");
  var width = %d;
  var height = %d;

  document.getElementById(id).innerHTML = "";
  var flash_enabled = flashembed.isSupported([9]);
  if ( ! flash_enabled) {
    var vidTag = document.createElement("video");
    if (provider == "mp4") {
        if (vidTag.canPlayType && vidTag.canPlayType("video/mp4")) {
           vidTag.src = public_url;
           vidTag.poster = poster;
           if (autostart) {
              vidTag.autoplay = "autoplay";
           }
           vidTag.controls = "controls";
           vidTag.width = width;
           vidTag.height = height;
           document.getElementById(id).appendChild(vidTag);
        } else {
           document.getElementById(id).innerHTML = \'<a style="text-align:center;text-decoration:none;vertical-align:middle;display:block;width:\'+width+\'px;height:\'+height+\'px;background-color:black;background-repeat:no-repeat;background-image:url(\'+poster+\')" href="\'+public_url+\'"><div style="background-color:transparent;color:transparent;text-decoration:none;height:100%%;width:100%%;background-position:center center;background-repeat:no-repeat;font-weight:bold;background-image:url(http://ccnmtl.columbia.edu/broadcast/images/prompt_320x240_download.png)">No browser playback capabilities were detected. Click to download the video.</div></a>\';
        }
    } else { /*provider=flv but no flash*/
           document.getElementById(id).innerHTML = \'<span style="text-align:center;text-decoration:none;vertical-align:middle;display:block;width:\'+width+\'px;height:\'+height+\'px;background-color:black;background-repeat:no-repeat;background-image:url(\'+poster+\')"><div style="font-family:Helvetica, Arial, sans-serif;background-color:transparent;color:white;text-decoration:none;height:100%%;width:100%%;background-position:center center;background-repeat:no-repeat;font-weight:bold;background-image:url(http://ccnmtl.columbia.edu/broadcast/images/prompt_320x240_download.png)">Adobe Flash version 9 or above is required to view this video.  Please visit <a href="http://get.adobe.com/flashplayer/">Adobe</a> to update your Flash plugin: <a target="_blank" href="http://get.adobe.com/flashplayer/">http://get.adobe.com/flashplayer/</a>.</div></span>\';

    }
  } else {
ccnmtl_flowplayer("%s",{
  clip:{
    scaling:"fit"
  },
  canvas:{
    backgroundColor: "#519ab8", backgroundGradient: [0,.7]
  },  
  plugins:{
    %s
    content:{
      url:"flowplayer.content-3.2.0.swf",
      bottom:45,height:45,backgroundColor:"transparent",backgroundGradient:"none",border:0,textDecoration:"outline",style:{body:{fontSize:14,fontFamily:"Helvetica,Arial",textAlign:"center",color:"#ffffff"}}
    },
    controls:{
      autoHide:false,
      backgroundColor: "#aaaaaa",backgroundGradient: "medium",borderRadius: "0px",bufferColor: "#222222",bufferGradient: "none",buttonColor: "#666666", buttonOverColor: "#ffffff",durationColor: "#666666",enabled: "true",hideDelay:null,hideDuration:null,hideStyle:null,mouseOutDelay:null,fullscreenOnly: "false",height: 24,opacity: 1.0,progressColor: "#777777",progressGradient: "none",scrubberBarHeightRatio: .5,scrubberHeightRatio: .6,sliderBorder: "none",sliderBorderColor: "#ffffff",sliderBorderWidth: 0,sliderColor: "#000000",sliderGradient: [0,.2],timeBgColor:null,timeBorder:"none",timeBorderWidth:0,timeBgHeightRatio: 0.7,timeColor: "#000000",timeSeparator:" &#8594; ",timeFontSize: 12,tooltipColor: "#ffff99",tooltipTextColor: "#000000",volumeBarHeightRatio: 0.5,volumeBorder: "none",volumeBorderColor: "#ffffff",volumeBorderWidth: 0,volumeColor: "#777777",volumeSliderColor: "#000000",volumeSliderGradient: "none",volumeSliderHeightRatio: 0.6
    }
  },
  playlist:[ poster,
  {
    url:public_url,
    %s
    autoPlay:autostart
  }]
});
}
}

if ((!window.flashembed || !window.ccnmtl_flowplayer) && window.attachEvent) {
  window.attachEvent("onload",init_ccnmtl_flowplayer_%s);
} else {
  init_ccnmtl_flowplayer_%s();
};
</script>
<p>
  <a href="'.$qturl.'">Streaming version of this lecture</a>
  is also available for viewing. (Opens in 
  <a href="http://www.apple.com/quicktime/download/">QuickTime Player</a> - 
  ver. 7.6 or under required)
  <br />
  Or, try to download the file:
  <a href="'.$url.'">MPEG4 / H.264 - (Windows / Mac compatible)</a>
  (Right-click or Control-click and "Save / Download As...")</p>
</p>
</center>
';
    $js_file = "ccnmtl.js";
    $provider = "mp4";
    $autostart = "false";
    $caption_plugin = '';
    $flash_provider = (($provider=='flv')?' "provider":"pseudo", ':'');

    $script = sprintf($script,
		      $js_file,
		      $id, $width, $height,
		      $id,
		      $id, 
		      $provider,
		      $imageurl,
		      $url,
		      $autostart,
		      $width, $height,
		      $id, $caption_plugin, $flash_provider,
		      $id, $id);
    return $script;

}
?>
