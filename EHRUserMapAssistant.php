<?php

namespace Stanford\EHRUserMapAssistant;

use ExternalModules\ExternalModules;

require_once "emLoggerTrait.php";


# test comment.
class EHRUserMapAssistant extends \ExternalModules\AbstractExternalModule
{

    use emLoggerTrait;

    const SUCCESS = 1;
    const HASH_EXPIRED = 2;
    const OTHER = 9;
    /**
     * @var int
     */
    private $mapperProjectId = 0;

    /**
     * @var bool
     */
    private $loggedIn = false;

    private $ehrContext = false;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var array
     */
    public $redcapData = [];

    /**
     * @var string
     */
    private $customJS = '';

    /**
     * @var string
     */
    private $customCSS = '';

    /**
     * @var bool
     */
    private $suppressForm = false;

    /**
     * @var string
     */
    private $header = '';

    /**
     * @var string
     */
    private $formHeader = '';
    /**
     * @var bool
     */
    private $isSurvey = false;

    /**
     * @var bool
     */
    private $isAPI = false;

    /**
     * @var bool
     */
    private $isNoAuth = false;

    public function __construct()
    {
        parent::__construct();
        // Other code to run when object is instantiated

        if (ExternalModules::getSystemSetting($this->PREFIX, 'mapper-project-id-attempts')) {
            $this->setMapperProjectId(ExternalModules::getSystemSetting($this->PREFIX, 'mapper-project-id-attempts'));
        }

        if (ExternalModules::getSystemSetting($this->PREFIX, 'suppress-table-login-option')) {
            $this->setSuppressForm(true);
            $this->setCustomJS(ExternalModules::getSystemSetting($this->PREFIX, 'custom-js') ?: '');
            $this->setCustomCSS(ExternalModules::getSystemSetting($this->PREFIX, 'custom-css') ?: '');
        }

        $this->setHeader(ExternalModules::getSystemSetting($this->PREFIX, 'link-page-header'));


        $this->setFormHeader(ExternalModules::getSystemSetting($this->PREFIX, 'login-form-header'));

        /**
         * find if logged in or not.
         */
        if (defined('USERID') && USERID != null) {
            $this->setLoggedIn(true);
        }

        /**
         * find if we are in Epic content
         */
        preg_match('/\/ehr\.php/m', $_SERVER['SCRIPT_NAME'], $matches, PREG_OFFSET_CAPTURE);
        // TODO for prod we want more validation by checking some URL params to confirm we are in launch mode.
        //if(!empty($matches)){
        if (!empty($matches)) {
            $this->setEhrContext(true);
        }

        if (strpos($_SERVER['REQUEST_URI'], 'surveys/') !== false && isset($_GET['s'])) {
            $this->setIsSurvey(true);
        }

        if (strpos($_SERVER['REQUEST_URI'], 'api/') !== false) {
            $this->setIsAPI(true);
        }

        if (isset($_GET['NOAUTH'])) {
            $this->setIsNoAuth(true);
        }
    }

    function redcap_every_page_before_render($project_id = '')
    {
        try {
            if ($this->isEhrContext()) {
                if (!$this->isLoggedIn()) {
                    $this->createLoginAttempt();
                }

            }
        } catch (\Exception $e) {
//            \REDCap::logEvent($e->getMessage());
            $this->errors[] = $e;
            $this->includeFile('views/errors.php');
        }
    }

    function redcap_every_page_top($project_id = '')
    {
        try {

            if ((!$this->isLoggedIn() && $this->getLoginMethod() == 'shibboleth_table') || ($this->isAPI() && !$this->isNoAuth()) && !$this->isSurvey() && !$this->isNoAuth()) {
                $this->includeFile('views/form.php');
            }
        } catch (\Exception $e) {
//            \REDCap::logEvent($e->getMessage());
            $this->emError($e->getMessage());
            echo $e->getMessage();
        }
    }

    public function getLoginMethod()
    {
        $result = db_query('select value from redcap_config where field_name = \'auth_meth_global\'');
        $authMethod = db_fetch_assoc($result)['value'];
        return $authMethod;
    }

    public function buildAttemptRecordArray()
    {
        $data = [];
        $data['record_id'] = \REDCap::reserveNewRecordId($this->getMapperProjectId());
        $data['ts_failed_mapping'] = date('Y-m-d H:i:s');;
        $data['hash'] = uniqid();
        if (isset($_GET['user'])) {
            $data['ehr_user'] = $this->cleanEHRUsername($_GET['user']);
        } else {
            $this->emLog('No EHR user found.', $_GET);
        }
        return $data;
    }

    public function cleanEHRUsername($username)
    {
        $temp = htmlspecialchars($username, ENT_QUOTES);
        $username = str_replace('+', '', $temp);
        $username = str_replace(' ', '', $username);
        return $username;
    }

    public function redirect($url)
    {
        $string = '<script type="text/javascript">';
        $string .= 'window.location = "' . $url . '"';
        $string .= '</script>';

        echo $string;
    }
    // test
    private function createLoginAttempt()
    {
        $user = htmlspecialchars($_GET['user'], ENT_QUOTES);
        if (isset($_GET['user']) && $this->canEHRUserBeMapped($user)) {
            $this->emLog('GET array', $_GET);
            $data = $this->buildAttemptRecordArray();
            $this->emLog('Data array', $data);
            $response = \REDCap::saveData($this->getMapperProjectId(), 'json', json_encode(array($data)));
            if (empty($response['errors'])) {
//                $this->setRedcapData($data);
//                $this->includeFile('views/form.php');
                $url = $this->getUrl('views/link.php', true, true) . '&hash=' . $data['hash'];
                $this->redirect($url);
                $this->exitAfterHook();
            } else {
                if (is_array($response['errors'])) {
                    $this->setErrors($response['errors']);
                } else {
                    $this->setErrors(array($response['errors']));
                }
            }

        } elseif (!$user) {
            // accessing standalone ehr.php
            return false;
        } else {
            throw new \Exception($user . ' already mapped but no session found for it. please check with Epic team. ');
        }
    }

    public function isHashValid($hash)
    {
        $param = array(
            'return_format' => 'array',
            'project_id' => $this->getMapperProjectId(),
        );

        $records = \REDCap::getData($param);
        foreach ($records as $key => $events) {
            foreach ($events as $event) {
                if ($hash == $event['hash']) {
                    $this->setRedcapData($event);
                    return true;
                }
            }
        }
        return false;
    }

    public function notifyREDCapAdmin($message)
    {
        //TODO
    }

    public function insertIntoEHRMapTable($userId)
    {
        $ehr_username = $this->getRedcapData()['ehr_user'];
        $sql = "INSERT INTO redcap_ehr_user_map VALUES ('$ehr_username', $userId)";

        $record = db_query($sql);
        if (!$record) {
            throw new \Exception(db_error());
        }
    }


    public function canEHRUserBeMapped($getUser = '')
    {
        $ehrUser = $this->cleanEHRUsername($getUser) ?: $this->getRedcapData()['ehr_user'];
        $sql = "SELECT * FROM redcap_ehr_user_map WHERE ehr_username ='$ehrUser'";

        $record = db_query($sql);

        if (db_num_rows($record) > 0) {
            $row = db_fetch_assoc($record);
            if (defined('UI_ID') && $row['redcap_userid'] != UI_ID) {
                $this->emError($ehrUser . ' is trying to get mapped to ' . UI_ID);
                $this->notifyREDCapAdmin($ehrUser . ' is trying to get mapped to ');
                if (!$getUser) {
                    throw new \Exception('User already mapped');
                }

            } else {
                if (!$getUser) {
                    throw new \LogicException('You already mapped this Epic user to a REDCap user. ');
                }
            }
            return false;
        }
        return true;
    }

    public function updateREDCapDataResult($user, $result)
    {
        $data = $this->getRedcapData();
        $data['ts_validate_attempt'] = date('Y-m-d H:i:s');
        $data['map_result'] = $result;
        $data['redcap_user'] = $user->getUsername();
        $response = \REDCap::saveData($this->getMapperProjectId(), 'json', json_encode(array($data)));
        if (empty($response['errors'])) {
            $this->setRedcapData($data);
        } else {
            if (is_array($response['errors'])) {
                $this->setErrors($response['errors']);
            } else {
                $this->setErrors(array($response['errors']));
            }
        }

    }

    /**
     * @param string $path
     */
    public function includeFile($path)
    {
        require $path;
    }


    /**
     * @return int
     */
    public function getMapperProjectId(): int
    {
        return $this->mapperProjectId;
    }

    /**
     * @param int $mapperProjectId
     */
    public function setMapperProjectId(int $mapperProjectId): void
    {
        $this->mapperProjectId = $mapperProjectId;
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->loggedIn;
    }

    /**
     * @param bool $loggedIn
     */
    public function setLoggedIn(bool $loggedIn): void
    {
        $this->loggedIn = $loggedIn;
    }

    /**
     * @return bool
     */
    public function isEhrContext(): bool
    {
        return $this->ehrContext;
    }

    /**
     * @param bool $ehrContext
     */
    public function setEhrContext(bool $ehrContext): void
    {
        $this->ehrContext = $ehrContext;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getRedcapData(): array
    {
        return $this->redcapData;
    }

    /**
     * @param array $redcapData
     */
    public function setRedcapData(array $redcapData): void
    {
        $this->redcapData = $redcapData;
    }

    /**
     * @return string
     */
    public function getCustomJS(): string
    {
        return $this->customJS;
    }

    /**
     * @param string $customJS
     */
    public function setCustomJS(string $customJS): void
    {
        $this->customJS = $customJS;
    }

    /**
     * @return string
     */
    public function getCustomCSS(): string
    {
        return $this->customCSS;
    }

    /**
     * @param string $customCSS
     */
    public function setCustomCSS(string $customCSS): void
    {
        $this->customCSS = $customCSS;
    }

    /**
     * @return bool
     */
    public function isSuppressForm(): bool
    {
        return $this->suppressForm;
    }

    /**
     * @param bool $suppressForm
     */
    public function setSuppressForm(bool $suppressForm): void
    {
        $this->suppressForm = $suppressForm;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $header
     */
    public function setHeader($header): void
    {
        $this->header = $header;
    }

    /**
     * @return bool
     */
    public function isSurvey(): bool
    {
        return $this->isSurvey;
    }

    /**
     * @param bool $isSurvey
     */
    public function setIsSurvey(bool $isSurvey): void
    {
        $this->isSurvey = $isSurvey;
    }

    /**
     * @return bool
     */
    public function isAPI(): bool
    {
        return $this->isAPI;
    }

    /**
     * @param bool $isAPI
     */
    public function setIsAPI(bool $isAPI): void
    {
        $this->isAPI = $isAPI;
    }

    /**
     * @return bool
     */
    public function isNoAuth(): bool
    {
        return $this->isNoAuth;
    }

    /**
     * @param bool $isNoAuth
     */
    public function setIsNoAuth(bool $isNoAuth): void
    {
        $this->isNoAuth = $isNoAuth;
    }

    /**
     * @return string
     */
    public function getFormHeader()
    {
        return $this->formHeader;
    }

    /**
     * @param string $formHeader
     */
    public function setFormHeader($formHeader): void
    {
        $this->formHeader = $formHeader;
    }


}
