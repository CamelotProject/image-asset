<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Command;

use Camelot\ImageAsset\Command\Helper\BatchResultTable;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Image\Attributes\Aliases;
use Camelot\ImageAsset\Transaction\BatchProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\PathUtil\Path;
use const DIRECTORY_SEPARATOR;

final class GenerateThumbsCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'camelot:image:generate-thumbs';

    /** @var BatchProcessor */
    private $batchProcessor;
    /** @var FilesystemInterface */
    private $thumbnailFilesystem;
    /** @var Aliases */
    private $aliases;
    /** @var string */
    private $projectDir;
    /** @var string */
    private $publicDir;

    public function __construct(BatchProcessor $batchProcessor, FilesystemInterface $thumbnailFilesystem, Aliases $aliases, string $projectDir, string $publicDir)
    {
        $this->batchProcessor = $batchProcessor;
        $this->thumbnailFilesystem = $thumbnailFilesystem;
        $this->aliases = $aliases;
        $this->projectDir = $projectDir;
        $this->publicDir = $publicDir;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate thumbnails for a batch of images')
            ->addArgument('aliases', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Alias name(s) to generate', array_keys($this->aliases->getAliases()))
            ->addOption('public', null, InputOption::VALUE_REQUIRED, 'Path to public directory to use (relative to project root)', str_replace($this->projectDir . DIRECTORY_SEPARATOR, '', $this->publicDir))
            ->addOption('thumbs', null, InputOption::VALUE_REQUIRED, 'The mount point of the thumbnails (relative to project root)', str_replace($this->projectDir . DIRECTORY_SEPARATOR, '', $this->thumbnailFilesystem->getMountPath()))
            ->addOption('include', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Pattern of files/directories to include (everything in public by default)')
            ->addOption('exclude', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Pattern of files/directories to exclude (supported image format extensions by default)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $return = 0;
        $io = new SymfonyStyle($input, $output);

        $resolver = function (array $paths): array {
            $return = [];
            foreach ($paths as $path) {
                if (Path::isAbsolute($path)) {
                    throw new RuntimeException(sprintf('Can not add an absolute path to include/exclude'));
                }
                $return[] = Path::makeRelative(Path::makeAbsolute($path, $this->projectDir), $this->publicDir);
            }

            return $return;
        };

        $aliases = (array) $input->getArgument('aliases');
        $publicDir = Path::isAbsolute($input->getOption('public')) ? $input->getOption('public') : Path::makeAbsolute($input->getOption('public'), $this->projectDir);
        $thumbsDir = Path::isAbsolute($input->getOption('thumbs')) ? $input->getOption('thumbs') : Path::makeAbsolute($input->getOption('thumbs'), $this->projectDir);
        $thumbnailFilesystem = new Filesystem($thumbsDir);
        $includes = $resolver((array) $input->getOption('include'));
        $excludes = $resolver((array) $input->getOption('exclude'));

        $io->title(sprintf('Processing aliases: %s', implode(', ', array_keys($this->aliases->getAliases()))));

        foreach ($aliases as $aliasName) {
            $alias = $this->aliases->getAlias($aliasName);
            $io->note(sprintf('Generating %s x %s (%s) thumbnails for images in %s', $alias->getWidth(), $alias->getHeight(), $alias->getAction(), Path::makeRelative($publicDir, $this->projectDir)));

            $finder = (new Filesystem($publicDir))
                ->finder()
                ->in($includes)
                ->exclude($excludes)
            ;

            $batch = $this->batchProcessor->createBatch($finder, $alias->getWidth(), $alias->getHeight(), $alias->getAction());
            $this->batchProcessor->process($batch, $thumbnailFilesystem);

            $return = (new BatchResultTable($input, $output))->output($batch);
        }

        $io->success('Generation complete');

        return $return;
    }
}
