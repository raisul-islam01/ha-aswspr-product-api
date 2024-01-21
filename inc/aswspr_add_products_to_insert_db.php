<?php

function aswspr_product_api() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://ha.aswspr.com/API/ghPR.ashx',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => 'tkn=9AFE2B75-30DD-45EB-B47B-DE7140AEFEC6&cmd=get&com=001&tbl=IM_Products_Products_Per_Warehouse_View&crs=LIKE&cdn=AND&rst=jso',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
        ),
    ));

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        // Handle cURL errors
        echo 'cURL Error: ' . curl_error($curl);
    }

    curl_close($curl);

    return $response;
}

// Insert products to database
function insert_products_to_db_callback() {
    ob_start();

    $api_response = aswspr_product_api();

    $products = json_decode($api_response, true);

    if (is_array($products)) {
        
        global $wpdb;

        $table_name = $wpdb->prefix . 'sync_products';
        $wpdb->query("TRUNCATE TABLE $table_name");

        // Insert to database
        foreach ($products as $product) {
            $product_data = json_encode($product);
            $wpdb->insert(
                $table_name,
                [
                    'operation_type' => 'product_create',
                    'operation_value' => $product_data,
                    'status' => 'pending',
                ]
            );
        }

        echo '<h4>Products inserted successfully</h4>';
    }

    return ob_get_clean();
}

add_shortcode('insert_products', 'insert_products_to_db_callback');
