<?php

/**
 * Register all actions and filters for the plugin
 *
 * @package    Credipost
 * @subpackage Credipost/includes
 */

class Credipost_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @var array $actions
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @var array $filters
	 */
	protected $filters;

	/**
	 * The array of shortcodes registered with WordPress.
	 *
	 * @var array $shortcodes
	 */
	protected $shortcodes;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 */
	public function __construct() {
		$this->actions    = array();
		$this->filters    = array();
		$this->shortcodes = array();
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @param string $hook          The name of the WordPress action.
	 * @param object $component     Reference to the instance of the object.
	 * @param string $callback      The callback function name.
	 * @param int    $priority      Optional. Priority. Default 10.
	 * @param int    $accepted_args Optional. Number of arguments. Default 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @param string $hook          The name of the WordPress filter.
	 * @param object $component     Reference to the instance of the object.
	 * @param string $callback      The callback function name.
	 * @param int    $priority      Optional. Priority. Default 10.
	 * @param int    $accepted_args Optional. Number of arguments. Default 1.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new shortcode to the collection.
	 *
	 * @param string $tag       The name of the shortcode.
	 * @param object $component Reference to the instance of the object.
	 * @param string $callback  The callback function name.
	 */
	public function add_shortcode( $tag, $component, $callback ) {
		$this->shortcodes[] = array(
			'tag'       => $tag,
			'component' => $component,
			'callback'  => $callback,
		);
	}

	/**
	 * Utility function to add an item to the collection.
	 *
	 * @param array  $hooks
	 * @param string $hook
	 * @param object $component
	 * @param string $callback
	 * @param int    $priority
	 * @param int    $accepted_args
	 * @return array
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Register the filters, actions, and shortcodes with WordPress.
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->shortcodes as $shortcode ) {
			add_shortcode( $shortcode['tag'], array( $shortcode['component'], $shortcode['callback'] ) );
		}
	}
}
