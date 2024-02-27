<?php
namespace block_google_search_form\output;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Simple html form renderable class.
 *
 * @package    block_google_search_form
 * @copyright  2024 Daniel Castro
 */
class simple_html_form implements renderable, templatable {

    /** @var int The course ID. */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param int $courseid The course ID.
     */
    function __construct($courseid) {
        $this->courseid = $courseid; // Store course ID in form object
        //$this->actionurl = new moodle_url('/mod/forum/search.php', ['id' => $courseid]);
    }

    public function export_for_template(renderer_base $output) {
        $data = [
            //'action' => '',  Specify the form action URL if needed
            //'helpicon' => '',  Add help icon if needed
            'hiddenfields' => (object) ['name' => 'courseid', 'value' => $this->courseid],
            'inputname' => get_string('enter_search_term', 'block_google_search_form'), // Name of the input field
            'searchstring' => get_string('search'), // Label for the input field
            'results' => $this->get_search_results() // Pass search results to the template
        ];
        return $data;
    }

    // Define custom processing logic
    public function submit($data) {
        global $OUTPUT;
        // Check if the form is being submitted
        if (!empty($data['submitbutton']) && !empty($data['search_term'])) {
            // Retrieve and sanitize the search term
            $search_term = trim($data['search_term']);
            $search_term = filter_var($search_term, FILTER_SANITIZE_STRING);

            // Make the API request
            $api_key = get_string('google_search_apikey', 'block_google_search_form');
            $search_engine_id = get_string('google_search_searchengineid', 'block_google_search_form');
            $response = $this->makeApiRequest($api_key, $search_engine_id, $search_term);

            // Process the API response
            if ($response !== false) {
                return $this->processApiResponse($response);
            } else {
                return $this->handleApiFailure();
            }
        }
        return [];
    }

    // Make API request using cURL
    private function makeApiRequest($api_key, $search_engine_id, $search_term) {
        $url = "https://www.googleapis.com/customsearch/v1?key={$api_key}&cx={$search_engine_id}&q={$search_term}";
        $response = file_get_contents($url);
        return $response;
    }

    // Process API response
    private function processApiResponse($response) {
        $decoded_response = json_decode($response);
        if ($decoded_response !== null && property_exists($decoded_response, 'items')) {
            return $decoded_response->items;
        } else {
            return;
        }
    }

    // Display search results
    private function displaySearchResults($items) {
        $displayedResults = '';
        foreach ($items as $item) {
            $result_html = '<div class="form-search-result">';
            $result_html .= '<h3>' . htmlspecialchars($item->title) . '</h3>';
            $result_html .= '<p>' . htmlspecialchars($item->snippet) . '</p>';
            $result_html .= '</div>';
            $displayedResults .= $result_html;
        }
        return $this->notification('<div class="form-search-results">' . $displayedResults . '</div>', 'success');
    }

    private function handleApiFailure() {
        return $this->notification('Failed to retrieve data from API', 'error');
    }

    private function notification($message, $type) {
        global $OUTPUT;
        return $OUTPUT->notification($message, $type);
    }

    private function get_search_results() {
        $data = $_POST;
        if (!empty($data['submitbutton']) && !empty($data['search_term'])) {
            $search_term = trim($data['search_term']);
            $api_key = get_config('block_google_search_form', 'google_search_apikey');
            $search_engine_id = get_config('block_google_search_form', 'google_search_searchengineid');
            $response = $this->makeApiRequest($api_key, $search_engine_id, $search_term);
            if ($response !== false) {
                return $this->processApiResponse($response);
            } else {
                return $this->handleApiFailure();
            }
        }
        return '';
    }
}