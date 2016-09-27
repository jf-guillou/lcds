<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use app\assets\UploadAsset;

UploadAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\Content */

$this->title = Yii::t('app', 'Create {type} content', ['type' => Yii::t('app', $type->name)]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="upload-dummy" contenteditable></div>
<div class="content-type-file">

    <h1><?= Html::encode($this->title) ?></h1>

    <div id="content-upload">
        <div class="form-group field-upload-file">
            <label class="control-label" for="content-upload-file"><?= Yii::t('app', 'Paste or drag&drop the file or url to upload') ?></label>
            <div id="content-upload-file" class="file-upload">
                <?= Html::fileInput('content', false, ['class' => 'content-file-upload']) ?>
            </div>
            <div class="help-block"></div>
        </div>

        <div class="progress">
            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 0%">
            </div>
        </div>
    </div>

    <div id="content-form" style="display: none;">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-lg-4">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'id' => 'content-name']) ?>
            </div>
            <div class="col-lg-8">
                <?= $form->field($model, 'description')->textInput(['maxlength' => true, 'id' => 'content-description']) ?>
            </div>
        </div>

        <?= $form->field($model, 'data')->hiddenInput(['id' => 'content-data'])->label(false) ?>

        <div class="row">
            <div class="col-lg-2">
                <?= $form->field($model, 'duration')->textInput(['maxlength' => true, 'id' => 'content-duration']) ?>
            </div>
            <div class="col-lg-5">
                <?= $form->field($model, 'start_ts')->widget(DateTimePicker::className(), [
                        'language' => 'fr',
                        'pluginOptions' => ['format' => 'yyyy-mm-dd HH:mm:ss'],
                    ]) ?>
            </div>

            <div class="col-lg-5">
                <?= $form->field($model, 'end_ts')->widget(DateTimePicker::className(), [
                        'language' => 'fr',
                        'pluginOptions' => ['format' => 'yyyy-mm-dd HH:mm:ss'],
                    ]) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
<style type="text/css">
.file-upload {
    margin: 4px;
    padding: 4px;
    border: dotted 1px black;
    border-radius: 4px;
    background-color: #eee;
}
.content-file-upload {
    width: 100%;
    height: 40px;
    margin: 6px;
}
</style>
<script type="text/javascript">
window.jqReady.push(function() {
    $('input[type="file"]').fileupload({
        dataType: 'json',
        url: '<?= $uploadUrl ?>',
        done: function (e, data) {
            console.log('done', e, data);
            uploading = false;
            if (data.result.success) {
                nextStep(data.result.path)
            } else {
                setError(data.result.message);
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            setProgress(progress);
        }
    });

    function nextStep(path)
    {
        uploaded = true;
        $('#content-data').val(path);
        $('#content-upload').slideUp();
        $('#content-form').slideDown();
        $('#content-description').val(path.split('/').pop());
    }

    function setProgress(progress) {
        $('.progress-bar').css('width', progress + '%');
    }

    function setError(err = '') {
        if (err) {
            $('.field-upload-file').addClass('has-error');
            setProgress(0);
        } else {
            $('.field-upload-file').removeClass('has-error');
        }
        $('.help-block').text(err);
    }

    var uploaded = false;
    var uploading = false;
    $(document).on('paste', function(e) {
        if (uploading || uploaded) {
            return false;
        }

        var cp = e.originalEvent.clipboardData;
        for (var i = 0; i < cp.types.length; i++) {
            var t = cp.types[i];
            if (t == 'text/plain') {
                handleUrl(cp.getData(t));
            } else if (t == 'Files') {
                handleFile(cp.files);
            }
        }
        //return false;
    });

    function handleUrl(url) {
        setError();
        uploading = true;
        setProgress(20);
        $.ajax({
            url: '<?= $sideloadUrl ?>',
            dataType: 'json',
            data: {url: url},
            success: function(data) {
                uploading = false;
                if (data.success) {
                    if (data.duration) {
                        $('#content-duration').val(data.duration + 1);
                    }
                    setProgress(100);
                    nextStep(data.path);
                } else {
                    setError(data.message);
                }
            },
            error: function() {
                uploading = false;
                setError('!');
            }
        })
    }

    function handleFile(files) {
        setError();
        uploading = true;
        $('input[type="file"]').fileupload('send', {files: files});
    }
});
</script>
