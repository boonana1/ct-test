<?php

/**
 * Plugin Name
 *
 * @package           test_lopatin
 * @author            Dmitry Lopatin
 * @copyright         2023 BN
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Test Lopatin
 * Plugin URI:        http://wordpress.org/
 * Description:       Plugin to add an extra field with user IP in comment.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Dmitry Lopatin
 * Author URI:        https://lopatinbn.ru/
 * Text Domain:       plugin-slug
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        http://wordpress.org/
 */

/**
 * Function to add a hidden custom field to the comment form.
 */
function add_ctl_custom_field()
{
    echo '<input type="hidden" name="ctl_custom_field" id="ctl_custom_field" value="" />';
}

/**
 * Assign callback function to comment form for all users
 */
add_action('comment_form', 'add_ctl_custom_field');

/**
 *  Function to add a script to the footer
 */
function add_ctl_custom_field_script()
{
?>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var customField = document.getElementById("ctl_custom_field");
            if (customField) {
                // Assign the visitor's IP address to the hidden field
                customField.value = "<?php echo get_visitor_ip(); ?>";
            }
        });
    </script>
<?php
}

/**
 * Assign callback function to the footer
 */
add_action('wp_footer', 'add_ctl_custom_field_script');

/**
 * Function to check if the hidden field value matches the visitor's IP address before submitting the comment.
 *
 * @param array $commentdata The comment data.
 * @return array The modified comment data.
 */
function check_custom_comment_field($commentdata)
{
    $visitor_ip = get_visitor_ip();

    // Get value of the hidden field from comment form
    $ctl_custom_field_value = sanitize_text_field($_POST['ctl_custom_field']);

    // If value of the hidden field does not match the IP address of the visitor, deny comment
    if ($ctl_custom_field_value !== $visitor_ip) {
        wp_die("Error: the current IP address doesn't match the user's IP address. Comment publication is denied.");
    }

    return $commentdata;
}

/**
 * Assign callback function to the filter hook
 */
add_filter('preprocess_comment', 'check_custom_comment_field');

/**
 * Function to get the IP address of the visitor.
 *
 * @return string The visitor's IP address.
 */
function get_visitor_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //check ip from share internet
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //check ip is pass from proxy
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}
