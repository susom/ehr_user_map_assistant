<?php
namespace Stanford\EHRUserMapAssistant;


/** @var \Stanford\EHRUserMapAssistant\EHRUserMapAssistant $module */
$link = $module->getUrl('views/validation.php', false, true) . '&hash=' . filter_var($_GET['hash'], FILTER_SANITIZE_STRING);
?>

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>
<?php
if ($module->getCustomCSS()) {
    ?>
    <style>
        <?php
        echo $module->getCustomCSS()
        ?>
    </style>
    <?php
}
?>
<?php
if ($module->getCustomJS()) {
    ?>
    <script>
        $(document).ready(function () {
            new ClipboardJS('.btn')
            setTimeout(function () {
                <?php
                    echo $module->getCustomJS()
                    ?>;
                console.log('hide');
            }, 100)
        });
        // window.onload = function () {
        <?php
        echo $module->getCustomJS()
        ?>
        // }
    </script>
    <?php
}
?>
<div class="container">
    <?php echo $module->getHeader() ?>
    <div class="alert alert-secondary">
        <div class="row">
            <div class="col-11"><input class="form-control" id="link" value="<?php echo $link ?>"/></div>
            <div class="col-1">
                <div class="bd-clipboard float-right">
                    <button class="btn-clipboard btn btn-primary" data-clipboard-target="#link" title=""
                            data-original-title="Copy to clipboard">Copy
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
