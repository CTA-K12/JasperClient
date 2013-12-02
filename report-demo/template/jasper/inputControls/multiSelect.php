<?php
    if(isset($_POST[$input->getId()]) && is_array($_POST[$input->getId()])){
        $optionVals = $_POST[$input->getId()];
    }
    else{
        $optionVals = array();
    }
?>
<!-- this goes in the control-group class maybe -->

<div class="input-control input-control-multi-select control-group
        <?php echo isset($_POST["submit"]) && 0 == count($optionVals) && 'true' == $input->getMandatory() ? 'error' : ''; ?>">
    <label for="<?php echo $input->getId(); ?>">
        <?php echo $input->getLabel() ? : $input->getId(); ?>
    </label>
    <select id="<?php echo $input->getId(); ?>"
            name="<?php echo $input->getId(); ?>[]"
            class="multiselect"
            multiple
            <?php echo true === $input->getMandatory() ? 'required' : ''; ?>
            <?php echo true === $input->getReadOnly() ? 'disabled' : ''; ?>
    >
        <?php
            $optionList = $input->getOptionList();
            foreach($optionList as $k => $option){
                echo  '<option value="' . $option->getId() . '" ';
                // go through the post values and see if any match the options
                if(0 < count($optionVals)){
                    foreach($optionVals as $v){
                        // if so, mark them as selected
                        echo ($option->getId() == $v ? 'selected' : '');
                    }
                }
                else{
                    // or mark them as default selected if jasper says it is
                    echo ( !isset($_POST["submit"]) && true === $option->getSelected() ? 'selected' : '');
                }
                echo '>' . $option->getLabel() . '</option>';
            }
        ?>
    </select>
    <div class="selectbox-options">
        Select:
        <a href="#" class="selectbox-options-all" onclick="$('#<?php echo $input->getId(); ?> option').attr('selected', 'selected'); return false;">All</a>
        <a href="#" class="selectbox-options-none" onclick="$('#<?php echo $input->getId(); ?> option:selected').removeAttr('selected'); return false;">None</a>
        <a href="#" class="selectbox-options-toggle" onclick="$('#<?php echo $input->getId(); ?> option').each(function(){$(this).attr('selected', !$(this).attr('selected'));});return false;">Toggle</a>
    </div>
</div>
