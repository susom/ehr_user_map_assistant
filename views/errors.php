<?php

namespace Stanford\EHRUserMapAssistant;
/** @var \Stanford\EHRUserMapAssistant\EHRUserMapAssistant $this */
?>
<style>
    html body * {
        display: none;
    }

    .shown {
        display: block;
    }
</style>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
<link rel="stylesheet"
      href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css">
<div id="showedArea" class="shown container mt-5">
    <?php
    if (!empty($this->errors)) {
        foreach ($this->errors as $error) {
            echo '<div class="shown alert alert-danger">' . $error . '</div>';
        }
    }
    ?>
</div>
