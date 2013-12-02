<?php
        if (isset($_POST[$input->getId()])) {
          $date = $_POST[$input->getId()];
        }
        elseif (null != $input->getDefaultValue()) {
          $date = $input->getDefaultValue();
        }
        else {
          $date = null;
        }
?>

<div class="input-control input-control-single-value-date control-group <?php echo empty($date) && 'true' == $input->getMandatory() ? 'error' : ''; ?>">
    <label for="<?php echo $input->getId(); ?>">
        <?php echo $input->getLabel(); ?>
        <?php echo true === $input->getMandatory() ? '<span class="required">*</span>' : ''; ?>
    </label>
    <input id="<?php echo $input->getId(); ?>"
           name="<?php echo $input->getId(); ?>"
           type="text"
           class="jasperDate"
           value="<?php echo $date; ?>"
           <?php echo true === $input->getMandatory() ? 'required' : ''; ?>
           <?php echo true === $input->getReadOnly() ? 'disabled' : ''; ?>
    />
</div>