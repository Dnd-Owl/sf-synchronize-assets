<?php

declare(strict_types=1);

namespace App\Command;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\Api\AssetManager\AssetApiInterface as AssetManagerApiInterface;
use Symfony\Component\Console\{
    Attribute\AsCommand,
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface,
    Style\SymfonyStyle,
    Helper\ProgressBar};

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
            ->addArgument('totalAssets', InputArgument::OPTIONAL, 'Total assets.', 25183);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clientBuilder = new AkeneoPimClientBuilder($input->getArgument('url'));
        $client = $clientBuilder->buildAuthenticatedByPassword($input->getArgument('clientId'), $input->getArgument('secret'), $input->getArgument('username'), $input->getArgument('password'));
        $assets = $client->getAssetManagerApi();

        $progressBar = new ProgressBar($output, intval($input->getArgument('totalAssets')));
        $progressBar->start();

        $families = [
            ['internes' => 'A_visuelsinternes'],
            ['autres' => 'A_autresmedias'],
            ['externes' => 'A_visuelsexternes']
        ];

        foreach ($families as $family) {
            $this->getAssets($assets, $family, $progressBar);
        }

        $progressBar->finish();

        $io->success('SUCCESS');
        return Command::SUCCESS;
    }

    public function getAssets(AssetManagerApiInterface $assets, array $family, ProgressBar $progressBar): void
    {
        $totalFamilyAssets = 0;
        $numberFile = null;

        $allAssets = [];
        foreach ($assets->all(array_values($family)[0]) as $asset) {
            // If we have more than 13.000 assets in a family we create a first .txt document
            if (count($allAssets) >= 13000) {
                file_put_contents('docs/assets/' . array_keys($family)[0] . '/data1.txt', json_encode($allAssets));
                $numberFile = $numberFile? $numberFile+1 :2;
                $totalFamilyAssets = 0;
                $allAssets = [];
            }

            $allAssets[] = $asset;
            $totalFamilyAssets++;
            $progressBar->advance();
        }
        file_put_contents('docs/assets/' . array_keys($family)[0] . '/data' . $numberFile . '.txt', json_encode($allAssets));
    }
}
