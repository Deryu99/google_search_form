<?php
namespace block_google_search_form\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class simple_html_form extends \moodleform
{
    public function definition()
    {
        global $PAGE;
        $mform = $this->_form;

        $mform->addElement('text', 'config_search_term', get_string('enter_search_term', 'block_google_search_form'));
        $mform->setType('config_search_term', PARAM_TEXT);
        $mform->setDefault('config_search_term', 'Moodle Blocks');

        // Define JavaScript function separately
        $ajax_script = <<<EOD
function handleFormSubmission(form) {
    var search_term = form.elements['config_search_term'].value;
    var api_key = 'AIzaSyBBHp0O3-WUfzwQc0u9qFzgpJYsCsLukVw';
    var search_engine_id = '53fce5df31b884201';
    var url = "https://www.googleapis.com/customsearch/v1?key=" + api_key + "&cx=" + search_engine_id + "&q=" + search_term;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('{$this->instance->id}').innerHTML = formatResults(data.results);
            } else {
                console.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching search results:', error);
        });
}

function formatResults(results) {
    var html = '<div class="form-search-results">';
    results.forEach(result => {
        html += '<div class="form-search-result">';
        html += '<h3>' + result.title + '</h3>';
        html += '<p>' + result.snippet + '</p>';
        html += '</div>';
    });
    html += '</div>';
    return html;
}

// Define the handleFormSubmission function in the global scope
window.handleFormSubmission = handleFormSubmission;

// Prevent form submission on Enter key press
document.getElementById('config_search_term').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
EOD;

        // Add JavaScript to the head section of the HTML document
        $PAGE->requires->js_init_code($ajax_script);

        // Add a submit button with the onclick event calling the JavaScript function
        $mform->addElement('button', 'submit_button', 'Submit', array('onclick' => 'handleFormSubmission(this.form); return false;'));
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}