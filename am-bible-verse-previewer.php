<?php

/**
 * Plugin Name:       Bible Verse Previewer
 * Description:       Shows the bible verse when clicking the verse
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Author:            Amir Latif
 * Author URI:        https://amir-latif.github.io/portfolio
 * Text Domain:       ambvp-bible-verse-previewer
 */

#region data class initialization
require_once("services/ambvpData.php");
$ambvpData = new AmbvpData(
    $french = json_decode(file_get_contents(plugin_dir_url(__FILE__) . "data/frenchText.json"), true),
    $russian = json_decode(file_get_contents(plugin_dir_url(__FILE__) . "data/russianText.json"), true),
    $persian = json_decode(file_get_contents(plugin_dir_url(__FILE__) . "data/persianText.json"), true),
    $tamil = json_decode(file_get_contents(plugin_dir_url(__FILE__) . "data/tamilText.json"), true),

);
#endregion

#region Require the Classic Editor plugin
function ambvp_child_plugin_notice()
{
?>
    <div class="error">
        <p>Sorry, but the plugin <b>"<u>Bible Verse Viewer"</u></b> requires the <a href='<?php site_url() ?>/wp-admin/plugin-install.php?s=classic%2520editor&tab=search&type=term'>Classic Editor Plugin</a> created by 'WordPress Contributors' to be installed and active.</p>
    </div>
<?php
}

function ambvp_require_classic_editor_Plugin()
{
    if (is_admin() && current_user_can('activate_plugins') &&  !is_plugin_active('classic-editor/classic-editor.php')) {
        add_action('admin_notices', 'ambvp_child_plugin_notice');

        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

add_action('admin_init', 'ambvp_require_classic_editor_Plugin');
#endregion Require the Classic Editor plugin

#region Add the format button option to the classic editor
function ambvp_add_classic_editor_styles_btn($buttons)
{
    array_unshift($buttons, 'styleselect');
    return $buttons;
}
add_filter('mce_buttons_2', 'ambvp_add_classic_editor_styles_btn');
#endregion Add the format button option to the classic editor

#region Add the custom styles
function ambvp_before_init_insert_formats($init_array)
{
    // The new styles
    $new_style_format = [[
        'title' => 'Verse Reference',
        'inline' => 'span',
        'classes' => 'ambvp-verse',
        'styles' => ['color' => 'blue'],
        'wrapper' => true,
    ]];

    // Add the new styles as sub-category
    if (get_option('tcs_submenu') === "1") {
        $mainmenu = [['title' => 'Bible Custom Styles', 'items' => $new_style_format]];
        $new_style_format = $mainmenu;
    }

    $init_array['style_formats'] = json_encode($new_style_format);
    $init_array['style_formats_merge'] = true;

    return $init_array;
}
add_filter('tiny_mce_before_init', 'ambvp_before_init_insert_formats');
#endregion Add the custom styles

#region modify the already created posts
function ambvp_add_style_to_verse($content)
{
    global $ambvpData;

    $french_books = implode("|", array_values($ambvpData->frenchDict));
    $russian_books = implode("|", array_values($ambvpData->russianDict));
    $persian_books = implode("|", array_values($ambvpData->persianDict));
    $tamil_books = implode("|", array_values($ambvpData->tamilDict));
    $all_books = "{$french_books}|{$russian_books}|{$persian_books}|{$tamil_books}";

    $persian_numbers = "۰۱۲۳۴۵۶۷۸۹";
    $number = "[\d$persian_numbers]";

    $verseRange = "\s?(–|-|(&#8211;))\s?$number$number?\s?";
    
    $regex = "/\\(?\s?($all_books)\s?$number$number?\s?:\s?$number$number?($verseRange)?(\s?,\s?$number$number?$verseRange)?\\)?/";
    $matches = [];
    preg_match_all($regex, $content, $matches);

    foreach ($matches[0] as $match) {
        $content = str_replace($match, "<span class='ambvp-verse'>{$match}</span>", $content);
    }
    return $content;
};
add_filter("the_content", "ambvp_add_style_to_verse");
#endregion modify the already created posts

#region Add custom styles and scripts
function ambvp_add_scripts()
{
    wp_enqueue_style("ambvpCss", plugin_dir_url(__FILE__) . "styles/ambvp-global.css", null, time());
    wp_enqueue_script("ambvpJs", plugin_dir_url(__FILE__) . "scripts/ambvp-global.js", null, time(), true);
    wp_localize_script("ambvpJs", "ambvpObject", [
        // "bookText" => json_decode(file_get_contents(plugin_dir_url(__FILE__) . "data/bibleText.json"), true),
        // "bookDictionary" => ambvpBooksDictionary
    ]);
}
add_action('wp_enqueue_scripts', 'ambvp_add_scripts');
#endregion Add custom styles and scripts
