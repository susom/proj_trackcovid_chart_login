<?php
namespace Stanford\TrackCovid\ProjChart;

use Message;
use REDCap;

require_once "emLoggerTrait.php";

class ProjChart extends \ExternalModules\AbstractExternalModule
{

    use emLoggerTrait;

    public function __construct() {
        parent::__construct();
    }


    /**
     * Hijack the public survey page to present our custom code entry page
     */
    function redcap_survey_page_top(
        $project_id,
        $record = null,
        $instrument,
        $event_id,
        $group_id = null,
        $survey_hash,
        $response_id = null,
        $repeat_instance = 1
    ) {

        $loginInstrument = $this->getProjectSetting('login-instrument');
        $loginEventId    = $this->getProjectSetting('login-instrument-event');

        // Handle a redirect to the main project
        if ($instrument == $loginInstrument && $event_id = $loginEventId) {
            $this->emDebug("Let's do this");
            $this->scheduleLogin();
        } else {
            $this->emDebug("Do nothing");
        }
    }


    private function scheduleLogin() {
        // Insert CSS (hide the submit button)
        echo '<link rel="stylesheet" type="text/css" href="' .
            $this->getUrl('asset/css/authentication.css', true, true) .
            '"/>';

        // Include HTML
        include "pages/authentication.php";

        // Include js
        echo '<script src="' . $this->getUrl('asset/js/authentication.js', true, true) . '"></script>';



    }


}
