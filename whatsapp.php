<?php
include("../db/db.php");
mysqli_query($con, "SET FOREIGN_KEY_CHECKS = 0");

// Fetch user type
$user_type_data_get = mysqli_query($con, "SELECT buyer_type FROM tbl_user_master WHERE user_id=$session_user_code");
$user_type_data = mysqli_fetch_row($user_type_data_get);
$user_type = $user_type_data[0];

// Get buyer id
$buyer_id = $session_user_code;

// Get the transferred items by the logged-in user
$query = "SELECT 
            dt.product_id, dt.transferred_quantity, dt.transferred_price, dt.entry_timestamp, 
            dt.transferred_to_id, p.product_name, c.category_name, u.name AS transferred_to_name,
            dt.transferred_products_unique_id
          FROM tbl_direct_transfer dt 
          JOIN tbl_product_master p ON dt.product_id = p.product_id 
          JOIN tbl_category_master c ON dt.category_id = c.category_id 
          JOIN tbl_user_master u ON dt.transferred_to_id = u.user_id 
          WHERE dt.transferred_by_id = $buyer_id";

$result = mysqli_query($con, $query);

// Prepare data for counting products by unique ID
$product_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $product_data[] = $row;
}

// Get the count of products for each unique ID
$unique_product_count = [];
foreach ($product_data as $item) {
    $unique_product_count[$item['transferred_products_unique_id']][] = $item;
}
?>

<div id="page-content">
    <div class="container">
        <!-- Toolbar -->
        <div class="toolbar toolbar-wrapper shop-toolbar mt-2 credit-addon">
            <div class="row align-items-center">
                <div class="col-12 text-left filters-toolbar-item d-flex justify-content-start">
                    <div class="filters-item d-flex align-items-center">
                        <div class="grid-options view-mode d-flex flex-wrap justify-content-between">
                            <a class="icon-mode credit-history d-block" href="<?php echo $baseUrl . '/direct_transfer'; ?>" data-col="1">
                                <img src="frontend_assets/img-icon/purchased_products.png" height="15px" width="15px">
                                <span class="purchased-products">Purchased Items</span>
                            </a>
                            <a class="icon-mode credit-add-on grid-2 d-block active" href="<?php echo $baseUrl . '/transferred_products'; ?>" data-col="2">
                                <img src="frontend_assets/img-icon/transferred_products.png" height="15px" width="15px">
                                <span class="transferred-products">Transferred Items</span>
                            </a>
                            <a class="icon-mode credit-history d-block" href="<?php echo $baseUrl . '/transferred_items_for_you'; ?>" data-col="3">
                                <img src="frontend_assets/img-icon/transfer_product_for_you.png" height="15px" width="15px">
                                <span class="transferred-products">Transferred Items For You</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="row">
        <div class="col-12">
            <div class="product-listview-loadmore" id="product-listview-loadmore">
                <div class="grid-products grid-view-items mt-5">
                    <div class="row row-cols-3 row-cols-md-3 p-3" id="product_list">
                        <?php
                        if (!empty($product_data)) {
                            foreach ($product_data as $row) {
                                $product_name = $row['product_name'];
                                $category_name = $row['category_name'];
                                $quantity = $row['transferred_quantity'];
                                $price = $row['transferred_price'];
                                $transfer_date = date("d.m.Y", strtotime($row['entry_timestamp']));
                                $transferred_to_name = $row['transferred_to_name'];
                                $unique_code = $row['transferred_products_unique_id'];

                                // Get the count of products for the same unique ID
                                $product_count = count($unique_product_count[$unique_code]);

                                // Modal ID for unique product
                                $modal_id = 'productModal' . $unique_code;
                                ?>

                                <div class="col">
                                    <div class="product-item" style="background-image: url('https://via.placeholder.com/150');" data-bs-toggle="modal" data-bs-target="#<?php echo $modal_id; ?>">
                                        <div class="product-count"><?php echo $product_count; ?>+</div>
                                    </div>
                                </div>

                                <!-- Modal -->
                                <div class="modal fade" id="<?php echo $modal_id; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $unique_code; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header text-white" style="background-color: #2f415d;">
                                                <h3 class="modal-title" id="productModalLabel<?php echo $unique_code; ?>"><?php echo $product_name; ?></h3>
                                                <span>Transfer Date: <?php echo $transfer_date; ?></span>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card shadow">
                                                    <div class="d-flex align-items-center justify-content-between p-4">
                                                        <div class="p-1">
                                                            <img src="https://via.placeholder.com/150" alt="Product Image" class="rounded shadow" style="width: 100px; object-fit: cover;">
                                                        </div>
                                                        <div class="p-1">
                                                            <h3><?php echo $product_name; ?></h3>
                                                            <p class="text-muted"><?php echo $category_name; ?></p>
                                                            <p><strong>Quantity:</strong> <?php echo $quantity; ?></p>
                                                            <p><strong>Transfer by:</strong> You</p>
                                                            <p><strong>Transfer to:</strong> <?php echo $transferred_to_name; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <?php
                                                    // Display all products with the same unique ID
                                                    foreach ($unique_product_count[$unique_code] as $modal_row) {
                                                        $modal_product_name = $modal_row['product_name'];
                                                        $modal_category_name = $modal_row['category_name'];
                                                        $modal_quantity = $modal_row['transferred_quantity'];
                                                        $modal_price = $modal_row['transferred_price'];
                                                        $modal_transferred_to_name = $modal_row['transferred_to_name'];
                                                        ?>
                                                        <div class="col-12">
                                                            <p><strong>Product Name:</strong> <?php echo $modal_product_name; ?></p>
                                                            <p><strong>Category:</strong> <?php echo $modal_category_name; ?></p>
                                                            <p><strong>Quantity:</strong> <?php echo $modal_quantity; ?></p>
                                                            <p><strong>Transferred to:</strong> <?php echo $modal_transferred_to_name; ?></p>
                                                            <p><strong>Price:</strong> ₹<?php echo $modal_price; ?></p>
                                                            <hr>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-between border border-2">
                                                <div>
                                                    <span class="text-muted">Total Quantity: <?php echo $quantity; ?></span><br>
                                                    <span class="fw-bold">Transferred Price: ₹<?php echo $price; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        } else {
                            echo '<p class="text-center">No transferred items found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
