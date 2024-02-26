<?php
namespace block_google_search_form\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class simple_html_form extends \moodleform
{
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'myfield', 'My Field');
        $mform->setType('myfield', PARAM_TEXT);
        $mform->addRule('myfield', 'Please enter a value', 'required', null, 'client');

        $this->add_action_buttons();
    }

    // Define custom validation logic
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['myfield'])) {
            $errors['myfield'] = 'Please enter a value';
        }
        return $errors;
    }

    // Define custom processing logic
    function submit($data) {
        global $OUTPUT;

        // Check if the form is being submitted
        if (isset($data['submitbutton'])) {
            // Retrieve the search term from the submitted form data
            $search_term = $data['search_term'];

            // Construct the request to the Google API with the search term
            $api_key = get_config('block_google_search_form', 'google_search_apikey');
            $search_engine_id = get_config('block_google_search_form', 'google_search_searchengineid');
            $url = "https://www.googleapis.com/customsearch/v1?key={$api_key}&cx={$search_engine_id}&q={$search_term}";

            // Make API request
            $response = file_get_contents($url);

            // Check if response was successful
            if ($response !== false) {
                // Decode the JSON response
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
                    $message = $OUTPUT->notification('<div class="form-search-results">' . $displayedResults . '</div>', 'success');
                    echo $message;
                } else {
                    // Handle case where items are not present in the response
                    $message = $OUTPUT->notification('No items found in API response', 'error');
                    echo $message;
                }
            } else {
                // Handle case where API request failed
                $message = $OUTPUT->notification('Failed to retrieve data from API', 'error');
                echo $message;
            }
        }
    }

}