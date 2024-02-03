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
    $table_name_products  = $wpdb->prefix . 'sync_products';

    // Retrieve pending products from the database
    $products = $wpdb->get_results("SELECT * FROM $table_name_products WHERE status = 'pending' LIMIT 1");

    // Loop through each pending product
    foreach ($products as $product) {
        $product_data = json_decode($product->operation_value, true);
        // echo "<pre>";
        // print_r($product_data);
        // wp_die();
        //vendor file
        $website_url     = home_url();
        $consumer_key    = 'ck_26a38e2c4ae117e15a8ea6cd6f57c92cc9a222af';
        $consumer_secret = 'cs_0d095cf92a02d645099f8d911bfe616d8ed12a17';

        // Extract product details from the decoded data
        $warehouse_code      = isset($product_data['WarehouseCode']) ? $product_data['WarehouseCode'] : '';
        $warehouse_name      = isset($product_data['WarehouseName']) ? $product_data['WarehouseName'] : '';
        $product_code      = isset($product_data['ProductCode']) ? $product_data['ProductCode'] : '';
        $product_name      = isset($product_data['ProductName']) ? $product_data['ProductName'] : '';
        $department_code      = isset($product_data['DepartmentCode']) ? $product_data['DepartmentCode'] : '';
        $department_name      = isset($product_data['DepartmentName']) ? $product_data['DepartmentName'] : '';
        $product_price        = isset($product_data['StandardPrice']) ? $product_data['StandardPrice'] : '';
        //groupcode and groupname
        $group_code1      = isset($product_data['GroupCode1']) ? $product_data['GroupCode1'] : '';
        $group_name1      = isset($product_data['GroupName1']) ? $product_data['GroupName1'] : '';
        $group_code2      = isset($product_data['GroupCode2']) ? $product_data['GroupCode2'] : '';
        $group_name2      = isset($product_data['GroupName2']) ? $product_data['GroupName2'] : '';
        $group_code3      = isset($product_data['GroupCode3']) ? $product_data['GroupCode3'] : '';
        $group_name3      = isset($product_data['GroupName3']) ? $product_data['GroupName3'] : '';
        $group_code4      = isset($product_data['GroupCode4']) ? $product_data['GroupCode4'] : '';
        $group_name4      = isset($product_data['GroupName4']) ? $product_data['GroupName4'] : '';
        $group_code5      = isset($product_data['GroupCode5']) ? $product_data['GroupCode5'] : '';
        $group_name5      = isset($product_data['GroupName5']) ? $product_data['GroupName5'] : '';
        $group_code6      = isset($product_data['GroupCode6']) ? $product_data['GroupCode6'] : '';
        $group_name6      = isset($product_data['GroupName6']) ? $product_data['GroupName6'] : '';
        $group_code7      = isset($product_data['GroupCode7']) ? $product_data['GroupCode7'] : '';
        $group_name7      = isset($product_data['GroupName7']) ? $product_data['GroupName7'] : '';
        $group_code8      = isset($product_data['GroupCode8']) ? $product_data['GroupCode8'] : '';
        $group_name8      = isset($product_data['GroupName8']) ? $product_data['GroupName8'] : '';
        $type_code      = isset($product_data['TypeCode']) ? $product_data['TypeCode'] : '';
        $valuation_code      = isset($product_data['ValuationCode']) ? $product_data['ValuationCode'] : '';
        $vendor_code      = isset($product_data['VendorCode']) ? $product_data['VendorCode'] : '';
        $vendor_name      = isset($product_data['VendorName']) ? $product_data['VendorName'] : '';
        $family_code      = isset($product_data['FamilyCode']) ? $product_data['FamilyCode'] : '';
        $family_name      = isset($product_data['FamilyName']) ? $product_data['FamilyName'] : '';
        $brand_code      = isset($product_data['BrandCode']) ? $product_data['BrandCode'] : '';
        $brand_name      = isset($product_data['BrandName']) ? $product_data['BrandName'] : '';
        $model_code      = isset($product_data['Model']) ? $product_data['Model'] : '';
        $model_name      = isset($product_data['ModelName']) ? $product_data['ModelName'] : '';
        $description      = isset($product_data['Description']) ? $product_data['Description'] : '';
        $weight      = isset($product_data['NetWeight']) ? $product_data['NetWeight'] : '';
        $length      = isset($product_data['Length']) ? $product_data['Length'] : '';
        $width      = isset($product_data['Width']) ? $product_data['Width'] : '';
        $height      = isset($product_data['Height']) ? $product_data['Height'] : '';
        $volume      = isset($product_data['Volume']) ? $product_data['Volume'] : '';
        $stock      = isset($product_data['Quantity1']) ? $product_data['Quantity1'] : '';


        // Set up the API client with your WooCommerce store URL and credentials
        $client = new Client(
            $website_url,
            $consumer_key,
            $consumer_secret,
            [
                'verify_ssl' => false,
            ]
        );

        // if sku already exists, update the product
        $args = array(
            'post_type'  => 'product',
            'meta_query' => array(
                array(
                    'key'     => '_sku',
                    'value'   =>  $product_code,
                    'compare' => '=',
                ),
            ),
        );

        // Check if the product already exists
        $existing_products = new WP_Query($args);

        if ($existing_products->have_posts()) {
            $existing_products->the_post();

            // get product id
            $product_id = get_the_ID();

            // Update the status of the processed product in your database
            $wpdb->update(
                $table_name_products,
                ['status' => 'completed'],
                ['id' => $product->id]
            );

            // Update the product
            $product_data = [
                'name'          =>  $product_name,
                'sku'           =>  $product_code,
                'type'          => 'simple',
                'description'   => $description,
                'regular_price' => $product_price, // Replace 'RegularPrice' with the actual key from your $product_data
                'attributes'    => [
                    [
                        'name'      => 'Dimensions',
                        'visible'   => true,
                        'variation' => true,
                    ],
                ],
            ];

            // update product
            $client->put('products/' . $product_id, $product_data);

        } else {
            // Create a new product
            $product_data = [
                'name'          => $product_name,
                'sku'           => $product_code,
                'type'          => 'simple',
                'description'   => $description,
                'regular_price' => $product_price, // Replace 'RegularPrice' with the actual key from your $product_data
                'attributes'    => [
                    [
                        'name'      => 'Dimensions',
                        'visible'   => true,
                        'variation' => true,
                    ],
                ],
            ];

            // Create the product
            $product    = $client->post('products', $product_data);
            $product_id = $product->id;


            // Update the status of the processed product in your database
            $wpdb->update(
                $table_name_products,
                ['status' => 'completed'],
                ['id' => $product->id]
            );
        }

        // Insert the product
        //$product_id = wp_insert_post($product_args);

        // Update product meta data
        update_post_meta($product_id, '_sku', $product_code);
        update_post_meta($product_id, '_regular_price', $product_price);
        update_post_meta($product_id, '_price', $product_price);
        update_post_meta($product_id, '_virtual', 'yes');
        update_post_meta($product_id, '_downloadable', 'no');
        update_post_meta($product_id, '_download_limit', -1);
        update_post_meta($product_id, '_download_expiry', -1);



        //Update product meta data in WordPress
        update_post_meta($product_id, '_stock', $stock);

        // //display out of stock message if stock is 0
        // if ($stock <= 0) {
        //     update_post_meta($product_id, '_stock_status', 'outofstock');
        // } else {
        //     update_post_meta($product_id, '_stock_status', 'instock');
        // }
        // update_post_meta($product_id, '_manage_stock', 'yes');
        return '<h4>product insert successfully</h4>';
        
    }

    ob_get_clean();
}
add_shortcode('products_insert_woocommerce', 'products_insert_woocommerce_callback');