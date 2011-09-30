<?php
/*
Plugin Name: Google+ One Button
Plugin URI: http://failen.com/developments/wordpress-googleplus-one-button
Description: Plugin for adding the "Google+ One"-Button to pages and posts. The layout of the button, can be configured in the backend.
Author: Alex Sawallich
Version: 1.0
Author URI: http://failen.com
*/

// Include needed files
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . WPINC . '/pluggable.php');

// Lookup-Arrays for possible values
$gpo_sizes = array(
    'small' => 'Small (15px)',
    'medium' => 'Medium (20px)',
    'standard' => 'Standard (24px)',
    'tall' => 'Tall (60px)'
);

$gpo_annotations = array(
    'inline' => 'Inline',
    'bubble' => 'Bubble',
    'none' => 'None'
);

$gpo_languages = array(
    'ar' => 'Arabic - العربية',
    'bg' => 'Bulgarian - български',
    'ca' => 'Catalan - català',
    'zh-CN' => 'Chinese (Simplified) - 中文 &rlm;（簡体）',
    'zh-TW' => 'Chinese (Traditional) - 中文 &rlm;（繁體）',
    'hr' => 'Croatian - hrvatski',
    'cs' => 'Czech - čeština',
    'da' => 'Danish - dansk',
    'nl' => 'Dutch - Nederlands',
    'en-US' => 'English (US) - English &rlm;(US)',
    'en-GB' => 'English (UK) - English &rlm;(UK)',
    'et' => 'Estonian - eesti',
    'fil' => 'Filipino - Filipino',
    'fi' => 'Finnish - suomi',
    'fr' => 'French - français',
    'de' => 'German - Deutsch',
    'el' => 'Greek - Ελληνικά',
    'iw' => 'Hebrew - עברית',
    'hi' => 'Hindi - हिन्दी',
    'hu' => 'Hungarian - magyar',
    'id' => 'Indonesian - Bahasa Indonesia',
    'it' => 'Italian - italiano',
    'ja' => 'Japanese - 日本語',
    'ko' => 'Korean - 한국어',
    'lv' => 'Latvian - latviešu',
    'lt' => 'Lithuanian - lietuvių',
    'ms' => 'Malay - Bahasa Melayu',
    'no' => 'Norwegian - norsk',
    'fa' => 'Persian - فارسی',
    'pl' => 'Polish - polski',
    'pt-BR' => 'Portuguese (Brazil) - português &rlm;(Brasil)',
    'pt-PT' => 'Portuguese (Portugal) - Português &rlm;(Portugal)',
    'ro' => 'Romanian - română',
    'ru' => 'Russian - русский',
    'sr' => 'Serbian - српски',
    'sv' => 'Swedish - svenska',
    'sk' => 'Slovak - slovenský',
    'sl' => 'Slovenian - slovenščina',
    'es' => 'Spanish - español',
    'es-419' => 'Spanish (Latin America) - español &rlm;(Latinoamérica y el Caribe)',
    'th' => 'Thai - ไทย',
    'tr' => 'Turkish - Türkçe',
    'uk' => 'Ukrainian - українська',
    'vi' => 'Vietnamese - Tiếng Việt'
);

// Append the necessary google xml to the content
function hook_posts($content)
{
    global $post;
    
    // Get options
    $gpo_size = get_option('gpo_size');
    $gpo_annotation = get_option('gpo_annotation');
    $gpo_width = get_option('gpo_width');
    
    $postId = $post->ID;
    $permalink = urlencode(get_permalink($postId));
    
    // Generate string
    $googleplus = "<g:plusone href=\"$permalink\" annotation=\"$gpo_annotation\" width=\"$gpo_width\" size=\"$gpo_size\"></g:plusone>";
    
    // Append string to content
    return $content . $googleplus;
}

// Put necessary js-code from google in <head>-element
function hook_googleplusscript($content)
{
    // Get options
    $gpo_language = get_option('gpo_language');
    
    // Generate js
    $script = '
    <script type="text/javascript">
      window.___gcfg = {lang: \'' . $gpo_language . '\'};
    
      (function() {
        var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
        po.src = \'https://apis.google.com/js/plusone.js\';
        var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
      })();
    </script>';
    
    // Output js
    echo $script;
}

// Settings-page in the admin-backend
function googleplusadmin()
{
    global $gpo_sizes, $gpo_annotations, $gpo_languages;
    
    // Get Values
    $gpo_size = (true === isset($_POST['gpo_size']))?$_POST['gpo_size']:get_option('gpo_size');
    $gpo_annotation = (true === isset($_POST['gpo_annotation']))?$_POST['gpo_annotation']:get_option('gpo_annotation');
	$gpo_width = (true === isset($_POST['gpo_width']))?$_POST['gpo_width']:get_option('gpo_width');
    $gpo_language = (true === isset($_POST['gpo_language']))?$_POST['gpo_language']:get_option('gpo_language');
    
    // Message
    $message = '';
    
    // Check if the form was submitted
    if(true === isset($_POST['submit']))
    {
        $error = false;
        
        // Validate input
        if(false === isset($gpo_sizes[$gpo_size]))
        {
            // Error invalid size
            $message .= '<div id="message" class="error below-h2"><p>Invalid size chosen.</p></div>';
            $error = true;
        }
        
        if(false === isset($gpo_annotations[$gpo_annotation]))
        {
            // Error invalid annotation
            $message .= '<div id="message" class="error below-h2"><p>Invalid annotation chosen.</p></div>';
            $error = true;
        }
        
        if(preg_match('#\D#', $gpo_width))
        {
            // Error size must be a number
            $message .= '<div id="message" class="form-invalid error below-h2"><p>Width must be a number.</p></div>';
            $error = true;
        }
        else if((int)$gpo_width < 120)
        {
            // Error minimum size
            $message .= '<div id="message" class="error below-h2"><p>Minimum width is a value of 120.</p></div>';
            $error = true;
        }
        
        
        
        if(false === isset($gpo_languages[$gpo_language]))
        {
            // Error invalid language
            $message = '<div id="message" class="error below-h2"><p>Invalid language chosen.</p></div>';
            $error = true;
        }
        
        // If no errors occured, save in database
        if(false === $error)
        {
            update_option('gpo_size', $gpo_size);
            update_option('gpo_annotation', $gpo_annotation);
            update_option('gpo_width', $gpo_width);
            update_option('gpo_language', $gpo_language);
            
            $message = '<div id="message" class="updated below-h2"><p>Options saved.</p></div>';
        }
    }
    
    // Output the formular with the current values
    echo '
	<div class="wrap">
		<h2>Google+ One Button</h2>
		' . $message . '
		<form action="' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . '" method="post">
			<table class="form-table">
				<tr valign="top">
    				<th><label>Size</label></th>
    				<td>
            			<select name="gpo_size">';

    foreach($gpo_sizes AS $key => $value)
    {
        if($gpo_size === $key)
            echo '<option value="' . $key . '" selected="selected">' . $value . '</option>';
        else
            echo '<option value="' . $key . '">' . $value . '</option>';
    }
    
    echo '
            			</select>
    				</td>
    			</tr>
    			<tr valign="top">
    				<th><label>Annotation</label></th>
    				<td>
    					<select name="gpo_annotation">';

    foreach($gpo_annotations AS $key => $value)
    {
        if($gpo_annotation === $key)
            echo '<option value="' . $key . '" selected="selected">' . $value . '</option>';
        else
            echo '<option value="' . $key . '">' . $value . '</option>';
    }
    
    echo '
    					</select>
    				</td>
    			</tr>
    			<tr valign="top">
    				<th><label>Width</label></th>
    				<td>
    					<input type="text" name="gpo_width" value="' . $gpo_width . '" />
    				</td>
    			</tr>
    			<tr valign="top">
    				<th><label>Language</label></th>
    				<td>
    					<select name="gpo_language">';

    foreach($gpo_languages AS $key => $value)
    {
        if($gpo_language === $key)
            echo '<option value="' . $key . '" selected="selected">' . $value . '</option>';
        else
            echo '<option value="' . $key . '">' . $value . '</option>';
    }

    echo '    						
    					</select>
    				</td>
    			</tr>
			</table>
			<p>
				<input class="button-primary" type="submit" name="submit" value="Save" />
			</p>
		</form>
	</div>';
}

// Hooking
add_options_page('Google+ One Settings', 'Google+ One', 8, 'googleplus', 'googleplusadmin');
add_action('wp_head', 'hook_googleplusscript');
add_filter('the_content', 'hook_posts');