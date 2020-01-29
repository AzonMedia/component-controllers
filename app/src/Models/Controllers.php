<?php
declare(strict_types=1);

namespace GuzabaPlatform\Controllers\Models;

use Azonmedia\Reflection\ReflectionClass;
use Guzaba2\Base\Base;
use Guzaba2\Kernel\Kernel;
use Guzaba2\Mvc\ActiveRecordController;
use Guzaba2\Orm\ActiveRecord;

class Controllers extends Base
{
    /**
     * Returns a tree of all ActiveRecord classes that are not controllers ActiveRecordController
     * @return array
     * @throws \ReflectionException
     */
    public static function get_tree()
    {
        // get all ActiveRecord classes that are loaded by the Kernel
        $controllers = ActiveRecord::get_active_record_classes(array_keys(Kernel::get_registered_autoloader_paths()));
        $controllers_classes = [];
        $non_controllers_classes = [];

        foreach ($controllers as $class_name) {
            $RClass = new ReflectionClass($class_name);

            if ($RClass->extendsClass(ActiveRecordController::class)) {
                $controllers_classes[$class_name] = $class_name;
            } else {
                $non_controllers_classes[$class_name] = $class_name;
            }
        }

        //$controllers_tree = self::explode_tree($controllers_classes, "\\");
        //$non_controllers_tree = self::explode_tree($non_controllers_classes, "\\", TRUE);

        //return [$controllers_tree, $non_controllers_tree];
        //$controllers_tree = self::explode_tree($controllers_classes, "\\");
        $controllers_tree = self::explode_tree($controllers_classes, "\\", TRUE);
        return $controllers_tree;
    }

    private static function explode_tree(array $array, string $delimiter = '\\', bool $add_crud_actions = FALSE, $baseval = false)
    {

        $split_regular_expression   = '/' . preg_quote($delimiter, '/') . '/';

        $return_arr = array();

        foreach ($array as $key => $val) {
            // Get parent parts and the current leaf
            $parts	= preg_split($split_regular_expression, $key, -1, PREG_SPLIT_NO_EMPTY);
            $leaf_part = array_pop($parts);

            // Build parent structure
            // Might be slow for really deep and large structures
            $parent_arr = &$return_arr;

            foreach ($parts as $part) {
                if (!isset($parent_arr[$part])) {
                    $parent_arr[$part] = array();
                } elseif (!is_array($parent_arr[$part])) {
                    if ($baseval) {
                        $parent_arr[$part] = array('__base_val' => $parent_arr[$part]);
                    } else {
                        $parent_arr[$part] = array();
                    }
                }
                $parent_arr = &$parent_arr[$part];
            }

            /*
            // Add the final part to the structure
            if (empty($parent_arr[$leaf_part])) {
                $RClass = new ReflectionClass($val);

                $methods_arr = [];
                foreach ($RClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $RMethod) {
                    if ($RMethod->class == $val && substr($RMethod->name, 0, 1 ) !== '_' && !$RMethod->isStatic() ) {
                        $methods_arr[$RMethod->name] = $val . "::" . $RMethod->name;
                    }
                }

                if($add_crud_actions) {
                    $methods_arr = array_merge([
                        'create' => $val . '::create',
                        'read' => $val . '::read',
                        'write' => $val . '::write',
                        'delete' => $val . '::delete',
                        'grant_permission' => $val . '::grant_permission',
                        'revoke_permission' => $val . '::revoke_permission'
                    ], $methods_arr);
                }

                $parent_arr[$leaf_part] = $methods_arr;
            } elseif ($baseval && is_array($parent_arr[$leaf_part])) {
                $parent_arr[$leaf_part]['__base_val'] = $val;
            }
            */

            //instead of showing the methods in the tree show all methods in the popup
            if (empty($parent_arr[$leaf_part])) {
                $parent_arr[$leaf_part] = $val;
            }
        }
        return $return_arr;
    }
}