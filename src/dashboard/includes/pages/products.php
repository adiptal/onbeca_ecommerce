<h2 class="breadcrump">
    Products
</h2>

<div class="controller">
    <select class="dataList"></select>
    <select class="dataList2"></select>
    <select class="dataList3"></select>
    <button onclick="showModal()" class="add-detail btn-data-list"><i class="fas fa-plus"></i><span>Add Product</span></button>
</div>

<div class="form-input col-md-4 search">
    <input type="text" id="search" required>
    <label for="search">Search</label>
</div>

<div class="row details">
    <div id="table" class="table product-table">
        <table>
            <thead>
                <tr>
                    <th>Products</th>
                    <th>Article</th>
                    <th>Affiliate</th>
                    <th>Edit</th>
                    <!-- ONLY FOR ADMIN -->
                    <?php if( $_SESSION['user_role_id'] == 1 ){ echo '<th>Delete</th><th>Action</th>'; }else{ echo '<th>Status</th>'; }?>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<div class="pagination" id="pagination"></div>

<div class="modal">
    <div class="modal-body">
        <div class="scroll">
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="product_name" required>
                <label for="product_name">Product Name</label>
            </div>
            
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="product_image_url" required>
                <label for="product_image_url">Product Image Url</label>
            </div>

            <div class="col-md-10 offset-md-1">
                <p class="col-12">Filter</p>
                <div class="row filter_json"></div>
            </div>

            <div class="col-md-10 offset-md-1">
                <p class="col-12">Location</p>
                <div class="row locale-data"></div>
            </div>
        </div>
        
        <div class="toolbar">
            <p></p>
            <button class="cancel">Cancel</button>
            <button class="save">Save</button>
        </div>
    </div>
</div>

<script>
    
    function getLocation()
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getLocations',
            dataType: "json"
        }).done(function(response) {
            $('.locale-data').html('');
            for( var i=0 ; i < response.length ; i++ )
            {
                $('.locale-data').append(
                    '<div class="form-input chk">'+
                    '<label class="checkbox" for="'+ response[i][0] +'">'+
                    '<input type="checkbox" class="locale_id" value="'+ response[i][0] +'" id="'+ response[i][0] +'"> '+ response[i][1] +''+
                    '</label>'+
                    '</div>'
                );
            }
        });
    }

    $(document).on( 'change' , '.locale_id' , function(){
        if( $(this).prop('checked') )
        {
            $(this).closest('label').addClass('active');
        }
        else
        {
            $(this).closest('label').removeClass('active');
        }
    });
    
    $(document).on( 'click' , '.modal .cancel' , function(){
        $('.modal-body').removeClass('active').addClass('deactive');
        setTimeout(function(){
            $('.modal-body').removeClass('deactive');
            $('.modal').hide();
        } , 150);
        $('.modal input , .modal textarea').val('');
        $('.filter_json select').val('').trigger('change');
        $('.save-disabled').addClass('save').removeClass('save-disabled').html('Save').removeAttr('data-product_id');
        $('.cancel').html('Cancel');
        getLocation();
    });
    
    $(document).on( 'click' , '.modal .save' , function(){
        if( $('#product_name').val() == null || $('#product_name').val() == '' )
        {
            $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;Provide Product Name Credential').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();
        }
        else
        {
            $('.modal-body .scroll').animate({ scrollTop: 0 }, 150);
            
            if( jQuery('.save')[0].hasAttribute('data-product_id') && $('.save').attr('data-product_id') != null )
            {
                manageData( $('.save').attr('data-product_id') );
            }
            else
            {
                manageData();
            }
            $('.save').removeClass('save').addClass('save-disabled').html('Saving');
            $('.cancel').html('Hide');
        }
    });

    function generateFilter()
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getSubCategory&sub_category_id=' + $('#' + dataList).val() ,
            dataType: "json"
        }).done(function(response) {
            var json = JSON.parse(response[0][2])[1];
            var keys = Object.keys(json);
            $('.filter_json').html('');
            for(var i=0 ; i<keys.length ; i++)
            {
                $('.filter_json').append('<select id="filter-'+ keys[i] +'" multiple="multiple" required></select>');
                $('#filter-'+ keys[i]).append('<option value="" selected disabled>'+ keys[i].replace(/[-]/gi, ' ') +'</option>');
                var options = json[keys[i]];
                for(var j=0 ; j<options.length ; j++)
                {
                    $('#filter-'+ keys[i]).append('<option value="'+ j +'">'+ options[j].replace(/[-]/gi, ' ') +'</option>');
                }
            }
            $('.filter_json select').select2();
        });
    }
    
    function showModal( product_id = null )
    {
        $('.modal').show();
        $('.modal-body').addClass('active');

        if( product_id != null )
        {
            $('.save').attr('data-product_id' , product_id);
            $.ajax({
                method: "POST",
                url: <?php echo '"' . $url . '"';?> + 'functions.php',
                data: 'getProductInfo&product_id=' + product_id ,
                dataType: "json"
            }).done(function(response) {
                $('#product_name').val(response[0][1]);
                $('#product_image_url').val(response[0][2]);
                
                if( response[0][3] != null && response[0][3] != '' )
                {
                    var filter = JSON.parse(response[0][3]);
                    var keys = Object.keys(filter);
                    for( var i=0 ; i<keys.length ; i++ )
                    {
                        $('#filter-'+keys[i]).val(filter[keys[i]]);
                    }
                    $('.filter_json select').trigger('change');
                }
                
                for( var i=0 ; i<response[1].length ; i++ )
                {
                    $('.locale_id#'+response[1][i][0]).click();
                }
            });
        }
    }

    function manageData( product_id = '' )
    {
        var locale_id = [];
        $('.locale_id:checked').each(function(){
            locale_id.push($(this).val());
        });

        var filter_element = $('.filter_json select');
        var filter_json = {};
        for(var i=0 ; i<filter_element.length ; i++)
        {
            filter_json[$(filter_element[i]).children(':first').html().replace(/[ ]/gi, '-')] = $(filter_element[i]).val();
        }
        
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'saveProduct&' + dataList + '=' + $('#' + dataList).val() + '&product_name=' + encodeURIComponent($('#product_name').val()) + '&product_image_url=' + $('#product_image_url').val() + '&locale_id=' + locale_id + '&filter_json=' + encodeURIComponent(JSON.stringify(filter_json)) + '&product_id=' + product_id
        }).done(function(response) {
            Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 3000
            }).fire({
                type: response[0],
                title: response[1]
            })
            $('.cancel').click();
            getData();
        });
    }
    getLocation();

    // -------------------- MODAL ENDS --------------------


    var dataList = 'sub_category_id';
    $('.dataList2').html('<option selected disabled>Category</option>');
    $('.dataList3').attr('id' , dataList).html('<option selected disabled>Sub Category</option>');
    
    $.getScript( <?php echo '"' . $url . '"';?> + "assets/js/jquery.nice-select.min.js" , function(){
        $('select').niceSelect();
    });
    
    $.getScript( <?php echo '"' . $url . '"';?> + "assets/js/select2.min.js" , function(){
        $('.filter_json select').select2();
    });
    
    function getDataList(element , param , label)
    {
        let deferred = $.Deferred();

        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: param,
            dataType: "json"
        }).done(function(response) {
            $(element).html('<option value="" selected disabled>'+ label +'</option>');
            for( var i=0 ; i < response.length ; i++ )
            {
                $(element).append('<option value="'+ response[i][0] +'">'+ response[i][1] +'</option>');
            }
            $(element).niceSelect('update');

            deferred.resolve();
        });

        updateURLInputs();
        return deferred.promise();
    }
    
    $(document).on( 'change' , '.dataList , .dataList2' , function(){
        $('.btn-data-list').hide();
    });

    $(document).on( 'change' , '.dataList' , function( event , callback ){
        $('tbody').html('');
        getDataList('.dataList2' , 'getCategories&department_id='+$('.dataList').val() , 'Category').then(function(){
            if(callback) callback(); 
        });
    });

    $(document).on( 'change' , '.dataList2' , function( event , callback ){
        $('tbody').html('');
        getDataList('.dataList3' , 'getSubCategories&category_id='+$('.dataList2').val() , 'Sub Category').then(function(){
            if(callback) callback(); 
        });
    });

    $(document).on( 'change' , '#' + dataList , function( event , callback ){
        if( $(this).val() != '' && $(this).val() != null )
        {
            $('.btn-data-list , .search').show();
            getData().then(function(){
                if(callback) callback(); 
            });
        }
    });
    
    $("#search").on('change keyup paste', function () {
        getData();
    });

    function getData( offset = (params[1]-1)*10 )
    {
        let deferred = $.Deferred();

        updateURLInputs( offset + 1 );
        generateFilter();
        var limit = 10;
        $('.loader').remove();
        $('.details').append('<div class="loader"></div>');
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getProducts&' + dataList + '=' + $('#' + dataList).val() + '&limit=' + limit + '&offset=' + offset + '&search=' + $('#search').val(),
            dataType: "json"
        }).done(function(response) {
            setTimeout(function(){
                $('.loader').remove();
            } , 150);
            $('tbody').html('');
            for( var i=2 ; i < response.length ; i++ )
            {
                $('tbody').append('<tr>');
                $('tr:last').append('<td>' + response[i][1] + '</td>');
                
                if( response[i][3] == 1 )
                {
                    $('tr:last').append('<td><button class="btn btn-redirect" onclick="redirect( '+ "'" + "articles" + "'" + ' , ' + response[i][0] + ')">Article</button></td>');
                    $('tr:last').append('<td><button class="btn btn-redirect" onclick="redirect( '+ "'" + "affiliates" + "'" + ' , ' + response[i][0] + ')">Affiliate</button></td>');
                    $('tr:last').append('<td><button class="btn edit" onclick="showModal(' + response[i][0] + ')"></button></td>');
                }
                else
                {
                    $('tr:last').append('<td>&mdash;</td>');
                    $('tr:last').append('<td>&mdash;</td>');
                    $('tr:last').append('<td>&mdash;</td>');
                }

                <?php if( $_SESSION['user_role_id'] == 1 ){ ?>
                // ONLY FOR ADMIN
                $('tr:last').append('<td><button class="btn delete" onclick="deleteData(' + response[i][0] + ')"></button></td>');
                if( response[i][2] == 0 )
                {
                    $('tr:last').append('<td><button class="btn btn-redirect" onclick="publishData(' + response[i][0] + ' , 1)">Approve</button></td>');
                }
                else
                {
                    $('tr:last').append('<td><button class="btn btn-redirect" onclick="publishData(' + response[i][0] + ' , 0)">Unapprove</button></td>');
                }
                <?php }
                else{ ?>
                // FOR NON ADMIN
                if( response[i][2] == 1 )
                {
                    $('tr:last').append('<td>Approved</td>');
                }
                else
                {
                    $('tr:last').append('<td>Pending / Unapproved</td>');
                }
                <?php } ?>
                
                $('tbody').append('</tr>');
            }
            
            // PAGINATION
            $('#pagination').html('');
            if( response[1] != 0 )
            {
                // FIRST
                $('#pagination').append('<button onclick="getData(0)" class="btn"><<</button>');
                // PREVIOUS
                $('#pagination').append('<button onclick="getData('+((response[1])*limit-limit)+')" class="btn"><</button>');
            }
            for(var j=response[1]-4 ; j < response[1]+11 ; j++)
            {
                if( j > 0 && j < 11 && j < response[0] )
                {
                    $('#pagination').append('<button id="pagination-'+j+'" onclick="getData('+(j*limit-limit)+')" class="btn">'+ j +'</button>');
                }
                else if( j > 0 && j <= response[0] && ( j < response[1]-3 || j < response[1]+6 ) )
                {
                    $('#pagination').append('<button id="pagination-'+j+'" onclick="getData('+(j*limit-limit)+')" class="btn">'+ j +'</button>');
                }
            }
            if( response[1]+1 < response[0] )
            {
                // NEXT PAGE
                $('#pagination').append('<button onclick="getData('+((response[1]+2)*limit-limit)+')" class="btn">></button>');
                $('#page-id-' + (response[1]+1) ).addClass('active');
                // LAST PAGE
                $('#pagination').append('<button onclick="getData('+(response[0]*limit-limit)+')" class="btn">>></button>');
            }
            $('#pagination-' + (response[1]+1) ).addClass('active');

            deferred.resolve();
        });
        return deferred.promise();
    }

    <?php if( $_SESSION['user_role_id'] == 1 ){ ?>
    // ONLY FOR ADMIN
    function deleteData( product_id )
    {
        Swal({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D84664',
            cancelButtonColor: '#2D7DD2',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    method: "POST",
                    url: <?php echo '"' . $url . '"';?> + 'functions.php',
                    data: 'deleteProduct&product_id='+ product_id
                }).done(function(response) {
                    Swal.mixin({
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 3000
                    }).fire({
                        type: response[0],
                        title: response[1]
                    });
                    getData();
                });
            }
        })
    }

    function publishData( product_id , product_publish_status )
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'publishProduct&product_id='+ product_id + '&product_publish_status='+ product_publish_status
        }).done(function(response) {
            Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 3000
            }).fire({
                type: response[0],
                title: response[1]
            });
            getData();
        });
    }
    <?php } ?>


    // ADVANCE SETUP
    function redirect( page , product_id )
    {
        window.open(<?php echo '"' . $url . '"';?> + page + "/" + product_id + "/" , '_blank');
    }

    function loadAllData()
    {
        getDataList('.dataList' , 'getDepartments' , 'Department').then(function(){
            if( params.hasOwnProperty(2) && params[2] != '' )
            {
                fetchURLJSON = JSON.parse(decodeURIComponent(params[2]));
                $('.dataList').val(fetchURLJSON[0]).trigger('change' , function(){
                    $('.dataList2').val(fetchURLJSON[1]).trigger('change' , function(){
                        $('.dataList3').val(fetchURLJSON[2]).trigger('change' , function(){
                            fetchURLJSON = '';
                        }).niceSelect('update');
                    }).niceSelect('update');
                }).niceSelect('update');
            }
        });
    }
    refreshURL();
    loadAllData();
    
    window.onpopstate = function(event) {
        fetchURLJSON = 'Loading';
        refreshURL();
        loadAllData();
    };
    
    var fetchURLJSON = '';
    var inputToUpdateToURL = [];

    function updateURLInputs( page = 1 )
    {
        if( fetchURLJSON == '' )
        {
            inputToUpdateToURL = [ $('.dataList').val() , $('.dataList2').val() , $('.dataList3').val() ];
            history.pushState( null , null , '<?php echo $url;?>products/'+ page +'/' + encodeURIComponent(JSON.stringify(inputToUpdateToURL)) + '/' );
            refreshURL();
        }
    }
</script>