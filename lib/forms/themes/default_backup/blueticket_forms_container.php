<div class="blueticket_forms<?php echo $this->is_rtl ? ' blueticket_forms_rtl' : ''?>">
    <?php echo $this->render_table_name(false, 'div', true)?>
    <div class="blueticket_forms-container"<?php echo ($this->start_minimized) ? ' style="display:none;"' : '' ?>>
        <div class="blueticket_forms-ajax">
            <?php echo $this->render_view() ?>
        </div>
        <div class="blueticket_forms-overlay"></div>
    </div>
</div>