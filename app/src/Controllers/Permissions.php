<?php
declare(strict_types=1);

namespace GuzabaPlatform\Controllers\Controllers;

use Guzaba2\Http\Method;
use GuzabaPlatform\Platform\Application\BaseController;
use Psr\Http\Message\ResponseInterface;

class Permissions extends BaseController
{
    protected const CONFIG_DEFAULTS = [
        'routes'        => [
            //'/admin/permissions-classes/{class_name}/{method_name}' => [
            '/admin/permissions-controllers/{class_name}' => [
                Method::HTTP_GET    => [self::class, 'main']
            ],
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * Returns the permissions of a single Controller (for all of its actions).
     * @param string $method_name
     */
    //public function main(string $method_name): ResponseInterface
    //public function main(string $class_name, string $method_name): ResponseInterface
    public function main(string $class_name): ResponseInterface
    {
        $struct = [];

        //list($class_name, $action_name) = explode("::", $method_name);
        //$class_name = str_replace(".", "\\", $class_name);
        if (strpos($class_name,'-') !== FALSE) {
            $class_name = str_replace('-', '\\', $class_name);
        }

        //$struct['items'] = \GuzabaPlatform\Platform\Crud\Models\Permissions::get_permissions($class_name, $action_name);
        //$struct['items'] = \GuzabaPlatform\Classes\Models\Permissions::get_permissions($class_name, $method_name);
        $struct['items'] = \GuzabaPlatform\Controllers\Models\Permissions::get_permissions($class_name);

        $Response = parent::get_structured_ok_response($struct);
        return $Response;
    }
}