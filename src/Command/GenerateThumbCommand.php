<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Command;

use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Thumbnail\CreatorInterface;
use Camelot\ImageAsset\Transaction\TransactionBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\PathUtil\Path;
use function dirname;

final class GenerateThumbCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'camelot:image:generate-thumb';

    /** @var TransactionBuilder */
    private $builder;
    /** @var CreatorInterface */
    private $creator;
    /** @var FilesystemInterface */
    private $thumbnailFilesystem;
    /** @var string */
    private $projectDir;

    public function __construct(TransactionBuilder $builder, CreatorInterface $creator, FilesystemInterface $thumbnailFilesystem, string $projectDir)
    {
        $this->builder = $builder;
        $this->creator = $creator;
        $this->thumbnailFilesystem = $thumbnailFilesystem;
        $this->projectDir = $projectDir;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a thumbnail for an image')
            ->addArgument('source', InputArgument::REQUIRED, 'Source image file path')
            ->addArgument('destination', InputArgument::REQUIRED, 'Output image file path')
            ->addArgument('action', InputArgument::REQUIRED, 'Action to perform: [b]order, [c]rop, [f]it, [r]esize')
            ->addArgument('width', InputArgument::REQUIRED, 'Target image width')
            ->addArgument('height', InputArgument::REQUIRED, 'Target image height')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcefully overwrite target file if it exists')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $action = Action::create($input->getArgument('action'));
        $dimensions = new Dimensions((int) $input->getArgument('width'), (int) $input->getArgument('height'));

        $sourcePath = Path::isAbsolute($input->getArgument('source')) ? $input->getArgument('source') : Path::makeAbsolute($input->getArgument('source'), $this->projectDir);
        $sourceFilesystem = new Filesystem(dirname($sourcePath));
        $sourceFile = $sourceFilesystem->get(basename($sourcePath));
        $destinationPath = Path::isAbsolute($input->getArgument('destination')) ? $input->getArgument('destination') : Path::makeAbsolute($input->getArgument('destination'), $this->projectDir);
        $destinationPathname = basename($destinationPath);
        $destinationFilesystem = new Filesystem(dirname($destinationPath));

        $io->title(sprintf('Creating a %s (%s) thumbnail of %s in %s', $dimensions, $action, Path::makeRelative($sourceFile->getPathname(), $this->projectDir), Path::makeRelative($destinationPath, $this->projectDir)));

        $transaction = $this->builder->createTransaction($sourceFile->getRelativePathname(), $action, $dimensions, $sourceFile);
        $thumbnailData = $this->creator->create($transaction->start());
        if ($destinationFilesystem->exists($destinationPathname) && !$input->getOption('force')) {
            throw new RuntimeException(sprintf('File %s exists. Refusing to overwrite!', $destinationPath));
        }
        $image = $destinationFilesystem->write($destinationPathname, $thumbnailData);

        $io->note('Wrote file to ' . Path::makeRelative($image->getPathname(), $this->projectDir));

        $io->success('Generation complete');

        return 0;
    }
}
