<?php

/**
 * Add custom post type of listable to REST API.
 *
 * @since  1.0.0
 */

add_action( 'rest_api_init', 'register_gallery_images' );
function register_gallery_images() {
    register_rest_field( 'job_listing',
        'gallery_images',
        array(
            'get_callback'    => 'get_gallery_images',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

/* -- This is all size of lisable available*/
//listable-card-image
//listable-carousel-image
//listable-featured-image


/**
 * Return Gallery Images field
 *
 * @since  1.0.0
 */
function get_gallery_images( $object, $field_name, $request ) {
    $arr_images = array();
	
    $gallery = get_post_meta( $object[ 'id' ], 'main_image', true );
	$gallery = explode(",",$gallery);
	foreach($gallery as $k=>$value): 
        $image = wp_get_attachment_image_src($value,'listable-featured-image');
  		$arr_images[$k]['size']['listable-featured-image']['url']= $image[0];
   		$arr_images[$k]['size']['listable-featured-image']['width']= $image[1];
  		$arr_images[$k]['size']['listable-featured-image']['height']=  $image[2];

        $image2 = wp_get_attachment_image_src($value,'listable-card-image');
        $arr_images[$k]['size']['listable-card-image']['url']= $image2[0];
   		$arr_images[$k]['size']['listable-card-image']['width']= $image2[1];
  		$arr_images[$k]['size']['listable-card-image']['height']=  $image2[2];

        $image3 = wp_get_attachment_image_src($value,'listable-carousel-image');
        $arr_images[$k]['size']['listable-carousel-image']['url']= $image3[0];
   		$arr_images[$k]['size']['listable-carousel-image']['width']= $image3[1];
  		$arr_images[$k]['size']['listable-carousel-image']['height']=  $image3[2];

        $image4 = wp_get_attachment_image_src($value,'thumbnail');
        $arr_images[$k]['size']['thumbnail']['url']= $image4[0];
   		$arr_images[$k]['size']['thumbnail']['width']= $image4[1];
  		$arr_images[$k]['size']['thumbnail']['height']=  $image4[2];

        $image5 = wp_get_attachment_image_src($value,'medium');
        $arr_images[$k]['size']['medium']['url']= $image5[0];
   		$arr_images[$k]['size']['medium']['width']= $image5[1];
  		$arr_images[$k]['size']['medium']['height']=  $image5[2];

        $image6 = wp_get_attachment_image_src($value,'full');
        $arr_images[$k]['size']['full']['url']= $image6[0];
   		$arr_images[$k]['size']['full']['width']= $image6[1];
  		$arr_images[$k]['size']['full']['height']=  $image6[2];


	endforeach;
    return $arr_images;
}