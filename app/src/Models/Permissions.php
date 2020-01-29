<?php
declare(strict_types=1);

namespace GuzabaPlatform\Controllers\Models;

use Guzaba2\Authorization\Acl\Permission;
use Guzaba2\Base\Base;
use GuzabaPlatform\Platform\Application\MysqlConnectionCoroutine;

class Permissions extends Base
{
    protected const CONFIG_DEFAULTS = [
        'services'      => [
            'ConnectionFactory'
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * Returns the permissions of the controllers.
     * @param string $class_name
     * @param string $action_name
     * @return mixed
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     */
    //public static function get_permissions(string $class_name, string $action_name)
    public static function get_permissions(string $class_name) : iterable
    {
        $Connection = self::get_service('ConnectionFactory')->get_connection(MysqlConnectionCoroutine::class, $ScopeReference);

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
        acl_permissions.class_name = :class_name
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
        meta.meta_class_name = :meta_class_name
-- WHERE
--    (users.user_id IS NULL OR users.user_id = 1)
ORDER BY
    roles.role_name
";

        $data = $Connection->prepare($q)->execute(['class_name' => $class_name, 'meta_class_name' => Permission::class])->fetchAll();

        $ret = [];
        //$object_actions = $class_name::get_class_actions();
        $object_actions = $class_name::get_actions();
        print_r($object_actions);
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