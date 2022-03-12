<?php

namespace Stanford\EHRUserMapAssistant;

?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
<link rel="stylesheet"
      href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css">
<div class="container mt-5">
    <?php
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
        echo '<div class="alert alert-success">Mapping completed successfully. please log in again from Epic</div>';
    }

    } catch (\LogicException $e) {
        echo '<div class="alert alert-warning">' . $e->getMessage() . '</div>';
    } catch (\Exception $e) {
        echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
    }
    ?>
</div>
