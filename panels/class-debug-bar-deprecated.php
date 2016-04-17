<?php
// Alot of this code is massaged from Andrew Nacin's log-deprecated-notices plugin

class Debug_Bar_Deprecated extends Debug_Bar_Panel {
	static $deprecated_functions = array();
	static $deprecated_files = array();
	static $deprecated_arguments = array();

	static function start_logging() {
		add_action( 'deprecated_function_run', array( __CLASS__, 'deprecated_function_run' ), 10, 3 );
		add_action( 'deprecated_file_included', array( __CLASS__, 'deprecated_file_included' ), 10, 4 );
		add_action( 'deprecated_argument_run',  array( __CLASS__, 'deprecated_argument_run' ),  10, 3 );

		// Silence E_NOTICE for deprecated usage.
		foreach ( array( 'function', 'file', 'argument' ) as $item ) {
			add_filter( "deprecated_{$item}_trigger_error", '__return_false' );
		}
	}

	static function stop_logging() {
		remove_action( 'deprecated_function_run', array( __CLASS__, 'deprecated_function_run' ), 10 );
		remove_action( 'deprecated_file_included', array( __CLASS__, 'deprecated_file_included' ), 10 );
		remove_action( 'deprecated_argument_run',  array( __CLASS__, 'deprecated_argument_run' ),  10 );

		// Don't silence E_NOTICE for deprecated usage.
		foreach ( array( 'function', 'file', 'argument' ) as $item ) {
			remove_filter( "deprecated_{$item}_trigger_error", '__return_false' );
		}
	}

	function init() {
		$this->title( __('Deprecated', 'debug-bar') );
	}

	function prerender() {
		$this->set_visible(
			count( self::$deprecated_functions )
			|| count( self::$deprecated_files )
			|| count( self::$deprecated_arguments )
		);
	}

	function render() {
		echo '<div id="debug-bar-deprecated">';

		$this->render_title( __( 'Total Functions:', 'debug-bar' ), count( self::$deprecated_functions ) );
		$this->render_title( __( 'Total Files:', 'debug-bar' ), count( self::$deprecated_files ) );
		$this->render_title( __( 'Total Arguments:', 'debug-bar' ), count( self::$deprecated_arguments ) );

		$this->render_list( self::$deprecated_functions, 'deprecated-function' );
		$this->render_list( self::$deprecated_files, 'deprecated-file' );
		$this->render_list( self::$deprecated_arguments, 'deprecated-argument' );

		echo '</div>';
	}

	function render_title( $title, $count ) {
		echo '<h2><span>', $title, '</span>', absint( $count ), "</h2>\n";
	}

	function render_list( $calls, $class ) {
		if ( count( $calls ) ) {
			echo '<ol class="debug-bar-deprecated-list">';
			foreach ( $calls as $location => $message_stack ) {
				list( $message, $stack ) = $message_stack;

				echo '
				<li class="debug-bar-', $class, '">',
				str_replace( ABSPATH, '', $location ), ' - ', strip_tags( $message ),
				'<br/>',
				$stack,
				'</li>';
			}
			echo '</ol>';
		}
	}

	static function deprecated_function_run($function, $replacement, $version) {
		$backtrace = debug_backtrace( false );
		$bt = 4;
		// Check if we're a hook callback.
		if ( ! isset( $backtrace[4]['file'] ) && 'call_user_func_array' == $backtrace[5]['function'] ) {
			$bt = 6;
		}
		$file = $backtrace[ $bt ]['file'];
		$line = $backtrace[ $bt ]['line'];
		if ( ! is_null($replacement) ) {
			/* translators: %1$s is a function or file name, %2$s a version number, %3$s an alternative function or file to use. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar'), $function, $version, $replacement );
		} else {
			/* translators: %1$s is a function or file name, %2$s a version number. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'debug-bar'), $function, $version );
		}

		self::$deprecated_functions[ $file . ':' . $line ] = array( $message, wp_debug_backtrace_summary( null, $bt ) );
	}

	static function deprecated_file_included( $old_file, $replacement, $version, $message ) {
		$backtrace = debug_backtrace( false );
		$file = $backtrace[4]['file'];
		$file_abs = str_replace(ABSPATH, '', $file);
		$line = $backtrace[4]['line'];
		$message = empty( $message ) ? '' : ' ' . $message;
		if ( ! is_null( $replacement ) ) {
			/* translators: %1$s is a function or file name, %2$s a version number, %3$s an alternative function or file to use. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar'), $file_abs, $version, $replacement ) . $message;
		} else {
			/* translators: %1$s is a function or file name, %2$s a version number. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'debug-bar'), $file_abs, $version ) . $message;
		}

		self::$deprecated_files[ $file . ':' . $line ] = array( $message, wp_debug_backtrace_summary( null, 4 ) );
	}

	static function deprecated_argument_run( $function, $message, $version) {
		$backtrace = debug_backtrace( false );
		if ( $function === 'define()' ) {
			self::$deprecated_arguments[] = array( $message, '' );
			return;
		}

		$bt = 4;
		if ( ! isset( $backtrace[4]['file'] ) && 'call_user_func_array' == $backtrace[5]['function'] ) {
			$bt = 6;
		}
		$file = $backtrace[ $bt ]['file'];
		$line = $backtrace[ $bt ]['line'];
		if ( ! is_null( $message ) ) {
			$message = sprintf( __('%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s! %3$s'), $function, $version, $message );
		} else {
			$message = sprintf( __('%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s with no alternative available.'), $function, $version );
		}

		self::$deprecated_arguments[ $file . ':' . $line ] = array( $message, wp_debug_backtrace_summary( null, $bt ) );
	}
}
