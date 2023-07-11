<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2004-present Agence Dn'D
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.dnd.fr/
 */
#[AsCommand(
    name: 'app:synchronize-assets',
    description: 'Add a short description for your command',
)]
class FluxAssetsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('source/host', InputArgument::IS_ARRAY| InputArgument::REQUIRED, 'Who do you want to greet?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $source = $input->getArgument('source/host')[0];
        $host = $input->getArgument('source/host')[1];

        dump($source);
        dump($host);


        // call api v5
        // CALL AUTH
        // CALL GET
        // stocker json dans un fichier text


        // call api v7
        // CALL AUTH
        // CALL POST
        // envoie


        $io->success('SUCCESS');

        return Command::SUCCESS;
    }
}
