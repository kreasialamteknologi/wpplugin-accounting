<?php
/**
 * Settings Options Rest Controller Class.
 *
 * @since       1.0.2
 * @subpackage  Rest
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;

use EverAccounting\Abstracts\Controller;

defined( 'ABSPATH' ) || die();

class Setting_Options_Controller extends Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'settings/(?P<group_id>[\w-]+)';


	/**
	 * Get the settings schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'setting',
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'A unique identifier for the setting.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'group_id'    => array(
					'description' => __( 'An identifier for the group this setting belongs to.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'label'       => array(
					'description' => __( 'A human readable label for the setting used in interfaces.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'description' => array(
					'description' => __( 'A human readable description for the setting used in interfaces.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'value'       => array(
					'description' => __( 'Setting value.', 'wp-ever-accounting' ),
					'type'        => 'mixed',
					'context'     => array( 'view', 'edit' ),
				),
				'default'     => array(
					'description' => __( 'Default value for the setting.', 'wp-ever-accounting' ),
					'type'        => 'mixed',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'tip'         => array(
					'description' => __( 'Additional help text shown to the user about the setting.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'placeholder' => array(
					'description' => __( 'Placeholder text to be displayed in text inputs.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'type'        => array(
					'description' => __( 'Type of setting.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'     => array( 'view', 'edit' ),
					'enum'        => array( 'text', 'email', 'number', 'color', 'password', 'textarea', 'select', 'multiselect', 'radio', 'image_width', 'checkbox' ),
					'readonly'    => true,
				),
				'options'     => array(
					'description' => __( 'Array of options (key value pairs) for inputs such as select, multiselect, and radio buttons.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
