<h2 class="breadcrump">
    Affiliates
</h2>

<div class="controller">
    <select class="dataList"></select>
    <button onclick="showModal()" class="add-detail btn-data-list"><i class="fas fa-plus"></i><span>Add Affiliate</span></button>
</div>

<div class="row details">
    <div id="table" class="table affiliate-table">
        <table>
            <thead>
                <tr>
                    <th>Affiliates</th>
                    <th>Condition</th>
                    <th>Price</th>
                    <th>URL</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal">
    <div class="modal-body">
        <div class="scroll">
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="affiliate_company" required>
                <label for="affiliate_company">Affiliate Company</label>
            </div>
            
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="product_condition" required>
                <label for="product_condition">Product Condition</label>
            </div>
            
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="affiliate_pricing" required>
                <label for="affiliate_pricing">Price</label>
            </div>
            
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="affiliate_url" required>
                <label for="affiliate_url">Affiliate URL</label>
            </div>
            
            <div class="col-md-10 offset-md-1">
                <p class="col-12">Automated Affiliate Tracking</p>
                <div class="row locale-data">
                    <div class="form-input chk">
                        <label class="checkbox active"><input type="radio" name="manageAffiliateTracking" value="on" checked> ON</label>
                    </div>
                    
                    <div class="form-input chk">
                        <label class="checkbox"><input type="radio" name="manageAffiliateTracking" value="off"> OFF</label>
                    </div>
                </div>
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
    $(document).on( 'change' , '[name=manageAffiliateTracking]' , function(){
        $('.checkbox').removeClass('active');
        $('[name=manageAffiliateTracking]:checked').closest('label').addClass('active');
    });

    $(document).on( 'click' , '.modal .cancel' , function(){
        $('.modal-body').removeClass('active').addClass('deactive');
        setTimeout(function(){
            $('.modal-body').removeClass('deactive');
            $('.modal').hide();
        } , 150);
        $('.modal input:not(:radio)').val('');
        $('[value=on]').prop( 'checked', true ).trigger('change');
        $('.save-disabled').addClass('save').removeClass('save-disabled').html('Save').removeAttr('data-affiliate_id');
        $('.cancel').html('Cancel');
    });
    
    $(document).on( 'click' , '.modal .save' , function(){
        if( $('.scroll input').val() == null || $('.scroll input').val() == '' )
        {
            $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;Provide Affiliate Company Credential').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();
        }
        else
        {
            $('.modal-body .scroll').animate({ scrollTop: 0 }, 150);
            
            if( jQuery('.save')[0].hasAttribute('data-affiliate_id') && $('.save').attr('data-affiliate_id') != null )
            {
                manageData( $('.save').attr('data-affiliate_id') );
            }
            else
            {
                manageData();
            }
            $('.save').removeClass('save').addClass('save-disabled').html('Saving');
            $('.cancel').html('Hide');
        }
    });
    
    function showModal( affiliate_id = null )
    {
        $('.modal').show();
        $('.modal-body').addClass('active');

        if( affiliate_id != null )
        {
            $('.save').attr('data-affiliate_id' , affiliate_id);
            $.ajax({
                method: "POST",
                url: <?php echo '"' . $url . '"';?> + 'functions.php',
                data: 'getAffiliateInfo&affiliate_id=' + affiliate_id ,
                dataType: "json"
            }).done(function(response) {
                $('#affiliate_company').val(response[0][1]);
                $('#product_condition').val(response[0][2]);
                $('#affiliate_pricing').val(response[0][3]);
                $('#affiliate_url').val(response[0][4]);
                if( response[0][5] == 0 )
                {
                    $('[value=off]').prop( 'checked', true ).trigger('change');
                }
            });
        }
    }

    function manageData( affiliate_id = '' )
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'saveAffiliate&product_id=<?php echo $product_id;?>&' + dataList + '=' + $('#' + dataList).val() + '&affiliate_company='+ encodeURIComponent($('#affiliate_company').val()) + '&affiliate_url='+ encodeURIComponent($('#affiliate_url').val()) + '&affiliate_pricing='+ $('#affiliate_pricing').val() + '&product_condition='+ $('#product_condition').val() + '&manageAffiliateTracking=' + $('[name=manageAffiliateTracking]:checked').val() + '&affiliate_id=' + affiliate_id,
            timeout: 5000
        }).fail(function(response , error){
            $('table button').fadeOut();
            $('.cancel').click();
            Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 5000
            }).fire({
                type: 'warning',
                title: 'Might take few seconds !'
            }).then(result => {
                getData();
            })
        }).done(function(response) {
            Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 5000
            }).fire({
                type: response[0],
                title: response[1]
            })
            $('.cancel').click();
            getData();
        });
    }

    // -------------------- MODAL ENDS --------------------


    var dataList = 'locale_id';
    $('.dataList').attr('id' , dataList);

    $(function(){
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getProductInfo&product_id=<?php echo $product_id;?>',
            dataType: "json"
        }).done(function(response) {
            $('.breadcrump').append('&nbsp;&nbsp;&mdash;&nbsp;&nbsp;' + response[0][1]);
        });
    });
    
    $.getScript( <?php echo '"' . $url . '"';?> + "assets/js/jquery.nice-select.min.js" , function(){
        $('#' + dataList).niceSelect();
    });
    
    function getDataList(element , param , label)
    {
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
        });
    }

    $(document).on( 'change' , '#' + dataList , function(){
        $('.btn-data-list').show();
        getData();
    });

    function getData()
    {
        $('.loader').remove();
        $('.details').append('<div class="loader"></div>');
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getAffiliates&product_id=<?php echo $product_id;?>&' + dataList + '=' + $('#' + dataList).val(),
            dataType: "json"
        }).done(function(response) {
            setTimeout(function(){
                $('.loader').remove();
            } , 150);
            $('tbody').html('');
            for( var i=0 ; i < response.length ; i++ )
            {
                $('tbody').append('<tr>');
                $('tr:last').append('<td>' + response[i][1] + '</td>');
                $('tr:last').append('<td>' + response[i][2] + '</td>');
                $('tr:last').append('<td>' + response[i][3] + '</td>');
                $('tr:last').append('<td>' + response[i][4] + '</td>');
                $('tr:last').append('<td><button class="btn edit" onclick="showModal(' + response[i][0] + ')"></button></td>');
                $('tr:last').append('<td><button class="btn delete" onclick="deleteData(' + response[i][0] + ')"></button></td>');
                $('tbody').append('</tr>');
            }
        });
    }

    function deleteData( affiliate_id )
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
                    data: 'deleteAffiliate&affiliate_id='+ affiliate_id
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
                    getData();
                });
            }
        })
    }
    getDataList('.dataList' , 'getProductLocations&product_id=<?php echo $product_id;?>' , 'Location' );
</script>