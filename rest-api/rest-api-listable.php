<?php
/*
ListApp API - Support Listable
*/

/**
 * add event to json api
 */
function add_events_to_json_api()
{
    global $wp_post_types;
    global $wp_taxonomies;

    if (isset($wp_post_types['job_listing'])) {
        $wp_post_types['job_listing']->show_in_rest = true;
        $wp_post_types['job_listing']->rest_base = 'job_listing';
        $wp_post_types['job_listing']->rest_controller_class = 'WP_REST_Posts_Controller';
    }


    //be sure to set this to the name of your taxonomy!
    $taxonomy_name = array('job_listing_category', 'job_listing_type');
    if (isset($wp_taxonomies)) {
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
 * register more field to rest api
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
        'gallery_images',
        array(
            'get_callback' => 'get_gallery_images_job_listing',
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
            'get_callback' => 'get_cost_for_booking',
            'schema' => null,
        )
    );


}

/**
 * Return Gallery Images field
 * @param $object
 * @param $field_name
 * @param $request
 * @return array
 */
function get_gallery_images_job_listing($object, $field_name, $request)
{
    $arr_images = array();

    $gallery = get_post_meta($object['id'], 'main_image', true);
    $gallery = explode(",", $gallery);
    foreach ($gallery as $k => $value):
        $image = wp_get_attachment_image_src($value, 'listable-featured-image');
        $arr_images[$k]['sizes']['listable-featured-image']['source_url'] = $image[0];
        $arr_images[$k]['sizes']['listable-featured-image']['width'] = $image[1];
        $arr_images[$k]['sizes']['listable-featured-image']['height'] = $image[2];

        $image2 = wp_get_attachment_image_src($value, 'listable-card-image');
        $arr_images[$k]['sizes']['listable-card-image']['source_url'] = $image2[0];
        $arr_images[$k]['sizes']['listable-card-image']['width'] = $image2[1];
        $arr_images[$k]['sizes']['listable-card-image']['height'] = $image2[2];

        $image3 = wp_get_attachment_image_src($value, 'listable-carousel-image');
        $arr_images[$k]['sizes']['listable-carousel-image']['source_url'] = $image3[0];
        $arr_images[$k]['sizes']['listable-carousel-image']['width'] = $image3[1];
        $arr_images[$k]['sizes']['listable-carousel-image']['height'] = $image3[2];

        $image4 = wp_get_attachment_image_src($value, 'thumbnail');
        $arr_images[$k]['sizes']['thumbnail']['source_url'] = $image4[0];
        $arr_images[$k]['sizes']['thumbnail']['width'] = $image4[1];
        $arr_images[$k]['sizes']['thumbnail']['height'] = $image4[2];

        $image5 = wp_get_attachment_image_src($value, 'medium');
        $arr_images[$k]['sizes']['medium']['source_url'] = $image5[0];
        $arr_images[$k]['sizes']['medium']['width'] = $image5[1];
        $arr_images[$k]['sizes']['medium']['height'] = $image5[2];

        $image6 = wp_get_attachment_image_src($value, 'full');
        $arr_images[$k]['sizes']['full']['source_url'] = $image6[0];
        $arr_images[$k]['sizes']['full']['width'] = $image6[1];
        $arr_images[$k]['sizes']['full']['height'] = $image6[2];


    endforeach;
    return $arr_images;
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
        'meta_key' => 'pixrating',
        'status' => 'approve'
    ));

    if (empty($comments)) {
        return false;
    }

    $total = 0;
    foreach ($comments as $comment) {
        $current_rating = get_comment_meta($comment->comment_ID, 'pixrating', true);
        $total = $total + (double)$current_rating;
    }

    $average = $total / count($comments);

    return [
        'totalReview' => count($comments),
        'totalRate' => number_format($average, $decimals)
    ];
}

/**
 * Get meta for api
 * @param $object
 * @return mixed
 */
function get_post_meta_for_api($object)
{
    $post_id = $object['id'];
    return get_post_meta($post_id);
}

/**
 * Get term meta images
 * @param $object
 * @param $field_name
 * @param $request
 * @return mixed
 */
function get_term_meta_image($object, $field_name, $request)
{
    $term_meta_id = get_term_meta($object['id'], 'pix_term_image', true);
    return get_post_meta($term_meta_id, '_wp_attachment_metadata');
}

/**
 * get product id link
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
    $countRating = get_comment_meta($id, 'pixrating', true);
    return new WP_REST_Response($countRating, 200);
}

/**
 * Get cost for booking
 * @param $object
 * @param $field_name
 * @param $request
 * @return string|void
 */
function get_cost_for_booking($object, $field_name, $request)
{
    $currency = get_option('woocommerce_currency');
    $product_id = get_post_meta($object['id'], '_products', true);
    $_product = wc_get_product($product_id[0]);

    if (!$_product) return;
    return $currency . ' ' . $_product->get_price();
}