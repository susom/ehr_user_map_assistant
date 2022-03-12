<?php

namespace Stanford\EHRUserMapAssistant;

use ExternalModules\ExternalModules;

require_once "emLoggerTrait.php";

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

    public function __construct()
    {
        parent::__construct();
        // Other code to run when object is instantiated

        if (ExternalModules::getSystemSetting($this->PREFIX, 'mapper-project-id-attempts')) {
            $this->setMapperProjectId(ExternalModules::getSystemSetting($this->PREFIX, 'mapper-project-id-attempts'));
        }

        if (ExternalModules::getSystemSetting($this->PREFIX, 'suppress-table-login-option')) {
            $this->setSuppressForm(true);
            $this->setCustomJS(ExternalModules::getSystemSetting($this->PREFIX, 'custom-js'));
            $this->setCustomCSS(ExternalModules::getSystemSetting($this->PREFIX, 'custom-css'));
        }

        $this->setHeader(ExternalModules::getSystemSettings($this->PREFIX, 'login-form-header'));

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
    }

    function redcap_every_page_before_render($project_id)
    {
        if ($this->isEhrContext()) {
            echo '<h1>Hello EHR USER</h1>';
        }
    }

    function redcap_every_page_top($project_id)
    {
        try {
            if ($this->isEhrContext()) {
                if (!$this->isLoggedIn()) {
                    $this->createLoginAttempt();
                }

            }
        } catch (\Exception $e) {
            \REDCap::logEvent($e->getMessage());
            $this->emError($e->getMessage());
        }
    }

    public function buildAttemptRecordArray()
    {
        $data = [];
        $data['record_id'] = \REDCap::reserveNewRecordId($this->getMapperProjectId());
        $data['ts_failed_mapping'] = date('Y-m-d H:i:s');;
        $data['hash'] = uniqid();
        if (isset($_GET['user'])) {
            $data['ehr_user'] = str_replace('+', '', filter_var($_GET['user'], FILTER_SANITIZE_STRING));
        } else {
            throw new \Exception('No EHR user found.');
        }
        return $data;
    }

    private function createLoginAttempt()
    {
        $data = $this->buildAttemptRecordArray();
        $response = \REDCap::saveData($this->getMapperProjectId(), 'json', json_encode(array($data)));
        if (empty($response['errors'])) {
            $this->setRedcapData($data);
            $this->includeFile('views/form.php');
        } else {
            if (is_array($response['errors'])) {
                $this->setErrors($response['errors']);
            } else {
                $this->setErrors(array($response['errors']));
            }
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

    }

    public function canEHRUserBeMapped()
    {
        $user = $this->framework->getUser();
        $ehrUser = $this->getRedcapData()['ehr_user'];
        $sql = "SELECT * FROM redcap_ehr_user_map WHERE ehr_username ='$ehrUser'";

        $record = db_query($sql);

        if (db_num_rows($record) > 0) {
            $row = db_fetch_assoc($record);
            if ($row['redcap_userid'] != UI_ID) {
                $this->emError($ehrUser . ' is trying to get mapped to ');
                $this->notifyREDCapAdmin($ehrUser . ' is trying to get mapped to ');
                throw new \Exception('User already mapped');
            } else {
                throw new \LogicException('You already mapped this your Epic user to a REDCap user. ');
            }
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

}
