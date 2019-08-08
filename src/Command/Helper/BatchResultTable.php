<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Command\Helper;

use Camelot\ImageAsset\Console\Helper\StyledTableCell;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Camelot\ImageAsset\Transaction\Batch;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class BatchResultTable
{
    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function output(Batch $batch): int
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $rows = [];
        $return = 0;

        foreach ($batch->getResults() as $id => $result) {
            /** @var ThumbnailInterface $result */
            $job = $batch->getTransaction($id)->getCurrent();
            $rows[] = [
                StyledTableCell::createSuccess('PASS'),
                $job->getRequestImage()->getRelativePathname(),
                $result->getImage()->getRelativePathname(),
            ];
        }
        foreach ($batch->getInvalidFiles() as $invalidFile) {
            /* @var FileInterface $invalidFile */
            $rows[] = [StyledTableCell::createWarning('FAIL'), $invalidFile->getRelativePathname(), ''];
            $return = 1;
        }
        foreach ($batch->getExceptions() as $exception) {
            /* @var Exception $exception */
            $rows[] = [StyledTableCell::createError('ERROR'), '', $exception->getMessage()];
            $return = 1;
        }

        $io->table(['Result', 'Source', 'Output file'], $rows);

        return $return;
    }
}
