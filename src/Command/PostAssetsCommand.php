<?php

declare(strict_types=1);

namespace App\Command;

use Akeneo\Pim\ApiClient\{
    AkeneoPimClientBuilder,
    AkeneoPimClientInterface,
};
use Symfony\Component\Console\{
    Attribute\AsCommand,
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface,
    Style\SymfonyStyle
};
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

            $this->uploadAssets($client, array_values($family)[0], $assets);
        }

        // $mediaFileCode = $client->getAssetMediaFileApi()->create('????????.png');

        $io->success('SUCCESS');
        return Command::SUCCESS;
    }

    public function uploadAssets(AkeneoPimClientInterface $client, string $family, array $assets):void
    {
        $headers = [];

        foreach ($assets as $asset) {
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

            $request = new Request('PUT', '', $headers, $body);

            //$client->getAssetApi()->upsert($asset['code'], [
            //    'localizable'     => null,
            //    'description'     => 'The wonderful unicorn',
            //    'end_of_use'      => '2042-11-21',
            //    'tags'            => ['colored', 'flowers'],
            //    'categories'      => ['face', 'pack'],
            //]);
        }
    }
}
