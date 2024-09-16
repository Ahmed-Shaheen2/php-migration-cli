<?php

namespace ByJG\DbMigration\Console;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DownCommand extends ConsoleCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this
            ->setName('down')
            ->setDescription('Migrate down the database version.');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $versionInfo = $this->migration->getCurrentVersion();
            if (str_contains($versionInfo['status'], 'partial')) {
                /** @var QuestionHelper $helper  */
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion(
                    'The database was not fully updated and maybe be unstable. Did you really want migrate the version? (y/N)',
                    false
                );

                if (!$helper->ask($input, $output, $question)) {
                    $output->writeln('Aborted.');

                    return Command::FAILURE;
                }
            }

            parent::execute($input, $output);
            $this->migration->down($this->upTo, true);
            return Command::SUCCESS;
        } catch (Exception $ex) {
            $this->handleError($ex, $output);
            return Command::FAILURE;
        }
    }
}
