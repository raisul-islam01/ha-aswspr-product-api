<?php
require_once HA_PLUGIN_PATH . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

// WordPress WooCommerce product insert shortcode function
function products_insert_woocommerce_callback()
{
    ob_start();

    // Get global $wpdb object
    global $wpdb;

    // Define table names
    $table_name_products = $wpdb->prefix . 'sync_products';

    // Retrieve pending products from the database
    $products = $wpdb->get_results("SELECT * FROM $table_name_products WHERE status = 'pending' LIMIT 1");

    // Loop through each pending product
    foreach ($products as $product) {
        $product_data = json_decode($product->operation_value, true);

        // Vendor file
        $website_url = home_url();
        $consumer_key = 'ck_6f272088802087dace42dc73c5b4168680df9a96';
        $consumer_secret = 'cs_4c5f0525dcebbd3f2d350f494fba9060b8d43e4b';

        // Extract product details from the decoded data
        $warehouse_code = $product_data['WarehouseCode'] ?? '';
        $warehouse_name = $product_data['WarehouseName'] ?? '';
        $product_code = $product_data['ProductCode'] ?? '';
        $product_name = $product_data['ProductName'] ?? '';
        $department_code = $product_data['DepartmentCode'] ?? '';
        $department_name = $product_data['DepartmentName'] ?? '';
        $product_price = $product_data['StandardPrice'] ?? '';

        // Group code and group name
        $group_code1 = $product_data['GroupCode1'] ?? '';
        $group_name1 = $product_data['GroupName1'] ?? '';
        $group_code2 = $product_data['GroupCode2'] ?? '';
        $group_name2 = $product_data['GroupName2'] ?? '';
        $group_code3 = $product_data['GroupCode3'] ?? '';
        $group_name3 = $product_data['GroupName3'] ?? '';
        $group_code4 = $product_data['GroupCode4'] ?? '';
        $group_name4 = $product_data['GroupName4'] ?? '';
        $group_code5 = $product_data['GroupCode5'] ?? '';
        $group_name5 = $product_data['GroupName5'] ?? '';
        $group_code6 = $product_data['GroupCode6'] ?? '';
        $group_name6 = $product_data['GroupName6'] ?? '';
        $group_code7 = $product_data['GroupCode7'] ?? '';
        $group_name7 = $product_data['GroupName7'] ?? '';
        $group_code8 = $product_data['GroupCode8'] ?? '';
        $group_name8 = $product_data['GroupName8'] ?? '';
        $type_code = $product_data['TypeCode'] ?? '';
        $valuation_code = $product_data['ValuationCode'] ?? '';
        $vendor_code = $product_data['VendorCode'] ?? '';
        $vendor_name = $product_data['VendorName'] ?? '';
        $family_code = $product_data['FamilyCode'] ?? '';
        $family_name = $product_data['FamilyName'] ?? '';
        $brand_code = $product_data['BrandCode'] ?? '';
        $brand_name = $product_data['BrandName'] ?? '';
        $model_code = $product_data['Model'] ?? '';
        $model_name = $product_data['ModelName'] ?? '';
        $description = $product_data['Description'] ?? '';
        $weight = $product_data['NetWeight'] ?? '';
        $length = $product_data['Length'] ?? '';
        $width = $product_data['Width'] ?? '';
        $height = $product_data['Height'] ?? '';
        $volume = $product_data['Volume'] ?? '';
        $stock = $product_data['Quantity1'] ?? '';

        // Set up the API client with your WooCommerce store URL and credentials
        $client = new Client(
            $website_url,
            $consumer_key,
            $consumer_secret,
            [
                'verify_ssl' => false,
            ]
        );

        // If SKU already exists, update the product
        $args = [
            'post_type' => 'product',
            'meta_query' => [
                [
                    'key' => '_sku',
                    'value' => $product_code,
                    'compare' => '=',
                ],
            ],
        ];

        // Check if the product already exists
        $existing_products = new WP_Query($args);

        if ($existing_products->have_posts()) {
            $existing_products->the_post();

            // Get product ID
            $product_id = get_the_ID();

            // Update the status of the processed product in your database
            $wpdb->update(
                $table_name_products,
                ['status' => 'completed'],
                ['id' => $product->id]
            );

            // Update the product
            $product_data = [
                'name' => $product_name,
                'sku' => $product_code,
                'type' => 'simple',
                'description' => $description,
                'regular_price' => $product_price,
                'attributes' => [
                    [
                        'name' => 'Dimensions',
                        'visible' => true,
                        'variation' => true,
                    ],
                ],
            ];

            // Update product
            $client->put('products/' . $product_id, $product_data);
        } else {
            // Create a new product
            $product_data = [
                'name' => $product_name,
                'sku' => $product_code,
                'type' => 'simple',
                'description' => $description,
                'regular_price' => $product_price,
                'attributes' => [
                    [
                        'name' => 'Dimensions',
                        'visible' => true,
                        'variation' => true,
                    ],
                ],
            ];

            // Create the product
            $product = $client->post('products', $product_data);
            $product_id = $product->id;

            // Update the status of the processed product in your database
            $wpdb->update(
                $table_name_products,
                ['status' => 'completed'],
                ['id' => $product->id]
            );
        }

        // Update product meta data
        update_post_meta($product_id, '_sku', $product_code);
        update_post_meta($product_id, '_regular_price', $product_price);
        update_post_meta($product_id, '_price', $product_price);
        update_post_meta($product_id, '_virtual', 'yes');
        update_post_meta($product_id, '_downloadable', 'no');
        update_post_meta($product_id, '_download_limit', -1);
        update_post_meta($product_id, '_download_expiry', -1);

        // Update product meta data in WordPress
        update_post_meta($product_id, '_stock', $stock);

        // Display out-of-stock message if stock is 0
        if ($stock <= 0) {
            update_post_meta($product_id, '_stock_status', 'outofstock');
        } else {
            update_post_meta($product_id, '_stock_status', 'instock');
        }
        update_post_meta($product_id, '_manage_stock', 'yes');
    }

    ob_get_clean();
}

add_shortcode('products_insert_woocommerce', 'products_insert_woocommerce_callback');
