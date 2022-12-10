<header>
    <nav class="topbar">
        <h1><a href="<?php echo $url;?>" title="<?php echo $site_name?>"><?php echo $site_name?></a></h1>

        <ul>
            <li class="d-mob"><button aria-labelledby="menu" id="menu"><span>Menu</span><i class="fas fa-bars"></i></button></li>
            <li><a href="<?php echo $url;?>logout/">Logout</a></li>
        </ul>
    </nav>

    <nav class="sidebar col-12 col-sm-10 col-md-3 col-lg-2">
        <header class="user-tag"><h2><?php echo $_SESSION['user_first_name'];?></h2><a href="<?php echo $url;?>settings/"><i class="fas fa-cog"></i></a></header>
        <button class="d-mob" aria-labelledby="close" id="close"><span>Close</span><i class="fas fa-times"></i></button>
        <ul>
            <li><a href="<?php echo $url;?>">Dashboard</a></li>
            <?php
                if( $_SESSION['user_role_id'] == 1 )
                {
            ?>
            <li><a href="<?php echo $url;?>issues/">Issues</a><span></span></li>
            <li><a href="<?php echo $url;?>manage-users/">Manage Users</a></li>
            <li><a href="<?php echo $url;?>locales/">Locations</a></li>
            <li><a href="<?php echo $url;?>banners/">Banners</a></li>
            <li><a href="<?php echo $url;?>departments/">Departments</a></li>
            <li><a href="<?php echo $url;?>categories/">Categories</a></li>
            <li><a href="<?php echo $url;?>sub-categories/">Sub Categories</a></li>
            <?php
                }
                if( $_SESSION['user_role_id'] != 3 )
                {
            ?>
            <li><a href="<?php echo $url;?>products/">Products</a></li>
            <li><a href="<?php echo $url;?>images/">Images</a></li>
            <?php
                }
                if( $_SESSION['user_role_id'] == 1 )
                {
            ?>
            <li><a href="<?php echo $url;?>backups/">Backup</a></li>
            <?php
                }
            ?>
        </ul>
    </nav>
</header>