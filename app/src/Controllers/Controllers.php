<?php
declare(strict_types=1);

namespace GuzabaPlatform\Controllers\Controllers;

use Guzaba2\Http\Method;
use GuzabaPlatform\Platform\Application\BaseController;
use Psr\Http\Message\ResponseInterface;

class Controllers extends BaseController
{
    protected const CONFIG_DEFAULTS = [
        'routes'        => [
            '/admin/controllers' => [
                Method::HTTP_GET => [self::class, 'main']
            ],
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * returns a list with all Controller classes
     */
    public function main(): ResponseInterface
    {
        $struct['tree'] = [];

        //$classes = \GuzabaPlatform\Platform\Crud\Models\Permissions::get_tree();

        $classes = \GuzabaPlatform\Controllers\Models\Controllers::get_tree();

//        $struct['tree'] = [
//            'Controllers' => $classes[0],
//            'Non-Controllers' => $classes[1]
//        ];
        $struct['tree'] = $classes;

        $Response = parent::get_structured_ok_response($struct);
        return $Response;
    }
}