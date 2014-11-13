<?php /** blueticket_forms wp plugin v.0.6 */
/*
Plugin Name: blueticket_forms loader
Version: 0.1
*/
function exec_blueticket_forms($matches)
{
    try
    {
        eval('ob_start();' . $matches[1] . '$output = ob_get_contents();ob_end_clean();');
    }
    catch (exception $e)
    {
        return 'executing error';
    }
    return $output;
}
function prepare_blueticket_forms_code($matches){
    return html_entity_decode($matches[1]);
}

function blueticket_forms_wp($content)
{   
    $content = preg_replace_callback('/(<blueticket_forms>.*<\/blueticket_forms>)/Umus', 'prepare_blueticket_forms_code', $content);
    $content = preg_replace_callback('/(\[blueticket_forms\].*\[\/blueticket_forms\])/Umus', 'prepare_blueticket_forms_code', $content);
    $content = preg_replace_callback('/<blueticket_forms>(.*)<\/blueticket_forms>/Umus', 'exec_blueticket_forms', $content);
    $content = preg_replace_callback('/\[blueticket_forms\](.*)\[\/blueticket_forms\]/Umus', 'exec_blueticket_forms', $content);
    return $content;
}
function load_blueticket_forms()
{
    require_once (dirname(__file__) . '/forms/blueticket_forms.php');
    if (!session_id())
    { 
        if (!headers_sent())
        {
            session_name(blueticket_forms_config::$sess_name);
            session_cache_expire(blueticket_forms_config::$sess_expire);
            session_start();
        }
    }
    blueticket_forms_config::$manual_load = true;
}
function blueticket_forms_load_css() {
    echo blueticket_forms::load_css();
}
function blueticket_forms_load_js() {
    echo blueticket_forms::load_js();
}
add_filter('plugins_loaded', 'load_blueticket_forms', 0);
remove_filter( 'the_content', 'wpautop');
add_filter('the_content', 'blueticket_forms_wp', -1000);
add_filter('wp_head', 'blueticket_forms_load_css',1000);
add_filter('wp_footer', 'blueticket_forms_load_js',1000);
