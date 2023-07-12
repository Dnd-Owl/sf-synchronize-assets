<?php

declare(strict_types=1);

namespace App\Command;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\Api\AssetManager\AssetApiInterface as AssetManagerApiInterface;
use Symfony\Component\Console\{
    Attribute\AsCommand,
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface,
    Style\SymfonyStyle,
    Helper\ProgressBar
};

/**
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2004-present Agence Dn'D
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.dnd.fr/
 */
#[AsCommand(
    name: 'app:synchronize-assets',
    description: 'Get akeneo assets',
)]
class SynchronizeAssetsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $progressBar = new ProgressBar($output, 25183);
        $progressBar->start();

        $clientBuilder = new AkeneoPimClientBuilder('https://staging-louispion.cloud.akeneo.com/');
        $client = $clientBuilder->buildAuthenticatedByPassword('7_gbsap62ugy88gkwkcogowcs0o0sowo8gs4gk8wwgs8s0gk888', '3plq4eocxkyswc0cgscw44gsgk0g0cgkw4kggg0s4408gsg4gk', 'dataflow', 'LCoKmMVQwc7gq^');
        $assets = $client->getAssetManagerApi();

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
            if ($totalFamilyAssets >= 13000) {
                file_put_contents('docs/assets/' . array_keys($family)[0] . '/data1.txt', json_encode($allAssets));
                $numberFile = 2;
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
