<?php
/*
Plugin Name: WP-HistographAI
Description: Revive Any Year in History with OpenAI-powered Summaries on WordPress.
Version: 1.0
Author: createIT
Author URI: https://www.createit.com
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once( plugin_dir_path( __FILE__ ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php' );

define('HISTOGRAPHAI_YEAR_COUNT', 2); // Number of years to process in each scheduled event

define('HISTOGRAPHAI_YEAR_START', 1900); // Number of years to process in each scheduled event
define('HISTOGRAPHAI_YEAR_END', 2021); // Number of years to process in each scheduled event

define('GPT_PROMPT', 'List the most significant worldwide events in a table format for year');

// Possible values: 'daily', 'weekly', 'monthly'
define('HISTOGRAPHAI_RECURRENCE', 'daily');

// Define the hour and minute for the scheduled event. Adjust as needed.
// Possible values: HOUR: 0-23, MINUTE: 0-59
define('HISTOGRAPHAI_SCHEDULED_HOUR', 1);
define('HISTOGRAPHAI_SCHEDULED_MINUTE', 30);


function histographai_register_post_type() {
    $args = array(
        'public' => true,
        'label' => 'HistographAI Years',
        'supports' => array('title', 'editor', 'custom-fields')
    );
    register_post_type('histographai_year', $args);
}
add_action('init', 'histographai_register_post_type');


function histographai_form_shortcode() {
    // Query for all the years (titles of the posts) in the 'histographai_year' CPT
    $existing_years = get_posts(array(
        'post_type' => 'histographai_year',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'DESC',
        'fields' => 'ids'
    ));



    ob_start();
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let params = new URLSearchParams(window.location.search);
            let year = params.get("histo_year");

            if (year) {
                // Do something with the year value, e.g., pre-populate a form field:
                document.getElementById("year").value = year;
                submitHistographaiForm();
            }
        });
    </script>
    <div id="histographai-sharing-links" class="position-fixed bottom-0 start-0 p-3 bg-white border d-flex flex-column flex-md-row gap-2 d-none"></div>

    <div class="row justify-content-center">
        <div class="col-sm-10 col-12">
            <!-- Header Section -->
            <div class="header text-center my-1">
                <h1 class="h3">WP-HistographAI</h1>
                <p>Revive Any Year in History with OpenAI-powered Summaries on WordPress</p>
            </div>

            <div class="result-section">

                <form action="#" method="post" id="histographai-form" class="text-center">
                    <div id="openai-badge" class="d-inline-block mx-3 badge bg-primary">OpenAI API inside</div>
                    <p>Select a year and discover its history!</p>

                    <div class="row align-items-center mb-3">
                        <div class="col-auto text-nowrap"> <!-- Label with nowrap -->
                            <label for="year" class="form-label mb-0">Select a Year:</label>
                        </div>
                        <div class="col"> <!-- Select input takes the remaining space -->
                            <select name="year" id="year" class="form-control form-select" required>
                                <?php foreach ($existing_years as $post_id):
                                    $year = get_the_title($post_id);
                                    $post_date = get_post_field('post_date', $post_id);
                                    $date_diff = (current_time('timestamp') - strtotime($post_date)) / DAY_IN_SECONDS; // Difference in days
                                    $is_new = $date_diff <= 7; // Check if the post is less than 7 days old
                                    ?>
                                    <option value="<?php echo esc_attr($year); ?>">
                                        <?php echo esc_html($year); ?>
                                        <?php if ($is_new) echo ' ★'; // Using a star to denote new items ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-auto mt-2 mt-md-0"> <!-- Submit button: full width on mobile, auto width on desktop -->
                            <input type="submit" value="Get Summary" class="btn btn-success w-100 w-md-auto">
                        </div>
                    </div>
                </form>

                <div id="histographai-result" class="table-formatted"></div>
            </div>
        </div>
    </div>
    <!-- / row -->


    <?php
    return ob_get_clean();
}
add_shortcode('histographai_form', 'histographai_form_shortcode');





function generate_social_share_links($year) {
    $base_url = get_site_url(); // Base site URL
    $share_url = esc_url($base_url . "/?histo_year=" . $year); // The URL you want to share
    $share_text = "Check out the summary for the year " . $year . " on " . get_bloginfo('name') . "!"; // Custom message for sharing

    // URLs for sharing
    $facebook_url = "https://www.facebook.com/sharer/sharer.php?u=" . $share_url;
    $twitter_url = "https://twitter.com/intent/tweet?url=" . $share_url . "&text=" . urlencode($share_text);
    $linkedin_url = "https://www.linkedin.com/shareArticle?mini=true&url=" . $share_url . "&title=" . urlencode(get_bloginfo('name')) . "&summary=" . urlencode($share_text);

    return [
        'facebook' => $facebook_url,
        'twitter' => $twitter_url,
        'linkedin' => $linkedin_url
    ];
}



function histographai_enqueue_scripts() {
    wp_enqueue_script('histographai-ajax', plugins_url('ajax.js', __FILE__), array('jquery'), '1.0.0', true);

    // Pass ajax_url to script.js
    wp_localize_script('histographai-ajax', 'frontendajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

    wp_enqueue_script('histographai-typewriter', plugins_url('typewriter.min.js', __FILE__), array('jquery'), '1.0.0', true);

    wp_enqueue_style('histographai-styles', plugins_url('styles.css', __FILE__));


    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

    wp_enqueue_style(
        'bootstrap-css', // A unique handle for the stylesheet
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', // The source of the stylesheet
        array(), // An array of registered stylesheets this stylesheet depends on, if any
        '5.3.2', // Version number
        'all' // Media type. Can be 'all', 'print', 'screen', 'speech', etc.
    );



}
add_action('wp_enqueue_scripts', 'histographai_enqueue_scripts');

function add_bootstrap_sri( $html, $handle ) {
    if ( 'bootstrap-css' === $handle ) {
        $html = str_replace(
            "media='all'",
            "media='all' integrity='sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN' crossorigin='anonymous'",
            $html
        );
    }
    return $html;
}
add_filter( 'style_loader_tag', 'add_bootstrap_sri', 10, 2 );

function fetch_year_summary() {
    $year = isset($_POST['year']) ? intval($_POST['year']) : null;
    $parsedown = new Parsedown();
    $summary = '';

    if ($year) {
        $existing_posts = get_posts(array(
            'post_type' => 'histographai_year',
            'title' => $year
        ));

        if (!empty($existing_posts)) {
            $markdown_content = $existing_posts[0]->post_content;
            $summary = $parsedown->text($markdown_content);
        } else {
            $summary = "No summary found for year $year.";
        }
    }

    echo $summary;
    wp_die(); // This is required to terminate immediately and return a proper response.
}
add_action('wp_ajax_fetch_year_summary', 'fetch_year_summary'); // If user is logged in
add_action('wp_ajax_nopriv_fetch_year_summary', 'fetch_year_summary'); // If user is not logged in



function histographai_get_summary($year) {
    $yourApiKey = get_option('openai_api_key');

    if(empty($yourApiKey)){
        wp_die( "Error: OpenAI API key is not set in the WP-HistographAI settings." );
    }

    // Assuming you've included the necessary library for OpenAI.
    $client = OpenAI::client($yourApiKey);

    $final_prompt = GPT_PROMPT . ' ' . $year;

    try {
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo', // gpt-4
            'messages' => [
                ['role' => 'user', 'content' => $final_prompt],
            ],
            'max_tokens' => 3000,
            'temperature' => 1
        ]);

        $summary = '';

        foreach ($response->choices as $result) {
            if(isset($result->message->content)){
                $summary = $result->message->content;
            }
        }

        return $summary;

    } catch (Exception $e) {
        ct_debug_log( "Error fetching summary from OpenAI: " . $e->getMessage());
    }

    return false;
}

function ct_debug_log($obj){
    $debug = var_export($obj, true);
    error_log($debug);
}




function histographai_schedule_events() {
    if (!as_has_scheduled_action('histographai_daily_fetch')) {

        // Get all the years that have already been saved as histographai_year posts.
        $existing_years = get_posts(array(
            'post_type' => 'histographai_year',
            'numberposts' => -1,
            'fields' => 'ids'
        ));

    // Extract the post titles (years) manually
        $existing_years = array_map(function($post_id) {
            return intval(get_the_title($post_id));
        }, $existing_years);

        $years = [];
        while (count($years) < HISTOGRAPHAI_YEAR_COUNT) {
            $random_year = rand(HISTOGRAPHAI_YEAR_START, HISTOGRAPHAI_YEAR_END);

            // Only add the year if it doesn't exist in the histographai_year posts.
            if (!in_array($random_year, $existing_years)) {
                $years[] = $random_year;
                $existing_years[] = $random_year; // Add to existing_years to ensure no duplicates within this loop.
            }
        }

        $params = array(array('years' => $years));

        as_schedule_single_action(get_next_occurrence_timestamp(), 'histographai_daily_fetch', $params);
    }
}
add_action('wp', 'histographai_schedule_events');



/**
 * Get the timestamp for the next occurrence based on the specified recurrence interval.
 *
 * Possible values for HISTOGRAPHAI_RECURRENCE: 'daily', 'weekly', 'monthly'
 *
 * @return int Timestamp of the next occurrence.
 */
function get_next_occurrence_timestamp() {
    $current_time = current_time('timestamp');

    switch (HISTOGRAPHAI_RECURRENCE) {
        case 'daily':
            $time_string = 'tomorrow ' . HISTOGRAPHAI_SCHEDULED_HOUR . ':' . HISTOGRAPHAI_SCHEDULED_MINUTE . ' am';
            break;
        case 'weekly':
            // Get the next occurrence of the specified time, one week from now
            $time_string = '+1 week ' . HISTOGRAPHAI_SCHEDULED_HOUR . ':' . HISTOGRAPHAI_SCHEDULED_MINUTE . ' am';
            break;
        case 'monthly':
            // Get the next occurrence of the specified time, one month from now
            $time_string = '+1 month ' . HISTOGRAPHAI_SCHEDULED_HOUR . ':' . HISTOGRAPHAI_SCHEDULED_MINUTE . ' am';
            break;
        default:
            // Default to daily if the recurrence value is not recognized
            $time_string = '+1 year ' . HISTOGRAPHAI_SCHEDULED_HOUR . ':' . HISTOGRAPHAI_SCHEDULED_MINUTE . ' am';
            break;
    }

    $target_time = strtotime($time_string, $current_time);
    return $target_time;
}



function histographai_daily_fetch_callback($item) {

    if (!isset($item['years']) || !is_array($item['years'])) {
        die("years param empty");
    }

    $years = $item['years'];

    foreach ($years as $year) {
        // Generate a summary for the year
        $summary = histographai_get_summary($year);

        if(!$summary){
            continue;
        }

        // Check if a post for this year already exists
        $existing_posts = get_posts(array(
            'post_type' => 'histographai_year',
            'title' => $year
        ));

        if (count($existing_posts) == 0) {
            // Save the summary as a new post
            wp_insert_post(array(
                'post_title' => $year,
                'post_content' => $summary,
                'post_type' => 'histographai_year',
                'post_status' => 'publish'
            ));
        }
    }

}
add_action('histographai_daily_fetch', 'histographai_daily_fetch_callback');

/**
 * admin option page
 */


function histographai_admin_menu() {
    add_options_page(
        'WP-HistographAI Settings',
        'WP-HistographAI',
        'manage_options',
        'wp-histographai',
        'histographai_settings_page'
    );
}
add_action('admin_menu', 'histographai_admin_menu');


function histographai_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP-HistographAI Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('histographai_settings');
            do_settings_sections('wp-histographai');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function histographai_register_settings() {
    register_setting('histographai_settings', 'openai_api_key');

    add_settings_section(
        'histographai_api_settings',
        'API Settings',
        'histographai_api_settings_callback',
        'wp-histographai'
    );

    add_settings_field(
        'openai_api_key',
        'OpenAI API Key',
        'histographai_api_key_callback',
        'wp-histographai',
        'histographai_api_settings'
    );
}
add_action('admin_init', 'histographai_register_settings');


function histographai_api_settings_callback() {
    echo '<p>Enter your OpenAI API key below:</p>';
}

function histographai_api_key_callback() {
    $openai_api_key = get_option('openai_api_key');
    echo "<input type='text' name='openai_api_key' value='{$openai_api_key}' size='50'>";
}


/**
 * Hack to use ?histo_year using multisite homepage for shortcode content
 */

add_action( 'pre_get_posts', 'histographai_front_page_override' );
function histographai_front_page_override( $query ) {
    if ( ! is_admin() && $query->is_main_query() && get_query_var( 'histo_year' ) ) {
        $query->set( 'page_id', get_option( 'page_on_front' ) ); // Set the query to fetch the front page
        $query->is_home = false; // This is not the blog home
        $query->is_page = true; // This is a page
        $query->is_singular = true; // This is a singular entity
        $query->is_front_page = true; // This should be treated as the front page
    }
}
