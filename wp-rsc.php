<?php
/**
 * Plugin Name: WordPress Really Simple Calendar
 * Plugin URI:  https://allerj.com/code/wp-rsc
 * Description: Really Simple Calendar Plugin provides easy event and calendar display.
 * Author:      J. Aller
 * Author URI:  https://allerj.com
 * Version:     0.5.0
 * Text Domain: wprsc
 */

defined('ABSPATH') || die;
require plugin_dir_path(__FILE__).'calendar.php';
define('rsc_CALENDAR_VER', '0.5.0');

function add_rsc_calendar_admin_css()
{
    wp_enqueue_style('wprsc-compart', plugin_dir_url(__FILE__).'/css/com-part.css', array(), null);
    wp_enqueue_style('wprsc-css', plugin_dir_url(__FILE__).'/css/calendar.css', array(), null);
}
add_action('admin_enqueue_scripts', 'add_rsc_calendar_admin_css');

function add_rsc_calendar_css()
{
    wp_enqueue_style('wprsc-css', plugin_dir_url(__FILE__).'/css/calendar.css', array(), null);
}
add_action('wp_enqueue_scripts', 'add_rsc_calendar_css');

function create_rsc_calendar_post_type()
{
    $rewrite = array(
        'slug'                  => 'our-events',
        'with_front'            => true,
        'pages'                 => true,
        'feeds'                 => true,
    );

    $labels = array(
        'name'                => 'Events',
        'singular_name'       => 'Event',
        'menu_name'           => 'Events',
        'all_items'           => 'All Events',
        'view_item'           => 'View Event' ,
        'add_new_item'        => 'Add New Event',
        'add_new'             => 'Add New',
        'edit_item'           => 'Edit Event',
        'update_item'         => 'Update Event',
        'search_items'        => 'Search Events',

        'not_found'           => 'Not Found',
        'not_found_in_trash'  => 'Not found in Trash',
        'rewrite' => $rewrite,
    );

    register_post_type(
        'wprsc',
        array(
            'labels' 		=> $labels,
            'public'		=> true,
            'menu_icon'     => 'dashicons-calendar-alt',
            'has_archive'	=> true,
            'show_in_rest' => true,

            'supports' 		=> array( 'title', 'editor', 'thumbnail' ),
            'hierarchical' 	=> false,
            'taxonomies'   	=> array( 'event_category' ),
    		'rewrite' => ['slug' => 'event'],
        )
    );
}
add_action('init', 'create_rsc_calendar_post_type');

add_action('init', function () {
    register_taxonomy('event_category', ['wprsc'], [
        'label' => __('Event Categories', 'wprsc'),
        'hierarchical' => false,
        'rewrite' => ['slug' => 'events-in'],
        'show_admin_column' => true,
        'show_in_rest' => true,
        'labels' => [
            'singular_name' => __('Event Category', 'wprsc'),
            'all_items' => __('All Categories', 'wprsc'),
            'edit_item' => __('Edit Category', 'wprsc'),
            'view_item' => __('View Category', 'wprsc'),
            'update_item' => __('Update Category', 'wprsc'),
            'add_new_item' => __('Add New Event Category', 'wprsc'),
            'new_item_name' => __('New Category Name', 'wprsc'),
            'search_items' => __('Search Categories', 'wprsc'),
            'popular_items' => __('Popular Categories', 'wprsc'),

        ]
    ]);
});


function rsc_calendar_change_title_text($title)
{
    $screen = get_current_screen();
    if ('wprsc' == $screen->post_type) {
        $title = 'Event Name';
    }
    return $title;
}
add_filter('enter_title_here', 'rsc_calendar_change_title_text');


function add_rsc_calendar_post_meta_boxes()
{
    add_meta_box(
        "post_metadata_event_box", // div id containing rendered fields
        "Event Details", // section heading displayed as text
        "post_meta_box_event_box", // callback function to render fields
        "wprsc", // name of post type on which to render fields
        'normal', 
        'high'
    );
}
add_action("admin_init", "add_rsc_calendar_post_meta_boxes");


function post_meta_box_event_box()
{
    global $post;
    $custom = get_post_custom($post->ID);

    $start_date = '_event_start_date';
    echo rsc_calendar_input_box('date', $start_date, $custom[ $start_date ][ 0 ], '1/1/2020', 'Event Date', null, null, '2', true);

    // TODO: add multi-day events with _event_end_date
    // $end_date = '_event_end_date';
    // echo rsc_calendar_input_box('date', $end_date, $custom[ $end_date ][ 0 ], '1/2/2020', 'End Date', null, null, '2', true);

    $event_time = '_event_time';
    echo rsc_calendar_input_box('text', $event_time, $custom[ $event_time ][ 0 ], 'From 5pm - 8pm', 'Event Time', null, null, '1', true);

    $location = '_event_location';
    echo rsc_calendar_input_box('text', $location, $custom[ $location ][ 0 ], 'LiFT Academy', 'Location', null, null, '1', true);

    $website = '_event_website';
    echo rsc_calendar_input_box('text', $website, $custom[ $website ][ 0 ], 'https://', 'Event Website', null, null, '1', true);

    $website_text = '_event_website_text';
    echo rsc_calendar_input_box('text', $website_text, $custom[ $website_text ][ 0 ], '', 'Text For Link', null, null, '1', true);
}


function rsc_calendar_input_box($type, $name, $value, $placeholder, $label = null, $css = null, $id = null, $row_of = null, $new_line = false)
{
    $new_line ? $build_input = '<div class="rsc_input block" ' : $build_input = '<div class="rsc_input inline" ' ;
    $row_of ? $build_input .='data-columns="'.$row_of.'">' : $row_of ;
    $label ? $build_input .= '<label>'.$label.'</label><br>' : $label ;
    $build_input .= "<input ";
    $build_input .= 'type="'.$type.'" ';
    $build_input .= 'value="'.$value.'" ';
    $build_input .= 'name="'.$name.'" ';
    $id ? $build_input .='id="'.$id.'" ' : $id ;
    $css ? $build_input .='class="'.$css.'" ' : $css ;
    $placeholder ? $build_input .='placeholder="'.$placeholder.'" ' : $placeholder ;
    $build_input .= '></div>';
    return $build_input;
}


function save_event_post_meta_boxes()
{
    global $post;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (get_post_status($post->ID) === 'auto-draft') {
        return;
    }
    // TODO: add multi-day events with _event_end_date
    $_post_items = array('_event_start_date', '_event_time', '_event_location', '_event_website', '_event_website_text');

    foreach ($_post_items as $post_name) {
        update_post_meta($post->ID, $post_name, sanitize_text_field($_POST[ $post_name ]));
    }
}
add_action('save_post', 'save_event_post_meta_boxes');


function rsc_calendar_run_after_title_meta_boxes()
{
    global $post, $wp_meta_boxes;
    do_meta_boxes(get_current_screen(), 'after_title', $post);
}
add_action('edit_form_after_title', 'rsc_calendar_run_after_title_meta_boxes');


function custom_rsc_calendar_columns($columns)
{
    $columns = array(
        'cb' => '<input type="checkbox" />',

        'title' => 'Community Partner',
        'partner-category' => 'Partner Category',
     );
    return $columns;
}
add_filter('manage_wprsc_posts_columns', 'custom_rsc_calendar_columns');


function custom_rsc_calendar_columns_data($column, $post_id)
{
    $fmt = new NumberFormatter('en_EN', NumberFormatter::CURRENCY);
    $custom = get_post_custom($post_id);
    $terms = get_the_terms($post_id, 'rsc_calendar');
    switch ($column) {
    case 'partner-category':
        foreach ($terms as $term) {
            echo $term->name.'<br>' ;
        };
    break;

    }
}
add_action('manage_wprsc_posts_custom_column', 'custom_rsc_calendar_columns_data', 10, 2);


function events_by_month($month = null, $year = null)
{
    $args = array(
        'post_type'      => 'wprsc',
        'posts_per_page' => '999',
        'meta_key'       => '_event_start_date',
        'orderby'   => 'meta_value',
        'order'     => 'ASC',
        'meta_value'     => $year.'-'.$month.'-',
        'meta_compare'   => 'LIKE' // default operator is (=) equals to
    );

    $query = new WP_Query($args);
    $posts = $query->posts;

    $posts = array_map(
        function ($post) {
            return array(
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_start_date' => get_post_meta($post->ID, '_event_start_date')[0],
                'post_time' => get_post_meta($post->ID, '_event_time')[0],
                'post_location' => get_post_meta($post->ID, '_event_location')[0],
                'post_link' => get_post_permalink($post->ID),
                'post_image' => get_the_post_thumbnail_url($post->ID),
                'post_excerpt' => get_the_excerpt($post->ID)
            );
        },
        $posts
    );
    wp_reset_postdata();

    return $posts;
}


function event_array_by_id($post_id)
{
    $post_array = array(
                'post_title' => get_the_title($post_id),
                'post_content' => get_the_content(null, null, $post_id),
                'post_start_date' => get_post_meta($post_id, '_event_start_date')[0],
                'post_time' => get_post_meta($post_id, '_event_time')[0],
                'post_location' => get_post_meta($post_id, '_event_location')[0],
                'post_link' => get_post_permalink($post_id),
                'event_outbound_link' => get_post_meta($post_id, '_event_website')[0],
                'event_outbound_link_text' => get_post_meta($post_id, '_event_website_text')[0],
                'post_image' => get_the_post_thumbnail_url($post_id),
                'post_excerpt' => get_the_excerpt($post_id)
            );

    return $post_array;
}


function rsc_calendar_preview_page()
{

    
    $calendar = new Calendar();

    if (null == $year && isset($_GET['cy'])) {
        $year = htmlentities($_GET['cy'], ENT_QUOTES | ENT_HTML401, 'UTF-8');
    } elseif (null == $year) {
        $year = date("Y", time());
    }
    if ((!is_numeric($year)) || ($year == "")) {
        $year = date("Y", time());
    }
    if (null == $month && isset($_GET['cm'])) {
        $month = htmlentities($_GET['cm'], ENT_QUOTES | ENT_HTML401, 'UTF-8');
    } elseif (null == $month) {
        $month = date("m", time());
    }
    if ((!is_numeric($month)) || ($month == "")) {
        $month = date("m", time());
    }



    $month_events = events_by_month($month, $year);

    $output = $calendar->show($month_events);
    $count_track = 1;
    $output .= '<div class="container mt-5"><div class="row justify-content-md-center"><div class="col-md-10"><h1 class="merriweather brandDark font-48px light-300 lineheight-1">Events This Month</h1><hr class="wp-block-separator has-text-color has-background has-brand-color-background-color has-brand-color-color short-brand-rule"></div></div>';

    foreach ($month_events as $event) {
        $output .= '<div class="row  justify-content-md-center mt-3"><div class="col-md-5">';
        $output .= '<h4><a href="'.$event['post_link'].'">'.$event['post_title'].'</a></h4>';
        $output .= '<strong>'.date('F j, Y', strtotime($event['post_start_date'])).' - ';
        $output .= $event['post_time'].'</strong><br>';
        $output .= $event['post_location'].'<br>';
        $output .= '<p class="mt-3">'.nl2br($event['post_excerpt']).'</p>';
        $output .= '<p class="mt-3 font-12px"><a href="'.$event['post_link'].'">Learn More</a></p>';
        $output .= '</div>';
        $output .= '<div class="col-md-3">';
        $output .= '<a href="'.$event['post_link'].'"><img src="'.$event['post_image'].'"></a>';
        $output .= '</div>';
        $output .= '</div>';

        if (count($month_events) != $count_track) {
            $output .= '<div class="row justify-content-md-center"><div class="col-md-8"><hr class="wp-block-separator has-text-color has-background has-brand-color-background-color has-brand-color-color short-brand-rule"></div></div>';
        }
        $count_track++;
    }


    return $output;
}
add_shortcode('rsc_calendar_view', 'rsc_calendar_preview_page');
