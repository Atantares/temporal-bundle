<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Symfony\Component\Console\Style\SymfonyStyle;
use Temporal\Worker\WorkerInterface as Worker;

#[AsCommand('debug:temporal:workflows', 'List registered workflows')]
final class WorkflowDebugCommand extends Command
{
    /**
     * @param array<non-empty-string, Worker> $workers
     * @param list<class-string> $workflowsWithoutWorkers
     */
    public function __construct(
        private readonly array $workers,
        private readonly array $workflowsWithoutWorkers
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this->addArgument('workers', mode: InputArgument::IS_ARRAY | InputArgument::OPTIONAL, description: 'Worker names', default: []);
    }


    protected function execute(Input $input, Output $output): int
    {
        /** @var list<non-empty-string> $workers */
        $workers = $input->getArgument('workers');
        $io      = new SymfonyStyle($input, $output);

        $io->title('Temporal Workflows');

        foreach ($this->workers as $name => $worker) {
            if (!empty($workers) && !in_array($name, $workers, true)) {
                continue;
            }


            $io->title(sprintf('Worker: %s', $name));

            $rows = [];

            foreach ($worker->getWorkflows() as $workflow) {
                if (in_array($workflow->getClass()->getName(), $this->workflowsWithoutWorkers, true)) {
                    continue;
                }

                $rows[] = [
                    $workflow->getID(),
                    $workflow->getClass()->getName(),
                    $workflow->getCronSchedule()?->interval ?? 'None',
                    $workflow->getMethodRetry() ? json_encode($workflow->getMethodRetry(), JSON_PRETTY_PRINT) : 'None',
                ];

                $rows[] = new TableSeparator();
            }

            if ($rows == []) {
                $io->note('Not found workflows');

                continue;
            }

            if (!is_array(end($rows))) {
                array_pop($rows);
            }

            $io->table(['Id', 'Class', 'Schedule Plan', 'Retry Policy'], $rows);
        }


        if (empty($this->workflowsWithoutWorkers) || !empty($workers)) {
            return self::SUCCESS;
        }


        $io->title('Registered workflow at all workers');

        $printedWorkflows = [];

        foreach ($this->workers as $worker) {
            $rows = [];

            foreach ($worker->getWorkflows() as $workflow) {
                if (!in_array($workflow->getClass()->getName(), $this->workflowsWithoutWorkers, true)) {
                    continue;
                }

                if (in_array($workflow->getClass()->getName(), $printedWorkflows, true)) {
                    continue;
                }

                $rows[] = [
                    $workflow->getID(),
                    $workflow->getClass()->getName(),
                    $workflow->getCronSchedule()?->interval ?? 'None',
                    $workflow->getMethodRetry() ? json_encode($workflow->getMethodRetry(), JSON_PRETTY_PRINT) : 'None',
                ];


                $rows[]             = new TableSeparator();
                $printedWorkflows[] = $workflow->getClass()->getName();
            }

            if (empty($rows)) {
                continue;
            }

            if (!is_array(end($rows))) {
                array_pop($rows);
            }

            $io->table(['Id', 'Class', 'Schedule Plan', 'Retry Policy'], $rows);
        }


        return self::SUCCESS;
    }
}
