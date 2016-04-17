<?php
// Alot of this code is massaged from Andrew Nacin's log-deprecated-notices plugin

class Debug_Bar_Deprecated extends Debug_Bar_Panel {
	static $deprecated_functions = array();
	static $deprecated_files = array();
	static $deprecated_arguments = array();
	static $deprecated_constructors = array();

	static function start_logging() {
		add_action( 'deprecated_function_run', array( __CLASS__, 'deprecated_function_run' ), 10, 3 );
		add_action( 'deprecated_file_included', array( __CLASS__, 'deprecated_file_included' ), 10, 4 );
		add_action( 'deprecated_argument_run',  array( __CLASS__, 'deprecated_argument_run' ),  10, 3 );
		add_action( 'deprecated_constructor_run',  array( __CLASS__, 'deprecated_constructor_run' ),  10, 3 );

		// Silence E_NOTICE for deprecated usage.
		foreach ( array( 'function', 'file', 'argument', 'constructor' ) as $item ) {
			add_filter( "deprecated_{$item}_trigger_error", '__return_false' );
		}
	}

	static function stop_logging() {
		remove_action( 'deprecated_function_run', array( __CLASS__, 'deprecated_function_run' ), 10 );
		remove_action( 'deprecated_file_included', array( __CLASS__, 'deprecated_file_included' ), 10 );
		remove_action( 'deprecated_argument_run',  array( __CLASS__, 'deprecated_argument_run' ),  10 );
		remove_action( 'deprecated_constructor_run',  array( __CLASS__, 'deprecated_constructor_run' ),  10 );

		// Don't silence E_NOTICE for deprecated usage.
		foreach ( array( 'function', 'file', 'argument', 'constructor' ) as $item ) {
			remove_filter( "deprecated_{$item}_trigger_error", '__return_false' );
		}
	}

	function init() {
		$this->title( __('Deprecated', 'debug-bar') );
		$this->set_visible( false );
	}

	function is_visible() {
		return ( $this->get_total() > 0 );
	}

	function prerender() {
		$total = $this->get_total();

		if ( $total > 0 ) {
			$this->title( $this->title() . '<span class="debug-bar-issue-count">' . absint( $total ) . '</span>' );
		}
	}

	function get_total() {
		return count( self::$deprecated_functions ) + count( self::$deprecated_files ) + count( self::$deprecated_arguments ) + count( self::$deprecated_constructors );
	}

	function debug_bar_classes( $classes ) {
		if ( $this->get_total() > 0 ) {
			$classes[] = 'debug-bar-notice-summary';
		}
		return $classes;
	}

	function render() {
		echo '<div id="debug-bar-deprecated">';

		$this->render_title( __( 'Total Functions:', 'debug-bar' ), count( self::$deprecated_functions ) );
		$this->render_title( __( 'Total Files:', 'debug-bar' ), count( self::$deprecated_files ) );
		$this->render_title( __( 'Total Arguments:', 'debug-bar' ), count( self::$deprecated_arguments ) );
		$this->render_title( __( 'Total Constructors:', 'debug-bar' ), count( self::$deprecated_constructors ) );

		$this->render_list( self::$deprecated_functions, 'deprecated-function' );
		$this->render_list( self::$deprecated_files, 'deprecated-file' );
		$this->render_list( self::$deprecated_arguments, 'deprecated-argument' );
		$this->render_list( self::$deprecated_constructors, 'deprecated-constructor' );

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
			/* TRANSLATORS: 1: a function or file name, 2: version number, 3: alternative function or file to use. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar'), $function, $version, $replacement );
		} else {
			/* TRANSLATORS: 1: a function or file name, 2: version number. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'debug-bar'), $function, $version );
		}

		error_log( $message );
		self::$deprecated_functions[ $file . ':' . $line ] = array( $message, wp_debug_backtrace_summary( null, $bt ) );
	}

	static function deprecated_file_included( $old_file, $replacement, $version, $message ) {
		$backtrace = debug_backtrace( false );
		$file = $backtrace[4]['file'];
		$file_abs = str_replace(ABSPATH, '', $file);
		$line = $backtrace[4]['line'];
		$message = empty( $message ) ? '' : ' ' . $message;
		if ( ! is_null( $replacement ) ) {
			/* TRANSLATORS: 1: a function or file name, 2: version number, 3: alternative function or file to use. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar'), $file_abs, $version, $replacement ) . $message;
		} else {
			/* TRANSLATORS: 1: a function or file name, 2: version number. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'debug-bar'), $file_abs, $version ) . $message;
		}

		error_log( $message );
		self::$deprecated_files[ $file . ':' . $line ] = array( $message, wp_debug_backtrace_summary( null, 4 ) );
	}

	static function deprecated_argument_run( $function, $message, $version ) {
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
			/* TRANSLATORS: 1: a function name, 2: a version number, 3: information about an alternative. */
			$message = sprintf( __('%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s! %3$s'), $function, $version, $message );
		} else {
			/* TRANSLATORS: 1: a function name, 2: a version number. */
			$message = sprintf( __('%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s with no alternative available.'), $function, $version );
		}

		error_log( $message );
		self::$deprecated_arguments[ $file . ':' . $line ] = array( $message, wp_debug_backtrace_summary( null, $bt ) );
	}

	static function deprecated_constructor_run( $class, $version, $parent_class = '' ) {
		$backtrace = debug_backtrace( false );
		$bt = 4;
		if ( ! isset( $backtrace[4]['file'] ) && 'call_user_func_array' == $backtrace[5]['function'] ) {
			$bt = 6;
		}
		$file = $backtrace[ $bt ]['file'];
		$line = $backtrace[ $bt ]['line'];

		if ( ! empty( $parent_class ) ) {
			/* TRANSLATORS: 1: PHP class name, 2: PHP parent class name, 3: version number, 4: __construct() method */
			$message = sprintf( __( 'The called constructor method for %1$s in %2$s is <strong>deprecated</strong> since version %3$s! Use %4$s instead.', 'debug-bar' ),
				$class, $parent_class, $version, '<pre>__construct()</pre>' );
		} else {
			/* TRANSLATORS: 1: PHP class name, 2: version number, 3: __construct() method */
			$message = sprintf( __( 'The called constructor method for %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar' ),
				$class, $version, '<pre>__construct()</pre>' );
		}

		error_log( $message );
		self::$deprecated_constructors[ $file . ':' . $line ] = array( $message, wp_debug_backtrace_summary( null, $bt ) );
	}
}
