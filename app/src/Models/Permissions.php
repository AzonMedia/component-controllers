<?php
declare(strict_types=1);

namespace GuzabaPlatform\Controllers\Models;

use Guzaba2\Authorization\Acl\Permission;
use Guzaba2\Base\Base;
use Guzaba2\Base\Exceptions\InvalidArgumentException;
use Guzaba2\Base\Exceptions\RunTimeException;
use Guzaba2\Database\Interfaces\ConnectionInterface;
use Guzaba2\Kernel\Kernel;
use Guzaba2\Orm\Store\Sql\Mysql;
use GuzabaPlatform\Platform\Application\MysqlConnectionCoroutine;

/**
 * Class Permissions
 * @package GuzabaPlatform\Controllers\Models
 */
abstract class Permissions extends Base
{
    protected const CONFIG_DEFAULTS = [
        'services'      => [
            'ConnectionFactory',
            'MysqlOrmStore',//needed because the get_class_id() method is used
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * Returns the permissions of the controllers.
     * @param string $class_name
     * @return mixed
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws RunTimeException
     * @throws \ReflectionException
     */
    //public static function get_permissions(string $class_name, string $action_name)
    public static function get_permissions(string $class_name) : iterable
    {
        /** @var ConnectionInterface $Connection */
        $Connection = self::get_service('ConnectionFactory')->get_connection(MysqlConnectionCoroutine::class, $CR);

        $q = "
SELECT
    roles.*,
    meta.meta_object_uuid,
    acl_permissions.action_name
FROM
    {$Connection::get_tprefix()}roles as roles
LEFT JOIN
    {$Connection::get_tprefix()}acl_permissions as acl_permissions
    ON
        acl_permissions.role_id = roles.role_id
    AND
        acl_permissions.class_id = :class_id
    AND
        acl_permissions.object_id IS NULL
LEFT JOIN
    {$Connection::get_tprefix()}users as users
    ON
        users.user_id = roles.role_id
LEFT JOIN
    {$Connection::get_tprefix()}object_meta as meta
    ON
        meta.meta_object_id = acl_permissions.permission_id
    AND
        meta.meta_class_id = :meta_class_id
-- WHERE
--    (users.user_id IS NULL OR users.user_id = 1)
ORDER BY
    roles.role_name
";
        //meta.meta_class_name = :meta_class_name
        //acl_permissions.class_name = :class_name

        //$data = $Connection->prepare($q)->execute(['class_name' => $class_name, 'meta_class_name' => Permission::class])->fetchAll();
        /** @var Mysql $MysqlOrmStore */
        $MysqlOrmStore = self::get_service('MysqlOrmStore');
        $data = $Connection->prepare($q)->execute(['class_id' => $MysqlOrmStore->get_class_id($class_name), 'meta_class_id' => $MysqlOrmStore->get_class_id(Permission::class) ] )->fetchAll();

        $ret = [];
        //$object_actions = $class_name::get_class_actions();
        $object_actions = $class_name::get_actions();

        foreach ($data as $row) {
            if (!array_key_exists($row['role_id'], $ret)) {
                $ret[$row['role_id']]['role_id'] = $row['role_id'];
                $ret[$row['role_id']]['role_name'] = $row['role_name'];
                $ret[$row['role_id']]['permissions'] = [];
            }

            foreach ($object_actions as $object_action) {
                if ($row['action_name'] && $row['action_name'] === $object_action) {
                    $ret [$row['role_id']] ['permissions'] [ $object_action ] = $row['meta_object_uuid'];
                } elseif (!array_key_exists($object_action, $ret[$row['role_id']]['permissions'] )) {
                    $ret [$row['role_id']] ['permissions'] [ $object_action ] = '';
                }
            }

        }

        return $ret;


    }
}