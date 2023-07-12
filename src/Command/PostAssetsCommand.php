<?php

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
    name: 'app:post-assets',
    description: 'Add a short description for your command',
)]
class PostAssetsCommand extends Command
{
    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);



        $clientBuilder = new AkeneoPimClientBuilder('http://localhost:8080/');
        $client = $clientBuilder->buildAuthenticatedByPassword('1_51dx3gjhxpk4s0844sksk4g44ggok04co0g8o88okk0wkook88', '4vnbto600su8w4ckosooo080kko4gww0g400o4kgw4ks48w8sc', 'dyoun', 'LouisPion2023');

        $assetsOthers = json_decode(file_get_contents('docs/assets/autres/data.txt'));

        foreach ($assetsOthers as $asset) {
            dump($asset);

            $client->getAssetApi()->upsert($asset['code'], [
                'localizable'     => null,
                'description'     => 'The wonderful unicorn',
                'end_of_use'      => '2042-11-21',
                'tags'            => ['colored', 'flowers'],
                'categories'      => ['face', 'pack'],
            ]);
        }


        // $mediaFileCode = $client->getAssetMediaFileApi()->create('????????.png');

        return Command::SUCCESS;
    }
}
