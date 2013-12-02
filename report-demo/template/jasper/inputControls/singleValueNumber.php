<?php
        if (isset($_POST[$input->getId()])) {
          $text = $_POST[$input->getId()];
        }
        elseif (null != $input->getDefaultValue()) {
          $text = $input->getDefaultValue();
        }
        else {
          $text = null;
        }
?>

<div class="input-control input-control-single-value-number control-group <?php echo empty($text) && 'true' == $input->getMandatory() ? 'error' : ''; ?>">
    <label for="<?php echo $input->getId(); ?>">
        <?php echo $input->getLabel(); ?>
        <?php echo true === $input->getMandatory() ? '<span class="required">*</span>' : ''; ?>
    </label>
    <input id="<?php echo $input->getId(); ?>"
           name="<?php echo $input->getId(); ?>"
           type="text"
           class=""
           value="<?php echo $text; ?>"
           <?php echo true === $input->getMandatory() ? 'required' : ''; ?>
           <?php echo true === $input->getReadOnly() ? 'disabled' : ''; ?>
    />
</div>
