<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Url;

$this->title = $name;
$css = [];
$css[] = 'body { background-image: url('.Url::to('@web/uploads/background/'.$background).') }';
$js = [];
?>

<?php
foreach ($types as $type) :
    if ($type->css) {
        //$css[] = '.field_'.$type->id.' {'.$type->css.'}';
        $css[] = $type->css;
    }
    if ($type->js) {
        $js[] = str_replace('%field%', '.field_'.$type->id, $type->js);
    }
endforeach;

foreach ($fields as $field) :
    $properties = [
        'position: absolute;',
        'left: '.($field->x1 * 100).'%;',
        'top: '.($field->y1 * 100).'%;',
        'right: '.((1 - $field->x2) * 100).'%;',
        'bottom: '.((1 - $field->y2) * 100).'%;',
        //'width: '.($field->x2 * 100).'%;',
        //'height: '.($field->y2 * 100).'%;',
    ];
    //if ($field->css) {
    //    $properties[] = $field->css;
    //}
    $css[] = '#field_'.$field->id.' { '.implode(' ', $properties).'}';
    if ($field->css) {
        $css[] = $field->css;
    }
    if ($field->js) {
        $js[] = $field->js;
    }

    $contentTypes = array_map(function ($ct) {
        return $ct->id;
    }, $field->contentTypes);

    $classes = array_map(function ($ct) {
        return 'field_'.$ct;
    }, $contentTypes);

    $type = $field->contentTypes[0];
?>
<div
    id="field_<?= $field->id ?>"
    class="field <?= implode(' ', $classes) ?>"
    data-id="<?= $field->id ?>"
    data-types="<?= implode(' ', $contentTypes) ?>"
    style="display: none;"
    <?= $type->self_update ? '' : 'data-url="'.$nextUrl.$field->id.'"' ?>
>
    <?php
    if ($type->self_update && $type->html) {
        echo $type->html;
    } ?>
</div>
<?php endforeach; ?>
<script type="text/javascript">var updateScreenUrl = "<?= $updateUrl ?>"</script>
<?php
$this->registerCSS(implode("\n", $css));
$this->registerJS(implode("\n", $js));
