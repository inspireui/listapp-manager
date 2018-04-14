<?php
/*
ListApp API - Support MyListing
*/

/**
 * Add event to json api
 */
function add_events_to_json_api()
{

    global $wp_post_types;

    if (isset($wp_post_types['job_listing'])) {
        $wp_post_types['job_listing']->show_in_rest = true;
        $wp_post_types['job_listing']->rest_base = 'job_listing';
        $wp_post_types['job_listing']->rest_controller_class = 'WP_REST_Posts_Controller';

    }

    global $wp_taxonomies;
    if (isset($wp_taxonomies)) {
        //be sure to set this to the name of your taxonomy!
        $taxonomy_name = array('job_listing_category', 'region', 'case27_job_listing_tags');
        foreach ($taxonomy_name as $k => $name):
            if (isset($wp_taxonomies[$name])) {
                $wp_taxonomies[$name]->show_in_rest = true;
                $wp_taxonomies[$name]->rest_base = $name;
                $wp_taxonomies[$name]->rest_controller_class = 'WP_REST_Terms_Controller';
            }
        endforeach;
    }
}

/**
 * register field to rest api
 */
function register_add_more_fields_to_rest_api()
{

    register_rest_field('job_listing_category',
        'term_image',
        array(
            'get_callback' => 'get_term_meta_image',
            'update_callback' => null,
            'schema' => null,
        )
    );

    register_rest_field('job_listing',
        'link_to_product',
        array(
            'get_callback' => 'get_product_id_linked',
            'update_callback' => null,
            'schema' => null,
        )
    );


    /**
     * Custom meta field with name is "main_image" was be transfered gallery_images in REST API.
     *
     * @since  1.0.0
     */
    register_rest_field('job_listing',
        'comments_ratings',
        array(
            'get_callback' => 'get_comments_ratings',
            'update_callback' => null,
            'schema' => null,
        )
    );


    register_rest_field('job_listing',
        'listing_data',
        array(
            'get_callback' => 'get_post_meta_for_api',
            'schema' => null,
        )
    );

    register_rest_route('wp/v2', '/getRating/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_rating',
    ));

    register_rest_field('job_listing',
        'cost',
        array(
            'get_callback' => 'get_price_for_booking',
            'schema' => null,
        )
    );

    register_rest_field('job_listing',
        'main_image_gallery',
        array(
            'get_callback' => 'get_image_gallery',
            'update_callback' => null,
            'schema' => null,
        )
    );
}

/**
 * Get comment rating
 * @param $object
 * @param $field_name
 * @param $request
 * @return array|bool
 */
function get_comments_ratings($object, $field_name, $request)
{
    $post_id = $object[0];
    $decimals = 1;

    if (empty($post_id)) {
        $post_id = get_the_ID();
    }

    $comments = get_comments(array(
        'post_id' => $post_id,
        'meta_key' => '_case27_ratings',
        'status' => 'approve'
    ));

    if (empty($comments)) {
        return false;
    }

    $total = 0;
    foreach ($comments as $comment) {
        $current_rating = get_comment_meta($comment->comment_ID, '_case27_post_rating', true);
        $total = $total + (double)$current_rating;
    }

    $average = $total / count($comments);

    return [
        'totalReview' => count($comments),
        'totalRate' => number_format($average, $decimals)
    ];
}

/**
 * Get post meta for api
 * @param $object
 * @return mixed
 */
function get_post_meta_for_api($object)
{
    $post_id = $object['id'];
    return get_post_meta($post_id);
}

/**
 * Get term meta image
 * @param $object
 * @param $field_name
 * @param $request
 * @return mixed
 */
function get_term_meta_image($object, $field_name, $request)
{
    $term_meta_id = get_term_meta($object['id'], 'image', true);
    return get_post_meta($term_meta_id, '_wp_attachment_metadata');
}

/**
 * Get product link
 * @param $object
 * @param $field_name
 * @param $request
 * @return mixed
 */
function get_product_id_linked($object, $field_name, $request)
{
    $product_id = get_post_meta($object['id'], '_products', true);
    return $product_id;
}

/**
 * Get rating
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_rating(WP_REST_Request $request)
{
    $id = $request['id'];
    $countRating = get_comment_meta($id, '_case27_post_rating', true);
    return new WP_REST_Response($countRating, 200);
}

/**
 * Get price for booking
 * @param $object
 * @param $field_name
 * @param $request
 * @return array
 */
function get_price_for_booking($object, $field_name, $request)
{
    $currency = get_option('woocommerce_currency');
    $price = get_post_meta($object['id'], '_price-per-day', true);
    return [
        'currency' => $currency,
        'price' => $price,
        'merge' => $currency != 'USD' ? $currency . ' ' . $price : $price . ' ' . $currency
    ];
}

/**
 * Get image gallery
 * @param $object
 * @param $field_name
 * @param $request
 * @return mixed
 */
function get_image_gallery($object, $field_name, $request)
{
    $gallery = get_post_meta($object['id'], '_job_gallery', true);
    return $gallery;
}

