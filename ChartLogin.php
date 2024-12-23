<?php
namespace Stanford\ChartLogin\ChartLogin;

use Message;
use REDCap;

require_once "emLoggerTrait.php";


/**
 * Class ChartLogin
 * @package Stanford\ChartLogin\ChartLogin
 * @property string $record
 * @property \Stanford\ChartAppointmentScheduler\ChartAppointmentScheduler $scheduler
 * @property \Project $project
 */
class ChartLogin extends \ExternalModules\AbstractExternalModule
{

    use emLoggerTrait;

    public function __construct()
    {
        parent::__construct();

        if (isset($_GET['pid'])) {

            $em = $this->getProjectSetting('redirect-em-name');
            if ($em) {
                try {
                    $this->setScheduler(\ExternalModules\ExternalModules::getModuleInstance($em));
                    if ($this->scheduler === null) {
                        $this->emError("Scheduler module instance could not be initialized.");
                    }
                } catch (\Exception $e) {
                    $this->setScheduler(null);
                }
            }

            global $Proj;

            $this->setProject($Proj);
        }
    }

    private $project;

    private $recordId;

    private $scheduler;

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
    )
    {

        $loginInstrument = $this->getProjectSetting('login-instrument');
        $loginEventId = $this->getProjectSetting('login-instrument-event');


        // Handle a redirect to the main project
        if ($instrument == $loginInstrument && $event_id = $loginEventId) {

            $this->setRecordId($record);
            $this->scheduleLogin();
        } else {
            $this->emDebug("Do nothing");
        }
    }


    private function scheduleLogin()
    {
        // Insert CSS (hide the submit button)
        echo '<link rel="stylesheet" type="text/css" href="' .
            $this->getUrl('asset/css/authentication.css', true, true) .
            '"/>';

        // Include HTML
        include "pages/authentication.php";


    }

    public function verifyCookie($name)
    {
        if (!isset($_COOKIE[$name])) {
            return false;
        }

        // when manager hits user page. they must be logged in and have right permission on redcap.
        if (defined('USERID') && isset($_GET[$this->getProjectSetting('validation-field')]) && self::isUserHasManagePermission()) {
            $param = array(
                'project_id' => $this->getProjectId(),
                'return_format' => 'array'
            );
            $records = REDCap::getData($param);
            foreach ($records as $id => $record) {
                if (filter_var($_GET[$this->getProjectSetting('validation-field')],
                        FILTER_SANITIZE_STRING) == $record[$this->getProjectSetting('login-instrument-event')][$this->getProjectSetting('validation-field')]) {
                    $this->setUserCookie('login',
                        $this->generateUniqueCodeHash(filter_var($_GET[$this->getProjectSetting('validation-field')],
                            FILTER_SANITIZE_STRING)));
                    return array('id' => $id, 'record' => $record);
                }
            }
        } else {
            $param = array(
                'project_id' => $this->getProjectId(),
                'return_format' => 'array',
                'events' => [$this->getProjectSetting('login-instrument-event')]
            );
            $records = REDCap::getData($param);
            foreach ($records as $id => $record) {
                $hash = $this->generateUniqueCodeHash($record[$this->getProjectSetting('login-instrument-event')][$this->getProjectSetting('validation-field')]);
                if ($hash == $_COOKIE[$name] && $id == $this->getRecordId()) {
                    return array('id' => $id, 'record' => $record);
                }
            }
            return false;
        }
        return false;
    }

    public function verifyUser($value, $recordId)
    {
        $dateValue = \DateTime::createFromFormat("m-d-Y", $value);
        //$filter = "[newuniq] = '" . strtoupper($newuniq) . "' AND [zipcode_abs] = '" . $zipcode_abs . "'";
        $param = array(
            'project_id' => $this->getProjectId(),
            'return_format' => 'array',
            'record' => [$recordId],
            'events' => $this->getProjectSetting('login-instrument-event')
        );
        $data = REDCap::getData($param);
        if ($this->getProjectSetting('input-fields') != '') {
            $validation_fields = json_decode($this->getProjectSetting('input-fields'), true);
        } else {
            $validation_fields = array('dob', 'zsfg_dob', 'birthdate');
        }


        $withdraw = $data[$recordId][$this->getProjectSetting('login-instrument-event')]['withdraw'];
        if (empty($data) || $withdraw) {
            return false;
        } else {
            foreach ($validation_fields as $field) {
                // if user is loggin in with date
                if ($dateValue != null) {
                    $d = ($data[$recordId][$this->getProjectSetting('login-instrument-event')][$field]);
                    if ($d != '') {
                        $d = \DateTime::createFromFormat("Y-m-d",
                            $data[$recordId][$this->getProjectSetting('login-instrument-event')][$field]);
                        if ($d->format('Y-m-d') == $dateValue->format('Y-m-d')) {
                            $this->setUserCookie('login',
                                $this->generateUniqueCodeHash($data[$recordId][$this->getProjectSetting('login-instrument-event')][$this->getProjectSetting('validation-field')]));
                            return $this->getSchedulerLink($recordId);
                        }
                    }
                }else{
                    $v = ($data[$recordId][$this->getProjectSetting('login-instrument-event')][$field]);
                    if ($v == $value) {
                            $this->setUserCookie('login',
                                $this->generateUniqueCodeHash($data[$recordId][$this->getProjectSetting('login-instrument-event')][$this->getProjectSetting('validation-field')]));
                            return $this->getSchedulerLink($recordId);
                        }
                }


            }
        }
    }

    public function getSchedulerLink($recordId = '')
    {
        $scheduler = $this->getScheduler();

        if ($scheduler === null) {
            $this->emError("Scheduler instance is null when trying to get URL.");
            return null; // Or handle the error as needed
        }

        return $scheduler->getUrl('src/user', true,
                true) . '&projectid=' . $this->getProjectId() . '&pid=' . $this->getProjectId() . '&NOAUTH&id=' . $recordId;
    }

    public function redirectToScheduler($recordId)
    {
        $url = $this->getSchedulerLink($recordId);
        if ($url) {
            header("Location: $url");
        } else {
            $this->emError("Cannot redirect to scheduler; URL is null.");
            // Handle the error, possibly show a user-friendly message
        }
        $this->exitAfterHook();
    }

    /**
     * @return mixed
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * @param mixed $recordId
     */
    public function setRecordId()
    {
        $temp = func_get_args();
        $recordId = $temp[0];
        $this->recordId = $recordId;
    }

    public function setUserCookie($name, $value, $time = 86406)
    {
        #day
        setcookie($name, $value, time() + $time, '/');
    }

    public function generateUniqueCodeHash($newuniq)
    {
        return hash('sha256', $newuniq);
    }

    public function getScheduler()
    {
        return $this->scheduler;
    }

    public function setScheduler($scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * @return \Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Project $project
     */
    public function setProject(\Project $project)
    {
        $this->project = $project;
    }


}
