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
    Style\SymfonyStyle
};
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

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
            ->addArgument('password', InputArgument::REQUIRED, 'Password.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clientBuilder = new AkeneoPimClientBuilder($input->getArgument('url'));
        $client = $clientBuilder->buildAuthenticatedByPassword($input->getArgument('clientId'), $input->getArgument('secret'), $input->getArgument('username'), $input->getArgument('password'));

        $families = [
            ['internes' => 'A_visuelsinternes'],
            ['autres' => 'A_autresmedias'],
            ['externes' => 'A_visuelsexternes']
        ];

        foreach ($families as $family) {
            $assets = json_decode(file_get_contents('docs/assets/' . array_keys($family)[0] . '/data.txt'), true);

            $this->uploadAssets($client, array_values($family)[0], $assets, $input->getArgument('url'));
        }

        // $mediaFileCode = $client->getAssetMediaFileApi()->create('????????.png');

        $io->success('SUCCESS');
        return Command::SUCCESS;
    }

    public function uploadAssets(AkeneoPimClientInterface $client, string $family, array $assets, string $url):void
    {
        $headers = [
            'Authorization' => 'Bearer' . $client->getToken()
        ];

        foreach ($assets as $asset) {
            if (isset($asset['values']['media'][0]['data'])) {
                $this->downloadMedia($asset);
                $this->uploadMedia($client, $asset);
                $this->deleteMedia();
            }

            $body = [
                "code"=> $asset['code'],
                "values"=> [
                    "media"=> [
                        "locale"=> null,
                        "channel"=> null,
                        "data"=> $asset['values']['media'][0]['data']
                    ],
                ],
                "end_of_use_date"=> [
                      "locale"=> null,
                    "channel"=> null,
                    "data"=> null
                ],
                "created"=> new \DateTime(),
                "updated"=> new \DateTime()
            ];



            $client = new Client();
            $request = new Request('PATCH', $url . 'api/rest/v1/asset-families/' . $family . '/assets', $headers, json_encode($body));
            $response = $client->sendAsync($request);
        }
    }

    public function downloadMedia(array $asset): void
    {
        $clientBuilder = new AkeneoPimClientBuilder('https://staging-louispion.cloud.akeneo.com/');
        $client = $clientBuilder->buildAuthenticatedByPassword('7_gbsap62ugy88gkwkcogowcs0o0sowo8gs4gk8wwgs8s0gk888', '3plq4eocxkyswc0cgscw44gsgk0g0cgkw4kggg0s4408gsg4gk', 'dataflow', 'LCoKmMVQwc7gq^');

        $mediaFile = $client->getAssetMediaFileApi()->download($asset['values']['media'][0]['data']);

        file_put_contents('docs/media/media_asset.jpg', $mediaFile->getBody()->getContents());
    }

    public function uploadMedia(AkeneoPimClientInterface $client, array $asset): void
    {
        // WIP
        $regex = '/^(.*\/).*$/';
        preg_match($regex, $asset['values']['media'][0]['data'], $matches);
        $path = $matches[1];

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    public function deleteMedia(): void
    {
        unlink('docs/media/media_asset.jpg');
    }
}
