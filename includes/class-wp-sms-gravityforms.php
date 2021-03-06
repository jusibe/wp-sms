<?php

/**
 * WP SMS gravityforms
 *
 * @category   class
 * @package    WP_SMS
 * @version    1.0
 */
class WP_SMS_Gravityforms
{
    static function get_field($form_id)
    {
        $option_field = array();

        if (!$form_id) {
            return $option_field;
        }

        if (!class_exists('RGFormsModel')) {
            return $option_field;
        }

        $fields = RGFormsModel::get_form_meta($form_id);

        if ($fields) {
            foreach ($fields['fields'] as $field) {
                if (isset($field['label'])) {
                    $option_field[$field['id']] = $field['label'];
                } elseif (isset($field->label)) {
                    $option_field[$field->id] = $field->label;
                }
            }

            return $option_field;
        }
    }
}

new WP_SMS_Gravityforms();