<?php
/**
 * Product structured data (JSON-LD), on top of what WooCommerce's own
 * class-wc-structured-data.php already outputs on the single-product
 * template (name, url, description, image, sku/gtin, offers, aggregateRating,
 * review - via WC_Structured_Data::generate_product_data(), hooked to
 * woocommerce_single_product_summary and printed on wp_footer). This file
 * fills in the rest of what Google's Product rich result docs list as
 * required/recommended and WooCommerce leaves out: brand, itemCondition,
 * size/color/material (from real product attributes), extra gallery images,
 * and the two nested types needed for full Merchant listing eligibility -
 * hasMerchantReturnPolicy and shippingDetails, both attached to each offer.
 *
 * Brand and the return/shipping terms are per-product fields on "Página de
 * producto CANUT" (field_pc_structured_data_* in inc/acf-fields/product-canut.php,
 * tab "10. Datos estructurados") rather than a single sitewide setting, since
 * they can legitimately differ product to product; each falls back to the
 * value already visible on the page (5-day returns, 1-3 day shipping) when
 * left empty.
 *
 * @see https://developers.google.com/search/docs/appearance/structured-data/product
 * @package air-light
 */

namespace Air_Light;

/**
 * Reads a per-product structured-data field from "Página de producto CANUT",
 * falling back when empty or ACF isn't active.
 *
 * @param string      $name    ACF field name.
 * @param \WC_Product $product
 * @param mixed       $default Fallback value.
 * @return mixed
 */
function get_product_structured_data_field( $name, $product, $default = '' ) {
  if ( ! function_exists( 'get_field' ) ) {
    return $default;
  }

  $value = get_field( $name, $product->get_id() );

  return ( '' !== $value && null !== $value ) ? $value : $default;
} // end get_product_structured_data_field

/**
 * Maps a WooCommerce attribute name to the schema.org Product property it
 * represents, so real attribute values (not fabricated ones) can be added to
 * the JSON-LD. Returns null for attributes with no schema.org equivalent.
 *
 * @param string $attribute_name
 * @return string|null
 */
function structured_data_attribute_property( $attribute_name ) {
  $name = remove_accents( mb_strtolower( $attribute_name ) );

  if ( str_contains( $name, 'color' ) || str_contains( $name, 'colour' ) ) {
    return 'color';
  }

  if ( str_contains( $name, 'material' ) ) {
    return 'material';
  }

  if ( str_contains( $name, 'tamano' ) || str_contains( $name, 'talla' ) || str_contains( $name, 'size' ) ) {
    return 'size';
  }

  return null;
} // end structured_data_attribute_property

add_filter( 'woocommerce_structured_data_product', __NAMESPACE__ . '\extend_product_structured_data', 10, 2 );
/**
 * @param array       $markup  Product JSON-LD markup built by WooCommerce.
 * @param \WC_Product $product
 * @return array
 */
function extend_product_structured_data( $markup, $product ) {
  $markup['brand'] = [
    '@type' => 'Brand',
    'name'  => get_product_structured_data_field( 'structured_data_brand', $product, get_bloginfo( 'name' ) ),
  ];

  // More than one photo: give Google the full gallery instead of just the
  // featured image WooCommerce sets by default.
  $gallery_image_ids = array_values( array_unique( array_filter( array_merge(
    [ $product->get_image_id() ],
    $product->get_gallery_image_ids()
  ) ) ) );

  if ( count( $gallery_image_ids ) > 1 ) {
    $gallery_image_urls = array_values( array_filter( array_map( 'wp_get_attachment_url', $gallery_image_ids ) ) );

    if ( $gallery_image_urls ) {
      $markup['image'] = $gallery_image_urls;
    }
  }

  // size/color/material straight from the product's real WooCommerce
  // attributes, when it has them - never fabricated.
  foreach ( $product->get_attributes() as $attribute ) {
    $schema_property = structured_data_attribute_property( $attribute->get_name() );

    if ( ! $schema_property || ! empty( $markup[ $schema_property ] ) ) {
      continue;
    }

    $values = $attribute->is_taxonomy()
      ? wc_get_product_terms( $product->get_id(), $attribute->get_name(), [ 'fields' => 'names' ] )
      : $attribute->get_options();

    if ( $values ) {
      $markup[ $schema_property ] = implode( ', ', $values );
    }
  }

  // Nothing to attach itemCondition/returns/shipping to without an offer
  // (out of stock or unpriced products don't get one - see
  // WC_Structured_Data::generate_product_data()).
  if ( empty( $markup['offers'] ) || ! is_array( $markup['offers'] ) ) {
    return $markup;
  }

  // Applicable/destination country is real WooCommerce store configuration
  // (Ajustes > General > Ubicación del negocio), not something to duplicate
  // as a separate field.
  $country = wc_get_base_location()['country'];

  $return_policy = [
    '@type'                => 'MerchantReturnPolicy',
    'applicableCountry'    => $country,
    'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
    'merchantReturnDays'   => (int) get_product_structured_data_field( 'structured_data_return_days', $product, 5 ),
    'returnMethod'         => get_product_structured_data_field( 'structured_data_return_method', $product, 'https://schema.org/ReturnByMail' ),
    'returnFees'           => get_product_structured_data_field( 'structured_data_return_fees', $product, 'https://schema.org/ReturnFeesCustomerResponsibility' ),
  ];

  $shipping_details = [
    '@type'               => 'OfferShippingDetails',
    'shippingRate'        => [
      '@type'    => 'MonetaryAmount',
      'value'    => (string) get_product_structured_data_field( 'structured_data_shipping_cost', $product, '0' ),
      'currency' => get_woocommerce_currency(),
    ],
    'shippingDestination' => [
      '@type'          => 'DefinedRegion',
      'addressCountry' => $country,
    ],
    'deliveryTime'        => [
      '@type'       => 'ShippingDeliveryTime',
      'transitTime' => [
        '@type'    => 'QuantitativeValue',
        'minValue' => (int) get_product_structured_data_field( 'structured_data_shipping_days_min', $product, 1 ),
        'maxValue' => (int) get_product_structured_data_field( 'structured_data_shipping_days_max', $product, 3 ),
        'unitCode' => 'DAY',
      ],
    ],
  ];

  foreach ( $markup['offers'] as &$offer ) {
    $offer['itemCondition']           = 'https://schema.org/NewCondition';
    $offer['hasMerchantReturnPolicy'] = $return_policy;
    $offer['shippingDetails']         = $shipping_details;
  }
  unset( $offer );

  return $markup;
} // end extend_product_structured_data
