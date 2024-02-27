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

    public function get_content() {
        global $CFG, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        if (empty($this->instance)) {
            $this->content->text   = '';
            return $this->content;
        }

        // Instantiate the renderer for your block
        $output = $this->page->get_renderer('block_google_search_form');

        // Create an instance of your form class
        $searchform = new \block_google_search_form\output\simple_html_form($this->page->course->id);

        // Check if the form has been submitted
        $submitted = optional_param('submitbutton', '', PARAM_ALPHA);

        // Check if the form has been submitted and get search results
        $search_results = null; // Initialize search results variable

        error_log('This is a test that till here is working');

        if ($submitted && $submitted === 'submit') {
            // Get form data from the submitted form
            $data = $this->extract_data($searchform);
            // Call the submit method to process the form data
            $search_results = $searchform->submit($data);
        }

        // Render the form template with the form data and search results
        $templatecontext = [
            'form' => $searchform->export_for_template($output),
            'search_results' => $search_results
        ];

        $this->content->text = $output->render_from_template('block_google_search_form/simple_html_form', $templatecontext);

        return $this->content;
    }

    private function extract_data($form) {
        $data = new stdClass();
        foreach ($form->export_for_template($this) as $key => $value) {
            if (property_exists($form, $key)) {
                $data->$key = $value;
            }
        }
        return $data;
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
