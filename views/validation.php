<?php

namespace Stanford\EHRUserMapAssistant;


/** @var \Stanford\EHRUserMapAssistant\EHRUserMapAssistant $module */
try {
    $hash = filter_var($_GET['hash'], FILTER_SANITIZE_STRING);
    if (empty($hash)) {
        throw new \Exception('Hash is missing');
    }
    $user = $module->framework->getUser();
    if (!$module->isHashValid($hash)) {
        $module->updateREDCapDataResult($user, EHRUserMapAssistant::OTHER);
        $module->notifyREDCapAdmin("Hash not valid");
        throw new \Exception("Hash not valid");
    }

    if (strtotime($module->getRedcapData()['ts_failed_mapping']) > time() + 300) {
        $module->updateREDCapDataResult($user, EHRUserMapAssistant::HASH_EXPIRED);
        throw new \Exception("Hash expired. please get another token from Epic.");
    }

    if ($module->canEHRUserBeMapped()) {
        $module->insertIntoEHRMapTable(UI_ID);
        $module->updateREDCapDataResult($user, EHRUserMapAssistant::SUCCESS);
        echo 'Mapping completed successfully. pleasea login again from Epic';
    }

} catch (\LogicException $e) {
    echo '<div class="alert alert-warning">' . $e->getMessage() . '</div>';
} catch (\Exception $e) {
    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
}
