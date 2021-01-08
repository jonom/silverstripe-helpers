<?php

use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Assets\Image;

class RegenerateThumbnailsTask extends BuildTask
{
    protected $title = 'Regenerate image thumnbnails';

    protected $description = 'This will probably timeout if lots of images are missing thumnbnails';

    protected $enabled = true;

    public function run($request)
    {
        $images = Image::get();
        foreach ($images as $image) {
            AssetAdmin::singleton()->generateThumbnails($image);
        }
    }
}
