<style>
    <?= str_replace('%field%', '.field_'.$type->id, $type->css) ?>
</style>
<script type="text/javascript">
    <?= str_replace('%field%', '.field_'.$type->id, $type->js) ?>
</script>

<div class="field_<?= $type->id ?>">
    <?= $data ?>
</div>
