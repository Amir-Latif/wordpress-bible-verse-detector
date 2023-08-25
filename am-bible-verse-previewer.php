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
        <p>Sorry, but the plugin <b>"<u>Bible Verse Viewer"</u></b> requires the <a href='<?php echo site_url() ?>/wp-admin/plugin-install.php?s=Classic%2520Editor&tab=search&type=term'>Classic Editor Plugin</a> created by 'WordPress Contributors' to be installed and active.</p>
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

#region modify the already created posts
function ambvp_add_style_to_verse($content)
{
    $content = str_replace(chr(194) . chr(160), ' ', $content);

    global $ambvpData;

    $french_books_reg = implode("|", array_values($ambvpData->french_books)) . "|Psaume";
    $russian_books_reg = implode("|", array_values($ambvpData->russian_books)) . "|Деяния|Исаией|Исаии";
    $persian_books_reg = implode("|", array_values($ambvpData->persian_books)) . "|اعمال|اشعیا|مزامیر";
    $tamil_books_reg = implode("|", array_values($ambvpData->tamil_books));
    $all_books = "{$french_books_reg}|{$russian_books_reg}|{$persian_books_reg}|{$tamil_books_reg}";
    $persian_numbers = implode("|", array_keys($ambvpData->persian_numbers_dict));
    $number = "(\d|$persian_numbers)";
    $space = "(\s|(&nbsp;)|(\xC2\xA0))";
    $verseRange /*After the chapter and verse start*/ = "($space?(–|-|(&#8211;))$space?$number{1,2}$space?:?$number{0,2}$space?)";
    $verse = "$number{1,3}$space?:$space?$number{1,2}$verseRange?";
    $and = "et|و|и|மற்றும்";
    $regex = "/($all_books)$space?$verse($space?(,|;|،|$and)$space?$verse)*/i";
    preg_match_all($regex, $content, $matches, PREG_OFFSET_CAPTURE);

    $matches = $matches[0];

    $new_content = substr(
        $content,
        0,
        $matches[0][1]
    );

    for ($i = 0; $i < sizeof($matches); $i++) {
        // detect the language
        $language = "french";
        switch (1) {
            case preg_match("/($russian_books_reg)/", $matches[$i][0]):
                global $language;
                $language = "russian";
                break;
            case preg_match("/($persian_books_reg)/", $matches[$i][0]):
                global $language;
                $language = "persian";
                break;
            case preg_match("/($tamil_books_reg)/", $matches[$i][0]):
                global $language;
                $language = "tamil";
                break;

            default:
                break;
        }

        $new_content .= "<span class='ambvp-verse' data-lang='{$language}'>{$matches[$i][0]}</span>";

        if ($i === array_key_last($matches))
            $new_content .= substr(
                $content,
                $matches[$i][1] + strlen($matches[$i][0])
            );
        else
            $new_content .= substr(
                $content,
                $matches[$i][1] + strlen($matches[$i][0]),
                $matches[$i + 1][1] - $matches[$i][1] - strlen($matches[$i][0])
            );
    }

    return $new_content;
};
add_filter("the_content", "ambvp_add_style_to_verse");
#endregion modify the already created posts

#region Add custom styles and scripts
function ambvp_add_scripts()
{
    global $ambvpData;
    if (is_singular()) {
        wp_enqueue_style("ambvpCss", plugin_dir_url(__FILE__) . "styles/ambvp-global.css", null, time());
        wp_enqueue_script("ambvpJs", plugin_dir_url(__FILE__) . "scripts/ambvp-global.js", null, time(), true);
        wp_localize_script("ambvpJs", "ambvpObject", [
            "frenchBooks" => $ambvpData->french_books,
            "russianBooks" => $ambvpData->russian_books,
            "persianBooks" => $ambvpData->persian_books,
            "tamilBooks" => $ambvpData->tamil_books,
            "textUrl" => plugin_dir_url(__FILE__) . "data",
            "persianNumbersDict" => $ambvpData->persian_numbers_dict
        ]);
    }
}
add_action('wp_enqueue_scripts', 'ambvp_add_scripts');
#endregion Add custom styles and scripts
