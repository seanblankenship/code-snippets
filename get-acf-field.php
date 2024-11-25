<?php

/**
 * Display an acf field
 *
 * @param   $atts   array   The shortcode attributes
 * @return          string  Returns the field value as a string with / without a link
 */
add_shortcode( 'appnet_acf', 'appnet_acf_shortcode' );
function appnet_acf_shortcode( $atts ) {
	shortcode_atts(
		array(
			'field'        => '',    // string   acf field name
			'options'      => false, // bool     [optional] is this on an options page? will override post_id
			'link'         => true,  // bool     [optional] should the field be a link; defaults to true and checks if email or phone number; false will display only value
			'post_id'      => false, // mixed    [optional] post id will be overwritten if there is an options page
			'format_value' => true,  // bool     [optional] whether to apply formatting logic
			'escape_html'  => false, // bool     [optional] return an escaped HTML safe version of the field value
			'url'          => '',    // string   [optional] will only work if link is true and there is a value; overrides the value of a phone number or email address
		),
		$atts
	);
	$field        = $atts['field'];
	$post_id      = $atts['post_id'];
	$url          = $atts['url'];
	$options      = set_to_boolean( $atts['options'], 'options' );
	$link         = set_to_boolean( $atts['link'], 'link' );
	$format_value = set_to_boolean( $atts['format_value'], 'format_value' );
	$escape_html  = set_to_boolean( $atts['escape_html'], 'escape_html' );
	$post_id      = ( $options === true ) ? 'option' : false;
	$value        = get_field( $field, $post_id, $format_value, $escape_html );

	if ( $link === false ) {
		return $value;
	}
	if ( ! empty( $url ) ) {
		return '<a href="' . $url . '">' . $value . '</a>';
	}
	if ( filter_var( $value, FILTER_VALIDATE_EMAIL ) !== false ) {
		return '<a href="mailto:' . $value . '">' . $value . '</a>';
	} else {
		$sanitizedValue = preg_replace( '/\D/', '', $value );
		if ( preg_match( '/^\+?\d{10,15}$/', $sanitizedValue ) ) {
			return '<a href="tel:' . $sanitizedValue . '">' . $value . '</a>';
		} else {
			return $value;
		}
	}
	return 'There has been an error.';
}

/**
 * Shortcode helper that takes an attribute and sets to boolean
 *
 * @param   $value  string  The value of the attribute to check
 * @param   $field  string  [optional] Name of the field we are checking to return in error message
 * @return          mixed   Returns the boolean value or an error message
 */
function set_to_boolean( $value, $field = 'your field' ) {

	$value = strtolower( $value );
	if ( $value === 'false' || $value === '0' ) {
		$value = false;
	} elseif ( $value === 'true' || $value === '1' ) {
		$value = true;
	} else {
		return 'Please enter a valid value for ' . $field . '.';
	}
	return (bool) $value;
}
