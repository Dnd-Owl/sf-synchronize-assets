<?php

declare(strict_types=1);

namespace App\Command;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;

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
class SynchronizeAssetsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('source/host', InputArgument::IS_ARRAY|InputArgument::REQUIRED, 'Who do you want to greet?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $source = $input->getArgument('source/host')[0];
        $host = $input->getArgument('source/host')[1];

        $clientBuilder = new AkeneoPimClientBuilder('https://staging-louispion.cloud.akeneo.com/');



        $client = $clientBuilder->buildAuthenticatedByPassword('7_gbsap62ugy88gkwkcogowcs0o0sowo8gs4gk8wwgs8s0gk888', '3plq4eocxkyswc0cgscw44gsgk0g0cgkw4kggg0s4408gsg4gk', 'dataflow', 'LCoKmMVQwc7gq^');

        $mediaFile = $client->getProductMediaFileApi()->listPerPage(10);
        $assets = $client->getAssetManagerApi();
        //dump($assets->all('A_visuelsexternes'));


        // Put ALL assets of A_visuelsinternes family in .txt
        $allAssets = [];
        foreach ($assets->all('A_visuelsinternes') as $asset) {
            $allAssets[] = $asset;
        }
        file_put_contents('docs/assets/internes/data.txt', json_encode($allAssets));


        // Put ALL assets of A_autresmedias family in .txt
        $allAssets = [];
        foreach ($assets->all('A_autresmedias') as $asset) {
            $allAssets[] = $asset;
        }
        file_put_contents('docs/assets/autres/data.txt', json_encode($allAssets));

        // Put ALL assets of A_visuelsexternes family in .txt
        $allAssets = [];
        foreach ($assets->all('A_visuelsexternes') as $asset) {
            $allAssets[] = $asset;
        }
        file_put_contents('docs/assets/externes/data.txt', json_encode($allAssets));



        // Put ALL assets of others family in .txt


        // call api v5
        // CALL AUTH
        // CALL GET
            // Boucle sur les assets
                // stocker tout dans une même fichier text (format json)

        // call api v7 (api local pour test)
        // CALL AUTH
        // boucle sur le json provenant du fichier text
            // Call POST pour ajouter les médias
            //     $mediaFileCode = $client->getAssetMediaFileApi()->create('????????.png');

            // Call POST pour ajouter les assets et les links à leur média


        $io->success('SUCCESS');

        return Command::SUCCESS;
    }
}