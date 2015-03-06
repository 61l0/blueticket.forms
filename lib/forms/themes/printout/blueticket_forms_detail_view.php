<?php echo $this->render_table_name($mode); ?>
<div class="blueticket_forms-top-actions">
    <?php echo $this->render_button('save_new','save','create','blueticket_forms-button blueticket_forms-blue','','create,edit') ?>
    <?php echo $this->render_button('save_edit','save','edit','blueticket_forms-button blueticket_forms-green','','create,edit') ?>
    <?php echo $this->render_button('save_return','save','list','blueticket_forms-button blueticket_forms-purple','','create,edit') ?>
    <?php echo $this->render_button('return','list','','blueticket_forms-button blueticket_forms-orange') ?>
</div>
<div class="blueticket_forms-view">
<?php echo $this->render_fields_list($mode); ?>
</div>
<div class="blueticket_forms-nav">
    <?php echo $this->render_benchmark(); ?>
</div>
