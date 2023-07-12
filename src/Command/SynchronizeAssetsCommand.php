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
        $progressBar = new ProgressBar($output, 25183);
        $progressBar->start();

        $io = new SymfonyStyle($input, $output);

        $clientBuilder = new AkeneoPimClientBuilder('https://staging-louispion.cloud.akeneo.com/');
        $client = $clientBuilder->buildAuthenticatedByPassword('7_gbsap62ugy88gkwkcogowcs0o0sowo8gs4gk8wwgs8s0gk888', '3plq4eocxkyswc0cgscw44gsgk0g0cgkw4kggg0s4408gsg4gk', 'dataflow', 'LCoKmMVQwc7gq^');

        $assets = $client->getAssetManagerApi();


        // Put ALL assets of A_visuelsinternes family in .txt
        $allAssets = [];
        foreach ($assets->all('A_visuelsinternes') as $asset) {
            $allAssets[] = $asset;
            $progressBar->advance();
        }
        file_put_contents('docs/assets/internes/data.txt', json_encode($allAssets));


        // Put ALL assets of A_autresmedias family in .txt
        $allAssets = [];
        foreach ($assets->all('A_autresmedias') as $asset) {
            $allAssets[] = $asset;
            $progressBar->advance();
        }
        file_put_contents('docs/assets/autres/data.txt', json_encode($allAssets));

        // Put ALL assets of A_visuelsexternes family in .txt
        $allAssets = [];
        $i = 1;
        $j = 1;
        foreach ($assets->all('A_visuelsexternes') as $asset) {

            // if */externes/data_1.txt exist
            if (file_exists('docs/assets/externes/data_1.txt')) {
                $assetsAlreadyTake = json_decode(file_get_contents('docs/assets/externes/data_1.txt'));


                dump($assetsAlreadyTake);

            }


            $allAssets[] = $asset;
            if ($i === 13000) {
                file_put_contents('docs/assets/externes/data_' . $j. '.txt', json_encode($allAssets));
                $i = 1;
                $j++;
            }
            $progressBar->advance();
            $i++;
        }

        $io->success('SUCCESS');

        return Command::SUCCESS;
    }
}
