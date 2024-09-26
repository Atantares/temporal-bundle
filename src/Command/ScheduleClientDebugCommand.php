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
use Temporal\Client\ClientOptions;

#[AsCommand('debug:temporal:schedule-clients', 'List registered schedule clients')]
final class ScheduleClientDebugCommand extends Command
{
    /**
     * @param array<int, array{
     *     id: non-empty-string,
     *     name: non-empty-string,
     *     options: ClientOptions,
     *     dataConverter: non-empty-string,
     *     address: non-empty-string
     * }> $clients
     */
    public function __construct(private readonly array $clients)
    {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this->addArgument('clients', mode: InputArgument::IS_ARRAY | InputArgument::OPTIONAL, description: 'Client names', default: []);
    }

    protected function execute(Input $input, Output $output): int
    {
        $foundClients = false;
        /** @var list<non-empty-string> $interestedClients */
        $interestedClients = $input->getArgument('clients');
        $io                = new SymfonyStyle($input, $output);

        $io->title('Temporal Schedule Clients');

        foreach ($this->clients as $client) {
            $rows = [];

            if (!empty($interestedClients) && !in_array($client['name'], $interestedClients, true)) {
                continue;
            }

            $foundClients = true;

            $io->title(sprintf('Client: %s', $client['name']));

            $rows[] = [
                $client['id'],
                $client['address'],
                $client['dataConverter'],
                json_encode($client['options'], JSON_PRETTY_PRINT),
            ];

            $rows[] = new TableSeparator();


            /**@phpstan-ignore-next-line **/
            if (!is_array(end($rows))) {
                array_pop($rows);
            }

            $io->table(['Id', 'Address', 'DataConverterId','Options'], $rows);
        }


        if (!$foundClients) {
            $io->note('Not found clients');

            return self::SUCCESS;
        }


        return self::SUCCESS;
    }
}