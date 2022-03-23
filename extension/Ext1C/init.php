<?php
function itgaziev_yml_1c_plugins()
{
        if (!is_dir(WOO1C_DATA_DIR)) mkdir(WOO1C_DATA_DIR);
        file_put_contents(WOO1C_DATA_DIR . ".htaccess", "Deny from all");
        file_put_contents(WOO1C_DATA_DIR . "index.html", '');

        itgaizev_yml_add_rewrite_rules();
        flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
/**
 * Rule Rewrite for 1C
 */
function itgaziev_yml_add_rewrite_rules() {
    add_rewrite_rule("itgaziev/exchange", "index.php?itgaziev=exchange", 'top');
    add_rewrite_rule("itgaziev/clean", "index.php?itgaziev=clean");
}