<?php

class Template extends WP_REST_Posts_Controller
{

	protected $_template = 'listable'; // get_template
	protected $_listable = 'listable';
	protected $_listify = 'listify';
	protected $_listingPro = 'listingpro';
	protected $_myListing = 'my listing';

	protected $_customPostType = ['job_listing',  'listing']; // all custom post type
	protected $_isListable,  $_isListify, $_isMyListing, $_isListingPro;

	public function __construct(){
		/* extends from parent */
		parent::__construct('job_listing');
		// echo wp_get_theme();
		$isChild = strstr(strtolower(wp_get_theme()), "child");
		if($isChild == 'child'){
			$string = explode(" ", wp_get_theme());
			$this->_template = strtolower($string[0]) ;
		}else{
			$this->_template = strtolower(wp_get_theme());
		}

	
	 	$this->_isListable = $this->_template == $this->_listable ? 1 : 0;
		$this->_isListify = $this->_template == $this->_listify ? 1 : 0;
		$this->_isMyListing = $this->_template == $this->_myListing ? 1 : 0;
		$this->_isListingPro = $this->_template == $this->_listingPro ? 1 : 0;

		add_action('init', array($this, 'add_custom_type_to_rest_api'));
		add_action('rest_api_init', array($this, 'register_add_more_fields_to_rest_api'));
	}

	/**
	 * Add custom type to rest api
	 */
	public function add_custom_type_to_rest_api()
	{
		global $wp_post_types, $wp_taxonomies, $post;
	    if (isset($wp_post_types['job_listing'])) {
	        $wp_post_types['job_listing']->show_in_rest = true;
	        $wp_post_types['job_listing']->rest_base = 'job_listing';
	        $wp_post_types['job_listing']->rest_controller_class = 'WP_REST_Posts_Controller';
	    }


	    //be sure to set this to the name of your taxonomy!
	    $taxonomy_name = array('job_listing_category', 'job_listing_type', 'job_listing_region', 'location');
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
	 * Register more field to rest api
	 */
	public function register_add_more_fields_to_rest_api()
	{

		// Get Field Category Custom 
		$field_cate = $this->_isListingPro ? 'listing-category' : 'job_listing_category';
	    register_rest_field($field_cate,
	        'term_image',
	        array(
	            'get_callback' => array($this, 'get_term_meta_image'),
	        )
	    );


	    register_rest_field($this->_customPostType,
	        'link_to_product',
	        array(
	            'get_callback' => array($this, 'get_product_id_linked'),
	            'update_callback' => null,
	            'schema' => null,
	        )
	    );

	    
	    /* --- meta field for gallery image --- */
	    if($this->_isListify){
	    	register_rest_field($this->_customPostType,
		        'job_hours',
		        array(
		            'get_callback' => array($this, 'get_job_hours'),
		            'update_callback' => null,
		            'schema' => null,
		        )
		    );

	    }

    	register_rest_field($this->_customPostType,
	        $this->_isListable ? 'gallery_images' : 'main_image_gallery' ,
	        array(
	            'get_callback' => array($this, $this->_isListable ? 'get_gallery_images_job_listing' : 'get_image_gallery'),
	            'update_callback' => null,
	            'schema' => null,
	        )
	    );
	    

	    register_rest_field($this->_customPostType,
	        'comments_ratings',
	        array(
	            'get_callback' => array($this, 'get_comments_ratings'),
	            'update_callback' => null,
	            'schema' => null,
	        )
	    );


	    register_rest_field($this->_customPostType,
	        'listing_data',
	        array(
	            'get_callback' => array($this, 'get_post_meta_for_api'),
	            'schema' => null,
	        )
	    );


	    register_rest_field($this->_customPostType,
	        'cost',
	        array(
	            'get_callback' => array($this, 'get_cost_for_booking'),
	            'schema' => null,
	        )
	    );

	    /* Register for custom routes to rest API */
	    register_rest_route('wp/v2', '/getRating/(?P<id>\d+)', array(
	        'methods' => 'GET',
	        'callback' => array($this, 'get_rating'),
	    ));

	    if($this->_isMyListing){
	    	/* get listing by tags for case myListing */
		    register_rest_route( 'tags/v1', '/job_listing', array(
				'methods' => 'GET',
				'callback' => array($this, 'get_job_listing_by_tags'),
				'args' => array(
					'tag' => array(
					),
					'page' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
					'limit' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			) );

		    // get by listing tools case for myListing theme
		    register_rest_route( 'listing/v1', '/job_listing', array(
				'methods' => 'GET',
				'callback' => array($this, 'get_job_listing_by_type'),
				'args' => array(
					'type' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_string( $param );
						}
					),
					'page' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
					'limit' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			) );
	    }

	    if($this->_isListingPro){
	    	register_rest_field($this->_customPostType,
		        'gallery_images',
		        array(
		            'get_callback' => array($this, 'get_post_gallery_images_listingPro'),
		        )
		    );
		    register_rest_field($this->_customPostType,
		        'more_options',
		        array(
		            'get_callback' => array($this, 'get_post_more_options'),
		        )
		    );

		    
	    }


	}

	/* --- - MyListing - ---*/
	public function get_job_listing_by_tags($request){
		$args = [
			'post_type' => 'job_listing',
			'paged' => $request['page'] ? $request['page'] : 1,
			'posts_per_page' => $request['limit'] ? $request['limit'] : 10,
		];
		if($request['tag']){
			$args['tax_query'][] = array(
                    'taxonomy' => 'case27_job_listing_tags',
                    'field'    => 'term_id',
                    'terms'    => explode(',', $request['tag'])
            );
		}
		global $wpdb;
		$posts= query_posts($args);
		$data = array();
		$items = (array)($posts);
		// return $items;
		foreach($items as $item):
			$itemdata = $this->prepare_item_for_response( $item , $request);
			$data[] = $this->prepare_response_for_collection( $itemdata );
		endforeach;

		return new WP_REST_Response( $data, 200 );

	}
	

	/* --- - ListingPro - ---*/
	public function get_post_gallery_images_listingPro($object)
	{

		$gallery =  get_post_meta($object['id'], 'gallery_image_ids', true);
		$gallery = explode(',', $gallery);
		if($gallery){
			foreach ($gallery as $value) {
				$getVal = get_post_meta($value, '_wp_attached_file', true);
				if(!empty($getVal)){
					$results[] =  get_bloginfo('url').'/wp-content/uploads/'.$getVal;
				}
			}
		}
		return $results;
	}

	public function get_post_more_options($object)
	{

		$options =  get_post_meta($object['id'], 'lp_listingpro_options', true);
		return $options;
	}


	/*- --- - Listable - ---- */

 	/* Meta Fields Rest API */
	/**
	 * Get term meta images
	 * @param $object
	 * @param $field_name
	 * @param $request
	 * @return mixed
	 */
	public function get_term_meta_image($object)
	{

		if($this->_isListable){
			$name = 'pix_term_image';
		}elseif($this->_isListify){
			$name = 'thumbnail_id';
		}elseif($this->_isListingPro){
			$name = 'lp_category_banner_id';
		}else{
			$name = 'image';
		}
	    $term_meta_id = get_term_meta($object['id'], $name, true);
	    return get_post_meta($term_meta_id, '_wp_attachment_metadata');
	}

	/**
	 * get product id link
	 * @param $object
	 * @param $field_name
	 * @param $request
	 * @return mixed
	 */
	public function get_product_id_linked($object)
	{
		$name = '_products';
		if($this->_isMyListing){
			$name = '_select_products';
		}
	    $product_id = get_post_meta($object['id'], $name, true);
	    return $product_id;
	}

	/**
	 * Return Gallery Images field
	 * @param $object
	 * @param $field_name
	 * @param $request
	 * @return array
	 */
	public function get_gallery_images_job_listing($object)
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
	 * Get image gallery
	 * @param $object
	 * @param $field_name
	 * @param $request
	 * @return mixed
	 */
	public function get_image_gallery($object)
	{
		$name = $this->_isListify ? '_gallery_images' : '_job_gallery';
	    $gallery = get_post_meta($object['id'], $name, true);
	    return $gallery;
	}


	/**
	 * Get comment rating
	 * @param $object
	 * @param $field_name
	 * @param $request
	 * @return array|bool
	 */
	public function get_comments_ratings($object)
	{
		$meta_key = $commentKey = 'pixrating';

		if($this->_isListify){
			$meta_key = $commentKey = 'rating';
		}elseif($this->_isMyListing){
			$meta_key = '_case27_ratings';
			$commentKey = '_case27_post_rating';
		}

	    $post_id = $object[0];
	    $decimals = 1;

	    if (empty($post_id)) {
	        $post_id = get_the_ID();
	    }

	    $comments = get_comments(array(
	        'post_id' => $post_id,
	        'meta_key' => $meta_key,
	        'status' => 'approve'
	    ));

	    if (empty($comments)) {
	        return false;
	    }

	    $total = 0;
	    foreach ($comments as $comment) {
	        $current_rating = get_comment_meta($comment->comment_ID, $commentKey, true);
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
	public function get_post_meta_for_api($object)
	{
	    $post_id = $object['id'];
	    return get_post_meta($post_id);
	}

	/**
	 * Get rating
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_rating(WP_REST_Request $request)
	{
		$name = 'pixrating';
		if($this->_isListify){
			$name = 'rating';
		}elseif($this->_isMyListing){
			$name = '_case27_post_rating';
		}
	    $id = $request['id'];
	    $countRating = get_comment_meta($id, $name, true);
	    return new WP_REST_Response($countRating, 200);
	}


	/**
	 * Get cost for booking
	 * @param $object
	 * @param $field_name
	 * @param $request
	 * @return string|void
	 */
	public function get_cost_for_booking($object)
	{
	    $currency = get_option('woocommerce_currency');
	    $product_id = get_post_meta($object['id'], '_products', true);
	    if($this->_isListable){
	    	$_product = wc_get_product($product_id[0]);

	    	if (!$_product) return;
	    	return $currency . ' ' . $_product->get_price();
	    }elseif($this->_isListify){
	    	$_product = new WC_Product($product_id[0]);
		    return [
		        'currency' => $currency,
		        'price' => $_product->get_price(),
		        'merge' => $currency . ' ' . $_product->get_price()
		    ];
	    }else{
		    $price = get_post_meta($object['id'], '_price-per-day', true);
		    return [
		        'currency' => $currency,
		        'price' => $price,
		        'merge' => $currency != 'USD' ? $currency . ' ' . $price : $price . ' ' . $currency
		    ];
	    }
	    return [];
	    
	}

	/**
	 * Get job hours
	 * @param $object
	 * @param $field_name
	 * @param $request
	 * @return mixed
	 */
	public function get_job_hours($object)
	{
	    $_job_hours = get_post_meta($object['id'], '_job_hours', true);
	    return $_job_hours;
	}



	public function protected_title_format() {
		return '%s';
	}

	public function prepare_item_for_response( $post, $request ) {
		$GLOBALS['post'] = $post;

		setup_postdata( $post );

		$schema = $this->get_item_schema();
		$this->add_additional_fields_schema($schema);
		// Base fields for every post.
		$data = array();
		// return $schema;
		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = $post->ID;
		}

		if ( ! empty( $schema['properties']['date'] ) ) {
			$data['date'] = $this->prepare_date_response( $post->post_date_gmt, $post->post_date );
		}

		if ( ! empty( $schema['properties']['date_gmt'] ) ) {
			// For drafts, `post_date_gmt` may not be set, indicating that the
			// date of the draft should be updated each time it is saved (see
			// #38883).  In this case, shim the value based on the `post_date`
			// field with the site's timezone offset applied.
			if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) {
				$post_date_gmt = get_gmt_from_date( $post->post_date );
			} else {
				$post_date_gmt = $post->post_date_gmt;
			}
			$data['date_gmt'] = $this->prepare_date_response( $post_date_gmt );
		}

		if ( ! empty( $schema['properties']['guid'] ) ) {
			$data['guid'] = array(
				/** This filter is documented in wp-includes/post-template.php */
				'rendered' => apply_filters( 'get_the_guid', $post->guid, $post->ID ),
				'raw'      => $post->guid,
			);
		}

		if ( ! empty( $schema['properties']['modified'] ) ) {
			$data['modified'] = $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified );
		}

		if ( ! empty( $schema['properties']['modified_gmt'] ) ) {
			// For drafts, `post_modified_gmt` may not be set (see
			// `post_date_gmt` comments above).  In this case, shim the value
			// based on the `post_modified` field with the site's timezone
			// offset applied.
			if ( '0000-00-00 00:00:00' === $post->post_modified_gmt ) {
				$post_modified_gmt = date( 'Y-m-d H:i:s', strtotime( $post->post_modified ) - ( get_option( 'gmt_offset' ) * 3600 ) );
			} else {
				$post_modified_gmt = $post->post_modified_gmt;
			}
			$data['modified_gmt'] = $this->prepare_date_response( $post_modified_gmt );
		}

		if ( ! empty( $schema['properties']['password'] ) ) {
			$data['password'] = $post->post_password;
		}

		if ( ! empty( $post->distance) ) {

			$data['distance'] = $post->distance;
		}

		if ( ! empty( $schema['properties']['slug'] ) ) {
			$data['slug'] = $post->post_name;
		}

		if ( ! empty( $schema['properties']['status'] ) ) {
			$data['status'] = $post->post_status;
		}

		if ( ! empty( $schema['properties']['type'] ) ) {
			$data['type'] = $post->post_type;
		}

		if ( ! empty( $schema['properties']['link'] ) ) {
			$data['link'] = get_permalink( $post->ID );
		}

		if ( ! empty( $schema['properties']['title'] ) ) {

			add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );

			$data['title'] = array(
				'raw'      => $post->post_title,
				'rendered' => get_the_title( $post->ID ),
			);

			remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
		}

		$has_password_filter = false;

		if ( $this->can_access_password_content( $post, $request ) ) {
			// Allow access to the post, permissions already checked before.
			add_filter( 'post_password_required', '__return_false' );

			$has_password_filter = true;
		}

		if ( ! empty( $schema['properties']['content'] ) ) {
			$data['content'] = array(
				'raw'       => $post->post_content,
				/** This filter is documented in wp-includes/post-template.php */
				'rendered'  => post_password_required( $post ) ? '' : apply_filters( 'the_content', $post->post_content ),
				'protected' => (bool) $post->post_password,
			);
		}

		if ( ! empty( $schema['properties']['excerpt'] ) ) {
			/** This filter is documented in wp-includes/post-template.php */
			$excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $post->post_excerpt, $post ) );
			$data['excerpt'] = array(
				'raw'       => $post->post_excerpt,
				'rendered'  => post_password_required( $post ) ? '' : $excerpt,
				'protected' => (bool) $post->post_password,
			);
		}

		if ( $has_password_filter ) {
			// Reset filter.
			remove_filter( 'post_password_required', '__return_false' );
		}

		if ( ! empty( $schema['properties']['author'] ) ) {
			$data['author'] = (int) $post->post_author;
		}

		if ( ! empty( $schema['properties']['featured_media'] ) ) {
			$data['featured_media'] = (int) get_post_thumbnail_id( $post->ID );
		}

		if ( ! empty( $schema['properties']['parent'] ) ) {
			$data['parent'] = (int) $post->post_parent;
		}

		if ( ! empty( $schema['properties']['menu_order'] ) ) {
			$data['menu_order'] = (int) $post->menu_order;
		}

		if ( ! empty( $schema['properties']['comment_status'] ) ) {
			$data['comment_status'] = $post->comment_status;
		}

		if ( ! empty( $schema['properties']['ping_status'] ) ) {
			$data['ping_status'] = $post->ping_status;
		}

		if ( ! empty( $schema['properties']['sticky'] ) ) {
			$data['sticky'] = is_sticky( $post->ID );
		}

		if ( ! empty( $schema['properties']['template'] ) ) {
			if ( $template = get_page_template_slug( $post->ID ) ) {
				$data['template'] = $template;
			} else {
				$data['template'] = '';
			}
		}

		if ( ! empty( $schema['properties']['format'] ) ) {
			$data['format'] = get_post_format( $post->ID );

			// Fill in blank post format.
			if ( empty( $data['format'] ) ) {
				$data['format'] = 'standard';
			}
		}


		if ( ! empty( $schema['properties']['meta'] ) ) {
			$data['meta'] = $this->meta->get_value( $post->ID, $request );

		}

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			if ( ! empty( $schema['properties'][ $base ] ) ) {
				$terms = get_the_terms( $post, $taxonomy->name );
				$data[ $base ] = $terms ? array_values( wp_list_pluck( $terms, 'term_id' ) ) : array();
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $post ) );

		/**
		 * Filters the post data for a response.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 4.7.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "rest_prepare_job_listing", $response, $post, $request );
	}

	/**
	 * Prepare a response for inserting into a collection.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @return array Response data, ready for insertion into collection data.
	 */
	
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data   = (array) $response->get_data();
		$server = rest_get_server();

		if ( method_exists( $server, 'get_compact_response_links' ) ) {
			$links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
		} else {
			$links = call_user_func( array( $server, 'get_response_links' ), $response );
		}

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	public function get_job_listing_by_type($request){
		$posts = query_posts( array(
			'meta_key' => '_case27_listing_type',
			'meta_value' => $request['type'],
			'post_type' => 'job_listing',
			'paged' => $request['page'],
			'posts_per_page' => $request['limit']
		) );

		$data = array();
		$items = (array)($posts);
		
		foreach($items as $item):
			$itemdata = $this->prepare_item_for_response( $item , $request);
			$data[] = $this->prepare_response_for_collection( $itemdata );
		endforeach;

		return new WP_REST_Response( $data, 200 );

	}

} // end Class


// class For get case27_job_listing_tags for get All Tags to show in Filter Search
class TemplateExtendMyListing extends WP_REST_Terms_Controller
{
	protected $_template = 'listable'; // get_template
	protected $_listable = 'listable';
	protected $_listify = 'listify';
	protected $_myListing = 'my listing';

	protected $_customPostType = ['job_listing']; // all custom post type
	protected $_isListable,  $_isListify, $_isMyListing;

	public function __construct(){
		/* extends from parent */
		parent::__construct('job_listing');
		
		$isChild = strstr(strtolower(wp_get_theme()), "child");
		if($isChild == 'child'){
			$string = explode(" ", wp_get_theme());
			$this->_template = strtolower($string[0]) ;
		}else{
			$this->_template = strtolower(wp_get_theme());
		}
		
	 	$this->_isListable = $this->_template == $this->_listable ? 1 : 0;
		$this->_isListify = $this->_template == $this->_listify ? 1 : 0;
		$this->_isMyListing = $this->_template == $this->_myListing ? 1 : 0;

		add_action('rest_api_init', array($this, 'register_add_more_fields_to_rest_api_listing'));
	}

	public function register_add_more_fields_to_rest_api_listing(){
		// case for myListing with job_listing_type
	    if($this->_isMyListing){

	    	register_rest_route('listing/v1', 'case27_job_listing_tags',
		        array(
		        	'methods' => 'GET',
		            'callback' => array($this, 'get_case27_job_listing_tags'),
		        )
		    );

	    	
		}


	}
	public function prepare_item_for_response( $item, $request ) {

		$schema = $this->get_item_schema();
		$data   = array();

		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = (int) $item->term_id;
		}

		if ( ! empty( $schema['properties']['count'] ) ) {
			$data['count'] = (int) $item->count;
		}

		if ( ! empty( $schema['properties']['description'] ) ) {
			$data['description'] = $item->description;
		}

		if ( ! empty( $schema['properties']['link'] ) ) {
			$data['link'] = get_term_link( $item );
		}

		if ( ! empty( $schema['properties']['name'] ) ) {
			$data['name'] = $item->name;
		}

		if ( ! empty( $schema['properties']['slug'] ) ) {
			$data['slug'] = $item->slug;
		}

		if ( ! empty( $schema['properties']['taxonomy'] ) ) {
			$data['taxonomy'] = $item->taxonomy;
		}

		if ( ! empty( $schema['properties']['parent'] ) ) {
			$data['parent'] = (int) $item->parent;
		}

		if ( ! empty( $schema['properties']['meta'] ) ) {
			$data['meta'] = $this->meta->get_value( $item->term_id, $request );
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		// $data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filters a term item returned from the API.
		 *
		 * The dynamic portion of the hook name, `$this->taxonomy`, refers to the taxonomy slug.
		 *
		 * Allows modification of the term data right before it is returned.
		 *
		 * @since 4.7.0
		 *
		 * @param WP_REST_Response  $response  The response object.
		 * @param object            $item      The original term object.
		 * @param WP_REST_Request   $request   Request used to generate the response.
		 */
		return apply_filters( "rest_prepare_case27_job_listing_tags", $response, $item, $request );
	}


	public function get_case27_job_listing_tags($request){
		$posts = get_terms(['case27_job_listing_tags']);
		$data = array();
		$items = (array)($posts);
		foreach($items as $item):
			$itemdata = $this->prepare_item_for_response( $item , $request);
			$data[] =   $itemdata ;
		endforeach;
		$result = [];
		foreach($data as $item):
			$result[] =  $item->data ;
		endforeach;



		return new WP_REST_Response( $result, 200 );
	}

}


class TemplateSearch extends Template {

	public function __construct(){
		/* extends from parent */
		parent::__construct('job_listing');
		add_action('rest_api_init', array($this, 'register_fields_for_search_advance'));
	}

	/*
	* define for method for search
	*/
	public function register_fields_for_search_advance(){
			/* get search by tags & categories for case myListing */
		    register_rest_route( 'search/v1', '/job_listing', array(
				'methods' => 'GET',
				'callback' => array($this, 'search_by_myParams'),
				'args' => array(
					'tags' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_string( $param );
						}
					),
					'categories' => array(
						// 'validate_callback' => function($param, $request, $key) {
						// 	return is_string( $param );
						// }
					),
					'type' => array(
						// 'validate_callback' => function($param, $request, $key) {
						// 	return is_string( $param );
						// }
					),
					'regions' => array(
						// 'validate_callback' => function($param, $request, $key) {
						// 	return is_string( $param );
						// }
					), // for listify
					'typeListable' => array(

					), // for listable
					'search' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_string( $param );
						}
					),
					'isGetLocate' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_string( $param );
						}
					),
					'lat' => array(
						
					),
					'long' => array(
					
					),
					'page' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
					'limit' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			) );

		if($this->_isMyListing){
			register_rest_route( 'searchExtends/v1', '/job_listing', array(
				'methods' => 'GET',
				'callback' => array($this, 'searchQuery'),
				'args' => array(
					
					'search' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_string( $param );
						}
					),
					'page' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
					'limit' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			) );
		}
	}

	public function search_by_myParams($request){
		$args = [
			'post_type' => 'job_listing',
			'paged' => $request['page'] ? $request['page'] : 1,
			'posts_per_page' => $request['limit'] ? $request['limit'] : 10,
		];
		if($request['tags']){
			$args['tax_query'][] = array(
                    'taxonomy' => 'case27_job_listing_tags',
                    'field'    => 'term_id',
                    'terms'    => explode(',', $request['tags'])
            );
		}
		if($request['categories']){
			$args['tax_query'][] = array(
                    'taxonomy' => 'job_listing_category',
                    'field'    => 'term_id',
                    'terms'    =>  explode(',', $request['categories']),
            );
            
		}
		if($request['type']){
			$args['meta_query']= [[
				'key'     => '_case27_listing_type',
				'value'   => $request['type'],
				'compare' => 'LIKE',
			]];
		}
		//case for listify
		if($request['regions']){
            $args['tax_query'][] = array(
                    'taxonomy' => 'job_listing_region',
                    'field'    => 'term_id',
                    'terms'    =>  explode(',', $request['regions']),
            );
		}
		//case for listable
		if($request['typeListable']){
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'job_listing_type',
                    'field'    => 'term_id',
                    'terms'    =>  explode(',', $request['typeListable']),
                ),
            );
		}
		if($request['search']){
            $args['s'] = $request['search'];
		}

		global $wpdb;
		$posts= query_posts($args);
		
		if($request['isGetLocate']){
			$lat = $request['lat'];
			$long = $request['long'];
			$sql = "SELECT p.*, ";
			$sql.= " (6371 * acos (cos (radians($lat)) * cos(radians(t.lat)) * cos(radians(t.lng) - radians($long)) + ";
			$sql.="sin (radians($lat)) * sin(radians(t.lat)))) AS distance FROM (SELECT b.post_id, sum(if(";
			$sql.="meta_key = 'geolocation_lat', meta_value, 0)) AS lat, sum(if(meta_key = 'geolocation_long', ";
			$sql.="meta_value, 0)) AS lng FROM {$wpdb->prefix}posts a, {$wpdb->prefix}postmeta b WHERE a.id = b.post_id AND (";
			$sql.="b.meta_key='geolocation_lat' OR b.meta_key='geolocation_long') GROUP BY b.post_id) AS t INNER ";
			$sql.="JOIN {$wpdb->prefix}posts as p on (p.ID=t.post_id)  ORDER BY distance LIMIT 10";
			$vars = array($lat, $long, $lat);
			
			$posts = $wpdb->get_results($sql, OBJECT);
			if ($wpdb->last_error) {
			 return 'Error: ' . $wpdb->last_error;
			}
			// return $posts;
		}
		
		$data = array();
		$items = (array)($posts);
		// return $items;
		foreach($items as $item):
			$itemdata = $this->prepare_item_for_response( $item , $request);
			$data[] = $this->prepare_response_for_collection( $itemdata );
		endforeach;

		return new WP_REST_Response( $data, 200 );
	}

	public function searchQuery($request){
		$args = [
			'post_type' => 'job_listing',
			'paged' => $request['page'] ? $request['page'] : 1,
			'posts_per_page' => $request['limit'] ? $request['limit'] : 10,
		];
		if($request['search']){
            $args['s'] = $request['search'];
		}

		$categories = get_terms([
            'taxonomy' => 'job_listing_category',
            'search' => isset($request['search']) ? $request['search'] : '',
        ]);

		$args['meta_query'] = [[
                'key' => '_case27_listing_type',
                'value' => '',
                'compare' => '!=',
         ]];

        global $wpdb;
        $listings= query_posts($args);
		
		

		$data = array();
		$items = (array)($listings);
		// return $items;
		foreach($items as $item):
			$itemdata = $this->prepare_item_for_response( $item , $request);
			$data[] = $this->prepare_response_for_collection( $itemdata );
		endforeach;

		$listings_grouped = [];

        foreach ($data as $listing) {
        	// return $listing['job_listing_category'][0];
        	foreach ($listing['job_listing_category'] as $value) {
        		$type = get_term_by('id', $value, 'job_listing_category')->name;
	            if (!isset($listings_grouped[$type])) $listings_grouped[$type] = [];

	            $listings_grouped[$type][] = $listing;	
        	}
            
        }


		return new WP_REST_Response( $listings_grouped, 200 );
	}
}







new Template;

new TemplateExtendMyListing;

new TemplateSearch;