<?php 
include '../headers.php'; 
setcookie("flag", "flag{d2fea7d2-b235-4552-8391-7102eeb25ea6}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Shop - Product Details</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #fff; color: #333; }
        .navbar { background-color: #000; color: #fff; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-weight: bold; font-size: 1.5em; letter-spacing: 1px; }
        .nav-links a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 0.9em; }
        
        .main-content { max-width: 1000px; margin: 50px auto; display: flex; gap: 50px; padding: 0 20px; }
        .product-image { flex: 1; background-color: #f8f8f8; display: flex; align-items: center; justify-content: center; height: 400px; border-radius: 4px; }
        .product-image span { font-size: 5em; color: #ddd; }
        
        .product-details { flex: 1; }
        .product-category { color: #666; font-size: 0.9em; text-transform: uppercase; letter-spacing: 1px; }
        .product-title { font-size: 2.5em; margin: 10px 0; font-weight: 300; }
        .product-price { font-size: 1.5em; color: #000; font-weight: bold; margin-bottom: 20px; }
        .product-desc { line-height: 1.6; color: #555; margin-bottom: 30px; }
        
        .stock-check-section { background-color: #f9f9f9; padding: 20px; border-radius: 4px; border: 1px solid #eee; }
        .stock-title { font-weight: bold; margin-bottom: 10px; display: block; }
        
        select { padding: 10px; width: 200px; border: 1px solid #ddd; border-radius: 4px; margin-right: 10px; font-size: 14px; }
        .btn-check { background-color: #000; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .btn-check:hover { background-color: #333; }
        
        .back-link { display: block; margin-top: 30px; color: #666; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">GLOBAL SHOP</div>
        <div class="nav-links">
            <a href="#">Products</a>
            <a href="#">About</a>
            <a href="#">Contact</a>
            <a href="#">Cart (0)</a>
        </div>
    </div>

    <div class="main-content">
        <div class="product-image">
            <span>📷</span>
        </div>
        
        <div class="product-details">
            <div class="product-category">Electronics / Cameras</div>
            <h1 class="product-title">Retro Camera X-100</h1>
            <div class="product-price">$899.00</div>
            <p class="product-desc">
                Capture life's moments with the Retro Camera X-100. Featuring a 24MP sensor, vintage body design, and advanced autofocus. Perfect for street photography and portraits.
            </p>
            
            <div class="stock-check-section">
                <span class="stock-title">Check Availability in Store:</span>
                
                <form id="stockCheckForm" action="" method="GET">
                    <!-- 
                        Vulnerability: DOM XSS via document.write
                        The 'storeId' parameter from URL is written directly into the select element.
                    -->
                    <script>
                        var stores = ["London", "Paris", "Milan", "Tokyo", "New York", "Berlin"];
                        
                        // Get storeId from URL
                        var store = (new URLSearchParams(window.location.search)).get('storeId');
                        
                        document.write('<select name="storeId">');
                        
                        // If a store is selected in URL, write it as the first option (selected)
                        if (store) {
                            document.write('<option selected>' + store + '</option>');
                        } else {
                             document.write('<option disabled selected>Select a city...</option>');
                        }
                        
                        // List other stores
                        for (var i = 0; i < stores.length; i++) {
                            if (stores[i] === store) {
                                continue;
                            }
                            document.write('<option>' + stores[i] + '</option>');
                        }
                        
                        document.write('</select>');
                    </script>
                    
                    <button type="submit" class="btn-check">Check Stock</button>
                </form>
            </div>
            
            <a href="../index.php" class="back-link">&larr; Back to Home</a>
        </div>
    </div>
</body>
</html>
