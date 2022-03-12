<?php
namespace Stanford\EHRUserMapAssistant;


/** @var \Stanford\EHRUserMapAssistant\EHRUserMapAssistant $this */
?>
<script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>
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
        <?php
        echo $this->getCustomJS()
        ?>
    </script>
    <?php
}
?>
<script>
    Form.errors = <?php echo json_encode($this->getErrors()) ?>;
    Form.validationURL = "<?php echo $this->getUrl('views/validation.php', false, true) . '&hash=' . $this->getRedcapData()['hash'] ?>";
    Form.header = "<?php echo $this->getHeader() ?>"
</script>

