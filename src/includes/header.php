<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FFFFFF" />
    <meta name="author" content="AGP">
    
    <meta name="robots" content="index,follow" />
    <meta name="googlebot" content="index,follow" />
    <meta name="copyright" content="<?php echo $site_name?>">
    <meta property="og:locale" content="en_US">
    <meta name="og:site_name" property="og:site_name" content="<?php echo $site_name?>"/>
    <meta name="og:type" property="og:type" content="website" />
    <link rel="canonical" href="<?php echo $current_url;?>" />
    <meta name="identifier-URL" content="<?php echo $current_url;?>">
    <meta name="url" property="og:url" content="<?php echo $current_url;?>" />
    <meta name="og:image" property="og:image" content="<?php echo $page_image;?>">
    <meta name="og:title" property="og:title" content="<?php echo $title;?>" />
    <meta name="description" property="og:description" content="<?php echo $desc;?>" />
    
    <meta name="og:email" content="admin@onbeca.com"/>
    <meta name="fb:page_id" content="2272069756400486" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@OnbecaOfficial" />
    <meta name="twitter:title" content="<?php echo $title;?>" />
    <meta name="twitter:description" content="<?php echo $desc;?>" />
    <meta name="twitter:image" content="<?php echo $page_image;?>" />

    <meta name="target" content="all"/>
    <meta name="audience" content="all"/>
    <meta name="coverage" content="Worldwide"/>
    <meta name="distribution" content="Global">
    <meta name="rating" content="safe for kids"/>
    
    <title><?php echo $title;?></title>

    <link rel="apple-touch-icon" href="<?php echo $url;?>img/favicon.ico" />
    <link rel="icon" href="<?php echo $url;?>img/favicon.ico">
    <link rel="manifest" href="<?php echo $url;?>manifest.json">

    <link rel="stylesheet" href="<?php echo $url;?>assets/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="<?php echo $url;?>assets/css/nice-select.min.css">
    <link rel="stylesheet" href="<?php echo $url;?>assets/css/loader.min.css">
    <link rel="stylesheet" href="<?php echo $url;?>assets/css/style.min.css">

    <?php
    echo '<script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name" : "Onbeca",
            "url": "'. $url .'",
            "image": "'. $url .'img/logo-512.png",
            "description": "'. $desc .'" 
        }
    </script>';
    ?>
    
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-137006531-1"></script>
    <script>
        // GOOGLE ANALYTICS
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
    </script>
</head>