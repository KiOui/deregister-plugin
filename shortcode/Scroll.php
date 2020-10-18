<?php

class Scroll {

    /**
     * Includes scripts utilised by the search bar
     */
	function include_scripts() {
		wp_enqueue_script("scroll", "/wp-content/plugins/deregister-plugin/js/scroll.js", array("jquery"));
	}

	function include_styles() {
		wp_enqueue_style("subscription_all_style", "/wp-content/plugins/deregister-plugin/css/main.css");
		wp_enqueue_style("subscription_scroll", "/wp-content/plugins/deregister-plugin/css/scroll.css");
	}

    /**
     * Creates the search HTML code
     * @return string: the HTML code
     */
	function create_scroll() {
		$this->include_scripts();
		$this->include_styles();
		return '
		<div class="scroll-button" onclick="scrollToY(document.body.scrollHeight, 5, \'easeInOutQuint\');";><i class="fas fa-arrow-circle-down"></i></div>';
	}
}