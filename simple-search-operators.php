<?php

/**
 * Plugin Name: Simple Search Operators
 * Description: Adds support for a few basic search operators (author, tag, category) by intercepting and overriding core search queries.
 * Version: 0.1
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
    add_action( 'parse_query', array( $this, 'parse_query' ) );
  }

  function parse_query() {
    // Only applies to "front-end" searches
    if ( !is_admin() && is_search() ) {
      if ( $operators = $this->parse_operators( $_REQUEST['s'] ) ) {
        global $wp_query;
        $this->override_search( $wp_query, $operators );
      }
    }
  }

  function parse_operators( $fulltext ) {
    // If it looks like any operators are present, let's process further
    if ( preg_match( '/(author|cat(egory)?|day|month|tag|year):/i', $_REQUEST['s'], $matches ) ) {
      $operators = array( 's' => '' );

      // Process each "word", looking for supported operators
      $words = explode( ' ', $_REQUEST['s'] );
      foreach ( $words as $word ) {
        $bits = explode( ':', $word );

        switch ( $bits[0] ) {
        case 'author':
          $operators[ 'author_name' ] = $bits[1];
          break;

        case 'cat':
        case 'category':
          $operators[ 'category_name' ] = $bits[1];
          break;

        case 'month':
          $operators[ 'monthnum' ] = $bits[1];
          break;

        // No rename necessary
        case 'day':
        case 'tag':
        case 'year':
          $operators[ $bits[0] ] = $bits[1];
          break;

        default:
          // Unknown operator, so add it to the freeform search
          $operators['s'] .= implode( ':', $bits );
        }
      }

      if ( 1 < count( $operators ) ) {
        return $operators;
      }
    }

    return false;
  }

  function override_search( $query, $operators ) {
    foreach ( $operators as $var => $value ) {
      set_query_var( $var, $value );
    }
  }
}

new Simple_Search_Operators;
