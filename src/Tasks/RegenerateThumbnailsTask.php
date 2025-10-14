<?php

use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Assets\Image;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class RegenerateThumbnailsTask extends BuildTask
{
    protected string $title = 'Regenerate image thumnbnails';

    protected static string $description = 'This will probably timeout if lots of images are missing thumnbnails';

    protected static string $commandName = 'regenerate-thumbnails';

    protected $enabled = true;

    public function execute(InputInterface $input, PolyOutput $output): int
    {
        $images = Image::get();
        foreach ($images as $image) {
            AssetAdmin::singleton()->generateThumbnails($image);
        }
        return Command::SUCCESS;
    }

    public function getOptions(): array
    {
        return [
            // new InputOption('do-action', null, InputOption::VALUE_NONE, 'do something specific'),
        ];
    }
}
