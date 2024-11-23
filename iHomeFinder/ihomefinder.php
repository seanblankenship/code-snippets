<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Ihome constants
 * add this to the bricks-child/includes folder
 */
define( 'IHOME_UID', get_field( 'uid', 'option' ) );
define( 'IHOME_SID', get_field( 'sid', 'option' ) );


/*
 * Set the content template of a page if it is an iHomefinder virtual page
 */
//add_filter('bricks/active_templates', 'set_active_template_if_ihome_page', 10, 3);
function set_active_template_if_ihome_page( $active_templates, $post_id, $content_type ) {
	// Only do this if iHomefinder is active
	if ( ! interface_exists( 'iHomefinderConstants' ) ) {
		return $active_templates;
	}

	// Only run my logic on the frontend
	if ( ! bricks_is_frontend() ) {
		return $active_templates;
	}

	// Return if single post $content_type is not 'content'
	if ( $content_type !== 'content' ) {
		return $active_templates;
	}

	$type = get_query_var( iHomefinderConstants::IHF_TYPE_URL_VAR );

	if ( ! empty( $type ) ) {
		$ihome_template = get_field( 'ihomefinder_page_template', 'option', false );

		if ( $ihome_template ) {
			$active_templates['content'] = $ihome_template;
		}
	}

	return $active_templates;
}

// add market reports from client account into acf select called market_report
add_filter( 'acf/load_field/name=market_report', 'populate_acf_market_reports' );
function populate_acf_market_reports( $field ) {
	$field['choices'] = array();
	$choices          = array();
	$options          = array();
	$opt_arr          = array();

	$client     = new SoapClient( 'https://secure.idxre.com/ihws/HotSheetsWebService.cfc?wsdl' );
	$hot_sheets = simplexml_load_string( $client->getAllHotSheets( IHOME_UID, IHOME_SID ) );
	$count      = count( $hot_sheets->HotSheet );

	function explode_text( $value ) {
		$replace_with = '|';
		$explode      = str_replace( array( ':', '-' ), $replace_with, $value );
		if ( strpos( $explode, $replace_with ) === false ) {
			$explode = 'Other|' . $explode;
		}

		return $explode;
	}

	if ( $count == 0 ) {
		$hsid = strval( $hot_sheets->HotSheetID );
		$name = strval( $hot_sheets->displayName );
		// $options[] = [$hsid, $name.' - ('.$hsid.')'];
		$explode   = explode_text( $name );
		$options[] = array( $hsid, $name . ' - (' . $hsid . ')' );
		// write_log($hot_sheets);
	}
	for ( $i = 0; $i < $count; ++$i ) {
		$hsid      = strval( $hot_sheets->HotSheet[ $i ]->HotSheetID );
		$name      = strval( $hot_sheets->HotSheet[ $i ]->displayName );
		$explode   = explode_text( $name );
		$options[] = array( $hsid, $name . ' - (' . $hsid . ')' );
	}
	write_log( $options );

	if ( $count > 1 ) {
		usort(
			$options,
			function ( $a, $b ) {
				return $a[1] <=> $b[1];
			}
		);
	}
	$j = 0;
	foreach ( $options as $option ) {
		$n = explode( '|', $explode, 2 );
		if ( count( $n ) > 1 ) {
			$hashed = '#' . $n[0];
			if ( ! in_array( $hashed, $opt_arr ) ) {
				$opt_arr[] = $hashed;
				array_insert( $options, $j, array( $j => array( $hashed, $hashed ) ) );
				$field['choices'][ $options[ $j ][0] ] = $options[ $j ][1];
				++$j;
			}
		}
		$field['choices'][ $options[ $j ][0] ] = $options[ $j ][1];
		++$j;
	}

	$raw_choices = $field['choices'];

	// reformat with optgroups
	$current_group = '';
	foreach ( $raw_choices as $value => $label ) {
		// if first letter is hashtag, turn it into group label
		if ( preg_match( '/^#(.+)/', $label, $matches ) ) {
			$current_group             = str_replace( '#', '', $label );
			$choices[ $current_group ] = array();
		} elseif ( ! empty( $current_group ) ) { // If group label already defined before this line
			$choices[ $current_group ][ $value ] = $label;
		} else {
			$choices[ $value ] = $label;
		}
	}
	$field['choices'] = $choices;

	return $field;
}

function array_insert( &$array, $position, $insert ) {
	if ( is_int( $position ) ) {
		array_splice( $array, $position, 0, $insert );
	} else {
		$pos   = array_search( $position, array_keys( $array ) );
		$array = array_merge(
			array_slice( $array, 0, $pos ),
			$insert,
			array_slice( $array, $pos )
		);
	}
}
