<?php

namespace block_google_search_form\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;
class renderer extends plugin_renderer_base {
    public function render_google_search_form(renderable $searchform) {
        return $this->render_from_template('block_google_search_form/simple_html_form', $searchform->export_for_template($this));
    }
}