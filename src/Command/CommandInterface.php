<?php

declare(strict_types=1);

namespace App\Command;

/**
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2004-present Agence Dn'D
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.dnd.fr/
 */
interface CommandInterface
{
    public const PATH_ASSETS = 'docs/assets/';

    public const PATH_FAMILIES = 'docs/families/families.csv';

    public const PATH_MEDIA = 'docs/media/';

    public const LIST_CODES = 'docs/assets/process/codes_list.txt';
}
