<?php
namespace Stanford\EHRUserMapAssistant;


/** @var \Stanford\EHRUserMapAssistant\EHRUserMapAssistant $module */
$link = $module->getUrl('views/validation.php', false, true) . '&hash=' . filter_var($_GET['hash'], FILTER_SANITIZE_STRING);
$css = file_get_contents('https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
echo '<style>' . $css . '</style>';
$css = file_get_contents('https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css');
echo '<style>' . $css . '</style>';
$js = file_get_contents('https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js');
echo '<script>' . $js . '</script>';
$js = file_get_contents('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js');
echo '<script>' . $js . '</script>';
$js = file_get_contents('https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js');
echo '<script>' . $js . '</script>';
$js = file_get_contents('https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js');
echo '<script>' . $js . '</script>';
?>

<!--<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">-->
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>-->
<!--<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>-->
<!--<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>-->
<!--<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">-->
<!--<script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>-->
<script>
    <?php
    $class = '.btn';
    $tooltip = 'tooltip';
    echo "window.onload = function () {
                new ClipboardJS('$class');
                $(function () {
                  $('[data-toggle=$tooltip]').tooltip()

                });

         }"
    ?>
</script>
<div class="container">
    <?php echo $module->getHeader() ?>
    <div class="alert alert-secondary">
        <div class="row">
            <div class="col-11"><input class="form-control" id="link" value="<?php echo $link ?>"/></div>
            <div class="col-1">
                <div class="bd-clipboard float-right">
                    <button id="button" class="btn-clipboard btn btn-primary" data-clipboard-target="#link" title=""
                            data-original-title="Copy to clipboard" data-toggle="tooltip"
                            onmouseenter="displayTooltip('show')" onclick="changeTooltip('Copied')"
                            onmouseleave="changeTooltip('Copy to clipboard');displayTooltip('hide')"
                            data-placement="bottom">Copy
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    <?php
    echo "function changeTooltip(text){
             $('#button')
              .attr('data-original-title', text)
              .tooltip('show');
         }; function displayTooltip(val){
            $('#button').tooltip(val)
         }";
    ?>
</script>
