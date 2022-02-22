<?php
/**
 * Handle the item object.
 *
 * @package     EverAccounting\Models
 * @class       Item
 * @version     1.1.0
 */

namespace EverAccounting\Models;

use EverAccounting\Abstracts\Resource_Model;
use EverAccounting\Repositories;
use EverAccounting\Traits\Attachment;
use EverAccounting\Traits\CurrencyTrait;

defined( 'ABSPATH' ) || exit;

/**
 * Class Item
 *
 * @since   1.1.0
 *
 * @package EverAccounting\Models
 */
class Item extends Resource_Model {
	use Attachment;

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'item';

	/**
	 * @since 1.1.0
	 *
	 * @var string
	 */
	public $cache_group = 'ea_items';

	/**
	 * Item Data array.
	 *
	 * @since 1.0.4
	 *
	 * @var array
	 */
	protected $data = array(
		'name'           => '',
		'sku'            => '',
		'thumbnail_id'   => null,
		'description'    => '',
		'sale_price'     => 0.0000,
		'purchase_price' => 0.0000,
		'quantity'       => 1,
		'category_id'    => null,
		'sales_tax'      => null,
		'purchase_tax'   => null,
		'enabled'        => 1,
		'creator_id'     => null,
		'date_created'   => null,
	);

	/**
	 * Get the item if ID is passed, otherwise the item is new and empty.
	 *
	 * @param int|string|object|Item $item Item object to read.
	 */
	public function __construct( $item = 0 ) {
		parent::__construct( $item );

		if ( $item instanceof self ) {
			$this->set_id( $item->get_id() );
		} elseif ( is_numeric( $item ) ) {
			$this->set_id( $item );
		} elseif ( ! empty( $item->id ) ) {
			$this->set_id( $item->id );
		} elseif ( is_array( $item ) ) {
			$this->set_props( $item );
		} else {
			$this->set_object_read( true );
		}

		//Load repository
		$this->repository = Repositories::load( 'items' );

		if ( $this->get_id() > 0 ) {
			$this->repository->read( $this );
		}

		$this->required_props = array(
			'name'           => __( 'Item name', 'wp-ever-accounting' ),
			'quantity'       => __( 'Item Quantity', 'wp-ever-accounting' ),
			'purchase_price' => __( 'Item Purchase Price', 'wp-ever-accounting' ),
			'sale_price'     => __( 'Item Sale Price', 'wp-ever-accounting' ),
		);
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD methods
	|--------------------------------------------------------------------------
	|
	| Methods which create, read, update and delete items from the database.
	|
	*/

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting item data. Getter methods wont change anything unless
	| just returning from the props.
	|
	*/

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_name( $context = 'edit' ) {
		return $this->get_prop( 'name', $context );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_sku( $context = 'edit' ) {
		return $this->get_prop( 'sku', $context );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_thumbnail_id( $context = 'edit' ) {
		return $this->get_prop( 'thumbnail_id', $context );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_description( $context = 'edit' ) {
		return $this->get_prop( 'description', $context );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_sale_price( $context = 'edit' ) {
		return $this->get_prop( 'sale_price', $context );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_purchase_price( $context = 'edit' ) {
		$price = $this->get_prop( 'purchase_price', $context );
		if ( empty( $price ) ) {
			$price = $this->get_sale_price();
		}

		return $price;
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_quantity( $context = 'edit' ) {
		return $this->get_prop( 'quantity', $context );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_category_id( $context = 'edit' ) {
		return $this->get_prop( 'category_id', $context );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_sales_tax( $context = 'edit' ) {
		return $this->get_prop( 'sales_tax', $context );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.1.0
	 *
	 */
	public function get_purchase_tax( $context = 'edit' ) {
		return $this->get_prop( 'purchase_tax', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting item data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	*/

	/**
	 * @param $name
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_name( $name ) {
		$this->set_prop( 'name', eaccounting_clean( $name ) );
	}

	/**
	 * @param $sku
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_sku( $sku ) {
		$this->set_prop( 'sku', eaccounting_clean( $sku ) );
	}

	/**
	 * @param $thumbnail_id
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_thumbnail_id( $thumbnail_id ) {
		$this->set_prop( 'thumbnail_id', absint( $thumbnail_id ) );
	}

	/**
	 * @param $description
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_description( $description ) {
		$this->set_prop( 'description', sanitize_textarea_field( $description ) );
	}

	/**
	 * @param $sale_price
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_sale_price( $sale_price ) {
		$this->set_prop( 'sale_price', eaccounting_format_decimal( $sale_price, 4 ) );
	}

	/**
	 * @param $purchase_price
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_purchase_price( $purchase_price ) {
		$this->set_prop( 'purchase_price', eaccounting_format_decimal( $purchase_price, 4 ) );
	}

	/**
	 * @param $quantity
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_quantity( $quantity ) {
		$this->set_prop( 'quantity', absint( $quantity ) );
	}

	/**
	 * @param $category_id
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_category_id( $category_id ) {
		$this->set_prop( 'category_id', absint( $category_id ) );
	}

	/**
	 * @param $tax
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_sales_tax( $tax ) {
		$this->set_prop( 'sales_tax', eaccounting_format_decimal( $tax, 4 ) );
	}

	/**
	 * @param $tax_ids
	 *
	 * @since 1.1.0
	 *
	 */
	public function set_purchase_tax( $tax ) {
		$this->set_prop( 'purchase_tax', eaccounting_format_decimal( $tax, 4 ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Additional methods
	|--------------------------------------------------------------------------
	|
	| Does extra thing as helper functions.
	|
	*/

}
