<?php

declare(strict_types=1);

namespace App\Command;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Symfony\Component\Console\{
    Attribute\AsCommand,
    Command\Command,
    Helper\ProgressBar,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface,
    Style\SymfonyStyle};

/**
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2004-present Agence Dn'D
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.dnd.fr/
 */
#[AsCommand(
    name: 'app:post-assets',
    description: 'Post assets',
)]
class PostAssetsCommand extends Command
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

        $progressBar = new ProgressBar($output, intval($input->getArgument('totalAssets')));
        $progressBar->start();

        $clientBuilder = new AkeneoPimClientBuilder($input->getArgument('url'));
        $client = $clientBuilder->buildAuthenticatedByPassword($input->getArgument('clientId'), $input->getArgument('secret'), $input->getArgument('username'), $input->getArgument('password'));

        $families = [
            ['internes' => 'A_visuelsinternes'],
            ['autres' => 'A_autresmedias'],
            ['externes' => 'A_visuelsexternes']
        ];

        foreach ($families as $family) {
            // scandir -> symfony finder
            $files = array_diff(scandir('docs/assets/' . array_keys($family)[0], SCANDIR_SORT_DESCENDING), ['..', '.', '.gitkeep']);
            foreach ($files as $file) {
                // vérifier
                $assets = json_decode(file_get_contents('docs/assets/' . array_keys($family)[0] . '/' . $file), true);

                $this->uploadAssetsAndMedias($client, array_values($family)[0], $assets, $input->getArgument('url'), $progressBar);
            }
        }

        $progressBar->finish();

        $io->success('SUCCESS');
        return Command::SUCCESS;
    }

    public function uploadAssetsAndMedias(AkeneoPimClientInterface $client, string $family, array $assets, string $url, ProgressBar $progressBar):void
    {
        foreach ($assets as $asset) {
            if (isset($asset['values']['media'][0]['data'])) {
                // download media in local -> upload on SASS -> delete media in local
                $this->downloadMedia($asset);
                $this->uploadMedia($client);
                $this->deleteMedia();
            }

            $client->getAssetManagerApi()->upsert($family, $asset['code'], [
                'code' => $asset['code'],
                'values' => [
                    'label' => [
                        ['locale' => 'fr_FR', 'channel' => null, 'data' => $asset['values']['media'][0]['data']??null],
                    ]
                ]
            ]);

            $progressBar->advance();
        }
    }

    public function downloadMedia(array $asset): void
    {
        $clientBuilder = new AkeneoPimClientBuilder('https://staging-louispion.cloud.akeneo.com/');
        $client = $clientBuilder->buildAuthenticatedByPassword('7_gbsap62ugy88gkwkcogowcs0o0sowo8gs4gk8wwgs8s0gk888', '3plq4eocxkyswc0cgscw44gsgk0g0cgkw4kggg0s4408gsg4gk', 'dataflow', 'LCoKmMVQwc7gq^');

        $mediaFile = $client->getAssetMediaFileApi()->download($asset['values']['media'][0]['data']);

        file_put_contents('docs/media/media_asset.jpg', $mediaFile->getBody()->getContents());
    }

    public function uploadMedia(AkeneoPimClientInterface $client): void
    {
        $mediaFileResponse = $client->getAssetMediaFileApi()->create('docs/media/media_asset.jpg');
        dump($mediaFileResponse);
        die;
    }

    public function deleteMedia(): void
    {
        unlink('docs/media/media_asset.jpg');
    }
}
