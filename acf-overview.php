<?php
/**
 * Plugin Name:     ACF Overview
 * Plugin URI:      https://nopticon.com
 * Description:     ACF Overview allows to quick view the configuration of all field groups
 * Author:          Guillermo Azurdia
 * Author URI:      https://nopticon.com
 * Text Domain:     acf-overview
 * Domain Path:     /lang
 * Version:         1.0.0
 *
 * @package         acf_overview
 */

namespace acf_overview;

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists('acf_overview') ) :

class acf_overview {
    public $plugin_name;
    public $settings;

    public function __construct () {
        $this->plugin_name = plugin_basename( dirname( __FILE__ ) );

        add_action('init',	array($this, 'init'), 5);
    }

    public function init () {
        // Check if ACF is installed and activated
        if ( !function_exists('acf') ) {
            return;
        }

        // settings
        $this->settings = array(
            'version' => '1.0.0',
            'url'     => plugin_dir_url( __FILE__ ),
            'path'    => plugin_dir_path( __FILE__ )
        );

        load_plugin_textdomain( 'acf-overview', false, $this->plugin_name . '/lang' );

        if ( is_admin() ) {
            add_action('admin_menu', array($this, 'admin_menu'), 11);
            add_filter('acf_overview/columns', array( $this, 'filter_columns' ), 10, 2);
        }
    }

    public function admin_menu () {
        // vars
        if ( function_exists('acf_get_setting') ) {
            $slug = 'edit.php?post_type=acf-field-group';
            $cap  = acf_get_setting('capability');
        } else {
            $slug = 'edit.php?post_type=acf';
		    $cap  = 'manage_options';
        }

		// add children
		add_submenu_page($slug, __('Overview', 'acf-overview'), __('Overview','acf-overview'), $cap, 'overview', array($this, 'render') );
    }

    public function render () {
        $groups = $this->get_groups();

        $v5_notice = false;
        if (false === $groups) {
            $v5_notice = __('ACF Overview is compatible with ACF v5', 'acf-overview');
            $groups    = array();
        }

        echo $this->get_view('views/index', array(
            'title'  => __('ACF Overview','acf-overview'),
            'v5_notice' => $v5_notice,
            'groups' => $groups,
        ));
    }

    public function anchor ($text) {
        return str_replace(' ', '', $text);
    }

    public function table ($fields) {
        $columns = $this->columns( $fields );

        echo $this->get_view('views/table', array(
            'columns' => $columns,
            'fields'  => $fields,
        ));
    }

    public function width () {
        return number_format( 100 / count( $this->columns() ), 2 );
    }

    public function colspan ( $columns ) {
        return count( $columns ) - 1;
    }

    public function get_groups () {
        $_groups = function_exists('acf_get_field_groups') ? acf_get_field_groups() : false;

        if (false === $_groups) {
            return $_groups;
        }

        $groups  = array();

        foreach ( $_groups as $i => $group ) {
            $group['fields'] = $this->fields( acf_get_fields($group) );

            $groups[] = $group;
        }

        return $groups;
    }

    public function prop_yes_no ($value) {
        $options = array(
			'<i style="color: #CCC">' . __('No','acf') . '</i>',
            __('Yes','acf'),
        );

        $value = isset($options[ $value ]) ? $options[ $value ] : $value;

        return $value;
    }

    public function options_list ($defaults, $field) {
        $list = array();

        foreach ( $defaults as $name => $default ) {
            if ('sub_fields' === $name) {
                continue;
            }

            $value = isset( $field[ $name ] ) ? $field[ $name ] : $default;

            if ( is_array( $value ) ) {
                $value = !empty( $value ) ? acf_encode_choices( $value ) : '';
            }

            if ( empty($value) ) {
                continue;
            }

            switch ( $name ) {
                case 'allow_null':
                    $value = $this->prop_yes_no( $value );
                    break;
                case 'choices':
                    $value = str_replace("\n", '<br />', $value);
                    break;
            }

            $list[ $name ] = $value;
        }

        return $list;
    }

    public function options ($defaults, $field) {
        $list = $this->options_list($defaults, $field);

        $table = '';
        if ( $list ) {
            $table = $this->get_view('views/options', array(
                'list' => $list,
            ));
        }

        return $table;
    }

    public function filter_columns ($columns, $fields) {
        $list = array();

        foreach ($fields as $i => $field) {
            foreach ($field as $key => $value) {
                $list[ $key ][] = $value;
            }
        }

        foreach ( $list as $key => $values ) {
            $list[ $key ] = array_filter( $list[ $key ] );

            if ( empty( $list[ $key ] ) ) {
                unset( $columns[ $key ] );
            }
        }

        return $columns;
    }

    public function columns ($fields) {
        $columns = array(
            'key'      => __('Key', 'acf'),
            'type'     => __('Type', 'acf'),
            'label'    => __('Label', 'acf'),
            'name'     => __('Name', 'acf'),
            'required' => __('Required?', 'acf'),
            'width'    => __('Width', 'acf'),
            'class'    => __('Class', 'acf'),
            'id'       => __('ID', 'acf'),
            'options'  => __('Options', 'acf'),
        );

        $columns = apply_filters('acf_overview/columns', $columns, $fields);

        return $columns;
    }

    public function fields ($list) {
        $fields = array();

        foreach ( $list as $i => $field ) {
            $field = acf_get_valid_field( $field );
            $type  = acf_get_field_type_prop( $field['type'], 'label' );

            if ( !$type ) {
                $type = '<i style="color: red;">' . $field['type'] . '</i>';
            }

            $options = acf_get_field_type( $field['type'] );

            $options = isset( $options->defaults ) ? $options->defaults : array();
            $options = $this->options( $options, $field );

            $value = apply_filters('acf_overview/field=' . $field['type'], $field);

            $fields[$i] = [
                'key'      => $field['key'],
                'type'     => $type,
                'label'    => $field['label'],
                'name'     => $field['name'],
                'required' => $this->prop_yes_no( $field['required'] ),
                'width'    => $field['wrapper']['width'],
                'class'    => $field['wrapper']['class'],
                'id'       => $field['wrapper']['id'],
                'children' => array(),
                'options'  => $options,
            ];

            switch ( $field['type'] ) {
                case 'repeater':
                case 'group':
                    $fields[$i]['children'] = $this->fields($field['sub_fields']);

                    if ( empty( $fields[ $i ]['children'] ) ) {
                        unset( $fields[ $i ] );
                    }
                    break;
            }
        }

        return $fields;
    }

    public function get_view ($path, $defined = false) {
        if ( false !== $defined ) {
            extract( $defined );
        }

        if ( false === strpos($path, '.php') ) {
            $path .= '.php';
        }

        ob_start();

        require $path;

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}

function acf_overview () {
    global $acf_overview;

    if ( !isset($acf_overview) ) {
        $acf_overview = new acf_overview;
    }

    return $acf_overview;
}

if ( !function_exists('dd') ) {
    function dd ($mixed, $finish = 0) {
        echo '<pre>';
        print_r($mixed);
        echo '</pre>';

        if ( $finish ) {
            exit;
        }
    }
}

acf_overview();

endif; // class_exists check
