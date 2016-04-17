<?php

class Debug_Bar_Doing_It_Wrong extends Debug_Bar_Deprecated {

	static $doing_it_wrong = array();

	static function start_logging() {
		add_action( 'doing_it_wrong_run', array( __CLASS__, 'doing_it_wrong_run' ), 10, 3 );
		add_filter( 'doing_it_wrong_trigger_error', '__return_false' );
	}

	static function stop_logging() {
		remove_action( 'doing_it_wrong_run', array( __CLASS__, 'doing_it_wrong_run' ), 10 );
		remove_filter( 'doing_it_wrong_trigger_error', '__return_false' );
	}

	function init() {
		$this->title( __( 'Doing It Wrong', 'debug-bar' ) );
		$this->set_visible( false );
	}

	function get_total() {
		return count( self::$doing_it_wrong );
	}

	function render() {
		echo '<div id="debug-bar-doingitwrong">';
		$this->render_title( __( 'Total Calls:', 'debug-bar' ), count( self::$doing_it_wrong ) );
		$this->render_list( self::$doing_it_wrong, 'doingitwrong' );
		echo '</div>';
	}

	static function doing_it_wrong_run( $function, $message, $version ) {
		$backtrace = debug_backtrace( false );
		$bt = 4;
		if ( ! isset( $backtrace[4]['file'] ) && 'call_user_func_array' == $backtrace[5]['function'] ) {
			$bt = 6;
		}
		$file = $backtrace[ $bt ]['file'];
		$line = $backtrace[ $bt ]['line'];
		/* TRANSLATORS: %s: version number */
		$version = is_null( $version ) ? '' : sprintf( __( '(This message was added in version %s.)' ), $version );
		/* TRANSLATORS: %s: Codex URL */
		$message .= ' ' . sprintf( __( 'Please see <a href="%s">Debugging in WordPress</a> for more information.' ),
			__( 'https://codex.wordpress.org/Debugging_in_WordPress' )
		);
		/* TRANSLATORS: 1: function name, 2: link to more information, 3: version information */
		$notice = sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s' ), $function, $message, $version );

		error_log( $notice );
		self::$doing_it_wrong[ $file . ':' . $line ] = array( $notice, wp_debug_backtrace_summary( null, $bt ) );
	}
}
