<?php

namespace app\models\types;

/**
 * This is the model class for HostedImage content type.
 */
class HostedImage extends Image
{
    public $name = 'Hosted image';
    public $description = 'Upload an image to servers.';
    public $input = 'file';
    public $usable = true;
}
