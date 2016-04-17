<?php

class Debug_Bar_Queries extends Debug_Bar_Panel {
	function init() {
		$this->title( __('Queries', 'debug-bar') );
	}

	function prerender() {
		$this->set_visible( defined('SAVEQUERIES') && SAVEQUERIES || ! empty($GLOBALS['EZSQL_ERROR']) );
	}

	function debug_bar_classes( $classes ) {
		if ( ! empty($GLOBALS['EZSQL_ERROR']) )
			$classes[] = 'debug-bar-warning-summary';
		return $classes;
	}

	function render() {
		global $wpdb, $EZSQL_ERROR;

		$out = '';
		$total_time = 0;

		if ( !empty($wpdb->queries) ) {
			$show_many = isset($_GET['debug_queries']);

			if ( $wpdb->num_queries > 500 && ! $show_many ) {
				/* translators: %s = a url. */
				$out .= "<p>" . sprintf( __('There are too many queries to show easily! <a href="%s">Show them anyway</a>', 'debug-bar'), esc_url( add_query_arg( 'debug_queries', 'true' ) ) ) . "</p>";
			}

			$out .= '<ol class="wpd-queries">';
			$counter = 0;

			foreach ( $wpdb->queries as $q ) {
				list($query, $elapsed, $debug) = $q;

				$total_time += $elapsed;

				if ( ++$counter > 500 && ! $show_many )
					continue;

				$debug = explode( ', ', $debug );
				$debug = array_diff( $debug, array( 'require_once', 'require', 'include_once', 'include' ) );
				$debug = implode( ', ', $debug );
				$debug = str_replace( array( 'do_action, call_user_func_array' ), array( 'do_action' ), $debug );
				$query = nl2br(esc_html($query));

				/* translators: %d = duration time in microseconds. */
				$out .= "<li>$query<br/><div class='qdebug'>$debug <span>#{$counter} (" . sprintf( __( '%0.1fms', 'debug-bar' ), number_format_i18n( ( $elapsed * 1000 ), 1 ) ) . ")</span></div></li>\n";
			}
			$out .= '</ol>';
		} else {
			if ( $wpdb->num_queries == 0 )
				$out .= "<p><strong>" . __('There are no queries on this page.', 'debug-bar') . "</strong></p>";
			else
				$out .= "<p><strong>" . __('SAVEQUERIES must be defined to show the query log.', 'debug-bar') . "</strong></p>";
		}

		if ( ! empty($EZSQL_ERROR) ) {
			$out .= '<h3>' . __( 'Database Errors', 'debug-bar' ) . '</h3>';
			$out .= '<ol class="wpd-queries">';

			foreach ( $EZSQL_ERROR as $e ) {
				$query = nl2br(esc_html($e['query']));
				$out .= "<li>$query<br/><div class='qdebug'>{$e['error_str']}</div></li>\n";
			}
			$out .= '</ol>';
		}

		$heading = '';
		if ( $wpdb->num_queries ) {
			$heading .= '<h2><span>' . __( 'Total Queries:', 'debug-bar' ) . '</span>' . number_format_i18n( $wpdb->num_queries ) . "</h2>\n";
		}
		if ( $total_time ) {
			$heading .= '<h2><span>' . __( 'Total query time:', 'debug-bar' ) . '</span>';
			/* translators: %d = duration time in microseconds. */
			$heading .= sprintf( __( '%0.1f ms', 'debug-bar' ), number_format_i18n( ( $total_time * 1000 ), 1 ) ) . "</h2>\n";
		}
		if ( ! empty($EZSQL_ERROR) ) {
			$heading .= '<h2><span>' . __( 'Total DB Errors:', 'debug-bar' ) . '</span>' . number_format_i18n( count( $EZSQL_ERROR ) ) . "</h2>\n";
		}

		$out = $heading . $out;

		echo $out;
	}
}
