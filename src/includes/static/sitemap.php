<?php
    require_once __DIR__ . '/../../dashboard/classes/Product.php';
    $Product = new Product();
    $allProduct = json_decode( $Product->getAllProducts() , true);

    header("Content-type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8" ?>
    <urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"> 
    <url>
      <loc>'. $url .'</loc>
      <lastmod>2019-04-06T19:15:47+00:00</lastmod>
      <priority>1.00</priority>
    </url>';

    foreach ($allProduct as $index => $products)
    {
      foreach ($products[2] as $key => $locale)
      {
        echo '
          <url>
            <loc>'. $url . $locale .'/'. $products[0] .'/</loc>
            <lastmod>'. $products[1] .'</lastmod>
            <priority>0.8</priority>
          </url>';
      }
    }
    echo '</urlset>';
?>