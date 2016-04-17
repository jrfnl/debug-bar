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
		add_action( 'deprecated_argument_run', array( __CLASS__, 'deprecated_argument_run' ), 10, 3 );
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

		add_action( 'deprecated_function_run', array( $this, 'deprecated_function_run' ), 10, 3 );
		add_action( 'deprecated_file_included', array( $this, 'deprecated_file_included' ), 10, 4 );
		add_action( 'deprecated_argument_run',  array( $this, 'deprecated_argument_run' ),  10, 3 );

		// Silence E_NOTICE for deprecated usage.
		foreach ( array( 'function', 'file', 'argument' ) as $item )
			add_filter( "deprecated_{$item}_trigger_error", '__return_false' );
	}

	function prerender() {
		$this->set_visible(
			count( $this->deprecated_functions )
			|| count( $this->deprecated_files )
			|| count( $this->deprecated_arguments )
		);
	}

	function render() {
		echo "<div id='debug-bar-deprecated'>";
		echo '<h2><span>', __( 'Total Functions:', 'debug-bar' ), '</span>', number_format_i18n( count( $this->deprecated_functions ) ), "</h2>\n";
		echo '<h2><span>', __( 'Total Arguments:', 'debug-bar' ), '</span>', number_format_i18n( count( $this->deprecated_arguments ) ), "</h2>\n";
		echo '<h2><span>', __( 'Total Files:', 'debug-bar' ), '</span>', number_format_i18n( count( $this->deprecated_files ) ), "</h2>\n";
		if ( count( $this->deprecated_functions ) ) {
			echo '<ol class="debug-bar-deprecated-list">';
			foreach ( $this->deprecated_functions as $location => $message_stack) {
				list( $message, $stack) = $message_stack;
				echo "<li class='debug-bar-deprecated-function'>";
				echo str_replace(ABSPATH, '', $location) . ' - ' . strip_tags($message);
				echo "<br/>";
				echo $stack;
				echo "</li>";
			}
			echo '</ol>';
		}
		if ( count( $this->deprecated_files ) ) {
			echo '<ol class="debug-bar-deprecated-list">';
			foreach ( $this->deprecated_files as $location => $message_stack) {
				list( $message, $stack) = $message_stack;
				echo "<li class='debug-bar-deprecated-file'>";
				echo str_replace(ABSPATH, '', $location) . ' - ' . strip_tags($message);
				echo "<br/>";
				echo $stack;
				echo "</li>";
			}
			echo '</ol>';
		}
		if ( count( $this->deprecated_arguments ) ) {
			echo '<ol class="debug-bar-deprecated-list">';
			foreach ( $this->deprecated_arguments as $location => $message_stack) {
				list( $message, $stack) = $message_stack;
				echo "<li class='debug-bar-deprecated-argument'>";
				echo str_replace(ABSPATH, '', $location) . ' - ' . strip_tags($message);
				echo "<br/>";
				echo $stack;
				echo "</li>";
			}
			echo '</ol>';
		}
		echo "</div>";
	}

	function deprecated_function_run($function, $replacement, $version) {
		$backtrace = debug_backtrace( false );
		$bt = 4;
		// Check if we're a hook callback.
		if ( ! isset( $backtrace[4]['file'] ) && 'call_user_func_array' == $backtrace[5]['function'] ) {
			$bt = 6;
		}
		$file = $backtrace[ $bt ]['file'];
		$line = $backtrace[ $bt ]['line'];
		if ( ! is_null($replacement) ) {
			/* translators: 1: a function or file name, 2: version number, 3: alternative function or file to use. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar'), $function, $version, $replacement );
		} else {
			/* translators: 1: a function or file name, 2: version number. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'debug-bar'), $function, $version );
		}

		$this->deprecated_functions[$file.':'.$line] = array( $message, wp_debug_backtrace_summary( null, $bt ) );
	}

	function deprecated_file_included( $old_file, $replacement, $version, $message ) {
		$backtrace = debug_backtrace( false );
		$file = $backtrace[4]['file'];
		$file_abs = str_replace(ABSPATH, '', $file);
		$line = $backtrace[4]['line'];
		$message = empty( $message ) ? '' : ' ' . $message;
		if ( ! is_null( $replacement ) ) {
			/* translators: 1: a function or file name, 2: version number, 3: alternative function or file to use. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar'), $file_abs, $version, $replacement ) . $message;
		} else {
			/* translators: 1: a function or file name, 2: version number. */
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'debug-bar'), $file_abs, $version ) . $message;
		}

		$this->deprecated_files[$file.':'.$line] = array( $message, wp_debug_backtrace_summary( null, 4 ) );
	}

	function deprecated_argument_run( $function, $message, $version) {
		$backtrace = debug_backtrace( false );
		if ( $function === 'define()' ) {
			$this->deprecated_arguments[] = array( $message, '' );
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
			/* translators: 1: PHP class name, 2: PHP parent class name, 3: version number, 4: __construct() method */
			$message = sprintf( __( 'The called constructor method for %1$s in %2$s is <strong>deprecated</strong> since version %3$s! Use %4$s instead.', 'debug-bar' ),
				$class, $parent_class, $version, '<pre>__construct()</pre>' );
		} else {
			/* translators: 1: PHP class name, 2: version number, 3: __construct() method */
			$message = sprintf( __( 'The called constructor method for %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar' ),
				$class, $version, '<pre>__construct()</pre>' );
		}

		error_log( $message );
		self::$deprecated_constructors[ $file . ':' . $line ] = array( $message, wp_debug_backtrace_summary( null, $bt ) );
	}
}
