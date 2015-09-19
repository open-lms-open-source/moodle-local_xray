<?php
defined('MOODLE_INTERNAL') or die();
require_once($CFG->dirroot . '/local/xray/controller/reports.php');

/**
 * Report Discussion Individual forum.
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_discussionreportindividualforum extends local_xray_controller_reports {

    /**
     * Forum id
     * @var integer
     */
    private $forumid;

    /**
     * Course module id.
     * @var integer
     */
    private $id;

    public function init() {
        parent::init();
        global $DB;
        $this->cmid = required_param('cmid', PARAM_ALPHANUM); // Cmid of forum.
        $this->forumid = required_param('forum', PARAM_ALPHANUM);
    }

    public function view_action() {

        global $PAGE, $USER, $DB;

        $title = get_string($this->name, $this->component);
        $PAGE->set_title($title);
        $this->heading->text = $title;

        // Add title to breadcrumb.
        $forumname = $DB->get_field('forum', 'name', array("id" => $this->forumid));
        $PAGE->navbar->add($forumname, new moodle_url("/mod/forum/view.php",
            array("id" => $this->cmid)));

        $PAGE->navbar->add($title);
        $output = "";

        try {
            $report = "discussion";
            $response = \local_xray\api\wsapi::course($this->courseid, $report, "forum/" . $this->forumid);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();

            } else {

                // Show graphs.
                $output .= $this->output->inforeport($response->reportdate,
                    null,
                    $DB->get_field('course', 'fullname', array("id" => $this->courseid)));

                $output .= $this->wordshistogram($response->elements->wordHistogram);
                $output .= $this->socialstructure($response->elements->socialStructure);
                $output .= $this->wordcloud($response->elements->wordcloud);

            }
        } catch (exception $e) {
            print_error('error_xray', $this->component, '', null, $e->getMessage());
        }

        return $output;
    }

    /**
     * Words Histogram
     *
     */
    private function wordshistogram($element) {

        $output = "";
        $output .= $this->output->discussionreportindividualforum_wordshistogram($element);
        return $output;
    }

    /**
     * Social Structure
     *
     */
    private function socialstructure($element) {

        $output = "";
        $output .= $this->output->discussionreportindividualforum_socialstructure($element);
        return $output;
    }

    /**
     * Wordcloud
     *
     */
    private function wordcloud($element) {

        $output = "";
        $output .= $this->output->discussionreportindividualforum_wordcloud($element);
        return $output;
    }
}
