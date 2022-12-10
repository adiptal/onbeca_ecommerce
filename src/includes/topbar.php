<?php
    require_once __DIR__ . '/../dashboard/classes/Department.php';
    require_once __DIR__ . '/../dashboard/classes/Category.php';
    $Department = new Department();
    $Category = new Category();
    $departmentList = json_decode( $Department->getDepartments() , true );

    if( !function_exists( 'getEncodedURL' ) )
    {
        function getEncodedURL( $exploded_url )
        {
            $exploded_url = explode( '/' , $exploded_url );
            foreach ( $exploded_url as $key => $value )
            {
                $exploded_url[$key] = preg_replace("/[ ]/", '%20' , $value );
            }
            return implode( '/' , $exploded_url );
        }
    }
?>

<header>
    <!-- TOPBAR -->
    <nav class="topbar">
        <h1><a href="<?php echo $url.$locale_name.'/';?>" title="<?php echo $site_name;?>"><?php echo $site_name;?></a></h1>

        <div class="form-input row">
            <div class="col-md-5 col-lg-4">
                <select id="department">
                    <option value="All">All Department</option>
                    <?php
                        for( $i = 0 ; $i < sizeof( $departmentList ) ; $i++ )
                        {
                            echo '<option>'. $departmentList[$i][1] .'</option>';
                        }
                    ?>
                </select>
            </div>
            <div class="col-md-7 col-lg-8">
                <input id="search" type="text" placeholder="Search Products">
                <button id="search-btn">GO</button>
            </div>
        </div>

        <ul>
            <li class="search-form">
                <button><i class="fas fa-search"></i></button>
            </li>
            <?php if( isset( $_SESSION['user_id'] ) && !empty( $_SESSION['user_id'] ) ){ echo '<li><a class="link no-refresh-link" title="Logout" href="' . $url . 'dashboard/logout/"><i class="fas fa-sign-out-alt"></i></a></li>'; } ?>
            <li>
                <select id="locale_id"></select>
            </li>
        </ul>
    </nav>
    <!-- END TOPBAR -->

    <!-- NAVIGATION -->
    <div class="department">
        <ul>
            <?php
                for( $i = 0 ; $i < sizeof( $departmentList ) ; $i++ )
                {
                    echo '<li><ul>';

                    $categoryList = json_decode( $Category->getCategories( $departmentList[$i][0] ) , true );
                    for( $j = 0 ; $j < sizeof( $categoryList ) ; $j++ )
                    {
                        $encodedURL = $url . getEncodedURL( $locale_name . '/' . $departmentList[$i][1] . '/' . $categoryList[$j][1] . '/1/' );
                        echo '<li><a href="'. $encodedURL .'" title="'. $categoryList[$j][1] .'">'. $categoryList[$j][1] .'</a></li>';
                    }

                    echo '</ul><button>'. $departmentList[$i][1] .'</button></li>';
                }
            ?>
        </ul>
    </div>
    <!-- END NAVIGATION -->
</header>