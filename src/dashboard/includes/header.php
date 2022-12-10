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
    <meta name="og:image" property="og:image" content="<?php echo $parent_url;?>img/logo.png">
    <meta name="og:title" property="og:title" content="<?php echo $title;?>" />
    <meta name="description" property="og:description" content="<?php echo $desc;?>" />
    
    <title><?php echo $title;?></title>

    <link rel="apple-touch-icon" href="<?php echo $parent_url;?>img/favicon.ico" />
    <link rel="icon" href="<?php echo $parent_url;?>img/favicon.ico">
    <link rel="manifest" href="<?php echo $parent_url;?>manifest.json">

    <link rel="stylesheet" href="<?php echo $url;?>assets/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="<?php echo $url;?>assets/css/nice-select.min.css">
    <link rel="stylesheet" href="<?php echo $url;?>assets/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?php echo $url;?>assets/css/style.min.css">
    <?php
        if( $page == 'products' )
        {
            echo '<link rel="stylesheet" href="'.$url.'assets/css/select2.min.css" />';
        }
    ?>
</head>