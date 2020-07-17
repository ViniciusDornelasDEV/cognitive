<?php

namespace Application\Factory;

/**
 * MyRouterFactory class.
 *
 * @author   Vinicius Silva <vinicius.s.dornelas@gmail.com>
 * @version 1.0
 */
class MyRouterFactory {

    /**
     * Return a route array to be used in the module.config.php
     * for a module. You can either use this static methor or 
     * write your own array (with custom routing structure, etc.)
     * 
     * @access public
     * @static
     * @param mixed $routeName                  The string that will appear in URL
     * @param mixed $classname                  The classname being instantiated by ZF2 router
     * @param mixed $action                     The action for this class
     * @param mixed $arg (default: null)        Any parameters
     * @param mixed $constraints (default: null) Any constraint on these parameters
     * @return void
     */
    public static function simpleRoute($routeName, $classname, $action, $arg = null, $constraints = null) {
        $route = array(
            'type' => 'segment',
            'options' => array(
                'route' => "/$routeName"
            )
        );

        if (is_array($classname)) {
            // Full namespace
            $route['options']['defaults'] = array(
                '__NAMESPACE__' => $classname[0],
                'controller' => $classname[1],
                'action' => $action
            );
        } else {
            // In same module
            $route['options']['defaults'] = array(
                'controller' => $classname,
                'action' => $action
            );
        }

        // Assemble query arguments
        if (is_array($arg)) {
            $i = 1;
            $args = '';
            foreach ($arg as $_arg) {
                if ($i == 1) {
                    $args .= '[?' . $_arg . '=:' . $_arg . ']';
                } elseif ($i == count($arg)) {
                    $args .= '[&' . $_arg . '=:' . $_arg . ']';
                } else {
                    $args .= '[&' . $_arg . '=:' . $_arg . ']';
                }
                $i++;
            }
            $route['options']['route'] .= $args;
        } elseif ($arg) {
            if (strpos($arg, '=') !== false) {
                $route['options']['route'] .= '[?' . $arg . ']';
            } else {
                $route['options']['route'] .= '[?' . $arg . '=:' . $arg . ']';
            }
        }

        // Assemble argument constraints (if any)
        if (is_array($constraints)) {
            $route['options']['constraints'] = $constraints;
        } elseif ($arg == 'id') {
            $route['options']['constraints'] = array('id' => '[0-9]+');
        }

        return $route;
    }

    public static function routeToChildren() {
        return array(
            'type' => 'Segment',
            'options' => array(
                'route' => '/[:controller[/:action]]',
                'constraints' => array(
                    'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                ),
                'defaults' => array(
                ),
        ));
    }

}
