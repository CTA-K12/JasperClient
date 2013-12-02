<?php
    if(isset($_POST[$input->getId()])){
        $optionVal = $_POST[$input->getId()];
    }
    else{
        $optionVal = null;
    }

?>
<!-- this goes in the control-group class maybe -->

<div class="input-control input-control-single-select control-group
        <?php echo isset($_POST["submit"]) && null == $optionVal && 'true' == $input->getMandatory() ? 'error' : ''; ?>">
    <label for="<?php echo $input->getId(); ?>">
        <?php echo $input->getLabel() ? : $input->getId(); ?>
    </label>
    <select id="<?php echo $input->getId(); ?>"
            name="<?php echo $input->getId(); ?>"
            class="select"
            <?php echo true === $input->getMandatory() ? 'required' : ''; ?>
            <?php echo true === $input->getReadOnly() ? 'disabled' : ''; ?>
    >
        <?php
            $optionList = $input->getOptionList();
            foreach($optionList as $k => $option){
                echo  '<option value="' . $option->getId() . '" ';
                // go through the post values and see if any match the options
                if(null != $optionVal){
                    // if so, mark them as selected
                    echo ($option->getId() == $optionVal ? 'selected' : '');
                }
                else{
                    // or mark them as default selected if jasper says it is
                    echo ( !isset($_POST["submit"]) && true === $option->getSelected() ? 'selected' : '');
                }
                echo '>' . $option->getLabel() . '</option>';
            }
        ?>
    </select>
</div>