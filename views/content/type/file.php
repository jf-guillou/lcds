<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\UploadAsset;

UploadAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\Content */

$this->title = Yii::t('app', 'Create {type} content', ['type' => $type->name]);
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
                <?= Html::fileInput('content', null, ['class' => 'content-file-upload']) ?>
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

        <?= $this->render('_time', [
            'model' => $model,
            'form' => $form,
        ]) ?>

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
                nextStep(data.result.filepath, data.result.filename, data.result.duration)
            } else {
                setError(data.result.message);
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            setProgress(progress);
        }
    });

    function nextStep(filepath, filename, fileduration)
    {
        uploaded = true;
        setError();
        $('#content-data').val(filepath + '§' + filename);
        $('#content-upload').slideUp();
        $('#content-form').slideDown();
        $('#content-description').val(filename);
        if (fileduration) {
            $('#content-duration').val(fileduration + 1);
        }
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
        console.log(cp);
        for (var i = 0; i < cp.types.length; i++) {
            var t = cp.types[i];
            if (t == 'text/plain') {
                handleUrl(cp.getData(t));
                return;
            }
            if (t == 'Files') {
                handleFile(cp.files);
                return;
            }
        }

        if (cp.files.length) {
            handleFile(cp.files);
            return;
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
                    setProgress(100);
                    nextStep(data.filepath, data.filename, data.duration);
                } else {
                    setError(data.message);
                }
            },
            error: function(jqXHR, textStatus) {
                uploading = false;
                setError(textStatus || 'Internal error');
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
