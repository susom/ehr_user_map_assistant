<?php
namespace Stanford\EHRUserMapAssistant;


/** @var \Stanford\EHRUserMapAssistant\EHRUserMapAssistant $this */
?>
<script src="<?php echo $this->getUrl('asset/js/form.js') ?>"></script>
<?php
if ($this->getCustomCSS()) {
    ?>
    <style>
        <?php
        echo $this->getCustomCSS()
        ?>
    </style>
    <?php
}
?>
<?php
if ($this->getCustomJS()) {
    ?>
    <script>
        // window.onload = function () {
        <?php
        echo $this->getCustomJS()
        ?>
        // }
    </script>
    <?php
}
?>

<script>
    Form.header = "<?php echo 'asfsdfsdfdsfdsfdd' ?>"
</script>

