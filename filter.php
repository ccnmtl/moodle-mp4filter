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
        $search = '#\[mp4\](http://.*?\.mp4)\[mp4\]#i';

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

    $width  = empty($link[3]) ? '480' : $link[3];
    $height = empty($link[4]) ? '360' : $link[4];
    $url = addslashes_js($link[1]);
    // our contract is that urls will always have accompanying images 
    $imageurl = str_replace('.mp4', '.jpg', $url);
    $qturl    = str_replace('.mp4', '.qtl', $url);

    return '<center>
<!-- Flash Embed -->
<script type="text/javascript" src="'.$CFG->wwwroot.'/filter/mp4filter/swfobject.js"></script> 
<div class="mediaplugin mediaplugin_mp4" id="'.$id.'">
<!-- fallback message -->
<img height="360" width="640" src="'.$imageurl.'" />
<!-- you *must* offer a download link as they may be able to play the file locally -->
      <p> <strong>No video playback capabilities detected.</strong> Why not try to download the file instead?<br /> 
	  <a href=".$url.">MPEG4 / H.264 - (Windows / Mac compatible)</a> | </p>
		To play the video here in the webpage, please do one of the following: </p>
<ul>	
       <li>Upgrade to <a href="http://getfirefox.com">Firefox v3.5</a>, or <a href="http://apple.com/safari">Safari v4</a></li>
					 <li>Install <a href="http://get.adobe.com/flashplayer/">Adobe Flash Player</a></li>
</ul
</div>
<script type="text/javascript">
//<![CDATA[
    var so = new SWFObject("'.$CFG->wwwroot.'/filter/mp4filter/player.swf","mpl","640","360","9");
    so.addParam("allowfullscreen","true");
    so.addParam("allowscriptaccess","always");
    so.addParam("wmode","opaque");
    so.addVariable("file","'.$url.'");
    so.addVariable("image","'.$imageurl.'");
    so.write("'.$id.'");
//]]>
</script>
<!-- alternate links -->
<p>
    <a href="'.$qturl.'">Streaming version of this lecture</a> is also available for viewing. (Opens in <a href="http://www.apple.com/quicktime/download/">QuickTime Player</a> - ver. 7.6 or under required)
<br />
		       																		Or, try to download the file: <a href="'.$url.'">MPEG4 / H.264 - (Windows / Mac compatible)</a> (Control-click and "Save As...")</p>
</ul>
</p>

</center>';

}
?>