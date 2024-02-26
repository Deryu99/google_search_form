<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block form_google_search is defined here.
 *
 * @package     block_google_search_form
 * @copyright   2024 Daniel Castro
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_google_search_form extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_google_search_form');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content()
    {
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // Instantiate the search form
        $search_form = new \block_google_search_form\form\simple_html_form();

        if ($search_form->is_cancelled()) {
            // Handle cancel operation if applicable
        } else if ($search_form->get_data()) {
            if ($form_data = $search_form->get_data()) {
                // Include JavaScript code here to handle AJAX request


                // Check if there is a response from the AJAX request
                if (isset($_POST['ajax_response'])) {
                    // Retrieve the response from the AJAX request
                    $response = $_POST['ajax_response'];

                    // Process the response here
                    // You can decode the JSON response and handle it accordingly
                    $decoded_response = json_decode($response);

                    // Check if decoding was successful and if items exist in the response
                    if ($decoded_response !== null && property_exists($decoded_response, 'items')) {
                        $items = $decoded_response->items;
                        $displayedResults = '';

                        // Iterate through each item and extract relevant fields
                        foreach ($items as $item) {
                            // Construct HTML for each result
                            $result_html = '<div class="form-search-result">';
                            $result_html .= '<h3>' . $item->title . '</h3>';
                            $result_html .= '<p>' . $item->snippet . '</p>';
                            $result_html .= '</div>';

                            // Append to the overall HTML string
                            $displayedResults .= $result_html;
                        }

                        // Display relevant aspects of JSON
                        $this->content->text = '<div class="form-search-results">' . $displayedResults . '</div>';
                    } else {
                        // Handle case where items are not present in the response
                        $this->content->text = 'No items found in AJAX response';
                    }
                } else {
                    // If there is no response from the AJAX request, display a message
                    $this->content->text = 'No response received from AJAX request';
                }
            }
        } else {
            // Display the form
            $this->content->text = $search_form->render();
        }
        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {
        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_google_search_form');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats()
    {
        return array(
            'all' => false,
            'course-view' => true,
            'course-view-social' => false,
        );
    }

    public function _self_test() {
        return true;
    }
}
