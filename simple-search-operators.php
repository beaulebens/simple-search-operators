<?php

/**
 * Plugin Name: Simple Search Operators
 * Description: Adds support for a few basic search operators (author, tag, category, not, format/type) by intercepting and overriding core search queries. Use "tag:string" etc in a search to help find what you're looking for.
 * Version: 0.2
 * Author: Beau Lebens
 * Author URI: http://dentedreality.com.au
 * Plugin URI: https://github.com/beaulebens/simple-search-operators
 *
 * Supports:
 *
 * 	`author:USERNAME` to search for posts by USERNAME
 * 	`tag:TAG` to search for posts tagged with TAG
 * 	`cat[egory]:CATEGORY` to search posts assigned to CATEGORY (slug!)
 *
 * Operators may be combined, and mixed with freeform text. Be careful, you might
 * generate a pretty ugly query if you get too complex.
 *
 */

class Simple_Search_Operators {
  function __construct() {
    add_action( 'pre_get_posts', array( $this, 'parse_query' ), 999 );
  }

  function parse_query() {
    // Only applies to "front-end" searches
    if ( !is_admin() && is_search() ) {
      if ( $operators = $this->parse_operators( $_REQUEST['s'] ) ) {
        $this->override_search( $operators );
      }
    }
  }

  function parse_operators( $fulltext ) {
    // If it looks like any operators are present, let's process further
    if ( preg_match( '/(author|tag|cat(egory)?|not|format|type):/i', $_REQUEST['s'], $matches ) ) {
      $operators = array( 's' => '' );

      // Process each "word", looking for supported operators
      $words = explode( ' ', $_REQUEST['s'] );
      foreach ( $words as $word ) {
        $bits = explode( ':', $word );

        switch ( $bits[0] ) {
        // Negation supported since WP4.4
        case 'not':
          $operators[ 's' ] .= '-' . $bits[1];
          break;

        case 'author':
          $operators[ 'author_name' ] = $bits[1];
          break;

        case 'cat':
        case 'category':
          $operators[ 'category_name' ] = $bits[1];
          break;

        case 'tag':
          $operators = $this->force_tax_query( $operators );
          $operators[ 'tax_query' ][] = array(
              'taxonomy' => 'post_tag',
              'field'    => 'name',
              'terms'    => array( $bits[1] )
          ); // $bits[0] = $bits[1];
          break;

	case 'format':
	case 'type':
          $operators = $this->force_tax_query( $operators );
          $operators[ 'tax_query' ][] = array(
              'taxonomy' => 'post_format',
              'field'    => 'slug',
              'terms'    => array( 'post-format-' . $bits[1] )
          );
          break;

        default:
          // Unknown operator, so add it to the freeform search
          $operators[ 's' ] .= implode( ':', $bits ) . ' ';
        }
      }

      if ( 1 < count( $operators ) ) {
        return $operators;
      }
    }

    return false;
  }

  function force_tax_query( $operators ) {
    // Combine multiple taxonomy queries if present (and use boolean AND)
    if ( array_key_exists( 'tax_query', $operators ) ) {
      $operators[ 'tax_query' ] = array_merge(
        array( 'relation' => 'AND' ),
        array_values( $operators[ 'tax_query' ] )
      );
    }
    $operators[ 'post_type' ] = 'post';
    return $operators;
  } 

  function override_search( $operators ) {
    foreach ( $operators as $var => $value ) {
      set_query_var( $var, $value );
    }
  }
}

new Simple_Search_Operators;
