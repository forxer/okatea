<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Database\ConfigLayers;

use Okatea\Tao\Application;

class Validator
{
    /**
     * Okatea application instance.
     *
     * @var Okatea\Tao\Application
     */
    protected $okt;

    public function __construct(Application $okt)
    {
        $this->okt = $okt;
    }

    public function validate(array $aFields, array $aData)
    {

    }
}
