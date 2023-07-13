<?php

declare(strict_types=1);

namespace App\Command;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Symfony\Component\Console\{
    Attribute\AsCommand,
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface,
    Style\SymfonyStyle,
    Helper\ProgressBar};
use Symfony\Component\Finder\Finder;

/**
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2004-present Agence Dn'D
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.dnd.fr/
 */
#[AsCommand(
    name: 'app:get-assets',
    description: 'Get akeneo assets',
)]
class GetAssetsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'URL.')
            ->addArgument('clientId', InputArgument::REQUIRED, 'Client ID.')
            ->addArgument('secret', InputArgument::REQUIRED, 'Secret.')
            ->addArgument('username', InputArgument::REQUIRED, 'Username.')
            ->addArgument('password', InputArgument::REQUIRED, 'Password.')
            ->addArgument('batchSize', InputArgument::OPTIONAL, 'Batch size.', 1000)
            ->addArgument('totalAssets', InputArgument::OPTIONAL, 'Total assets.', 25183);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clientBuilder = new AkeneoPimClientBuilder($input->getArgument('url'));
        $client = $clientBuilder->buildAuthenticatedByPassword($input->getArgument('clientId'), $input->getArgument('secret'), $input->getArgument('username'), $input->getArgument('password'));

        $progressBar = new ProgressBar($output, intval($input->getArgument('totalAssets')));
        $progressBar->start();

        $families = $this->getFamilies();

        if (!$families) {
            $io->info('No families file detected.');
        }

        foreach ($families as $family) {
            $this->getAssets($client, $family, $progressBar, intval($input->getArgument('batchSize')));
        }

        $progressBar->finish();

        $io->success('SUCCESS');
        return Command::SUCCESS;
    }

    public function getFamilies(): array|null
    {
        if (!file_exists(CommandInterface::PATH_FAMILIES)) {
            return null;
        }

        $separator = ';';
        $families = [];
        $headers = '';

        $csvFile = file(CommandInterface::PATH_FAMILIES);
        foreach ($csvFile as $index => $line) {
            if ($index === 0) {
                $headers = str_getcsv($line, $separator);
                continue;
            }
            $families[] = array_combine($headers, str_getcsv($line, $separator));
        }

        return $families;
    }

    public function getAssets(AkeneoPimClientInterface $client , array $family, ProgressBar $progressBar, int $batchSize): void
    {
        $fp = fopen(CommandInterface::LIST_CODES, 'a');
        $alreadyProcess = file_get_contents(CommandInterface::LIST_CODES);

        $allAssets = [];
        $totalFamilyAssets = 0;

        foreach ($client->getAssetManagerApi()->all($family['code']) as $asset) {
            if (in_array($asset['code'],  explode(',', $alreadyProcess))) {
                $progressBar->advance();
                continue;
            }

            $allAssets[] = $asset;
            $totalFamilyAssets++;

            if (count($allAssets) >= $batchSize) {
                $this->downloadAssets($family, $allAssets);

                $totalFamilyAssets = 0;
                $allAssets = [];
            }

            fwrite($fp, $asset['code'] . ',');
            $progressBar->advance();
        }

        if (!empty($allAssets)) {
            $this->downloadAssets($family, $allAssets);
        }
    }

    public function downloadAssets(array $family, array $allAssets): void
    {
        $numberFile = 1;
        $finder = new Finder();
        $finder->files()->in(CommandInterface::PATH_ASSETS . $family['folder']);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $numberFile = intval(substr($file->getRelativePathname(), strlen('data_')+strpos($file->getRelativePathname(), '-'), (strlen($file->getRelativePathname()) - strpos($file->getRelativePathname(), '.txt'))*(-1)));
                $numberFile++;
            }
        }

        $fileName = CommandInterface::PATH_ASSETS . $family['folder'] . '/data_' . $numberFile . '.txt';

        file_put_contents($fileName, json_encode($allAssets));
    }
}
