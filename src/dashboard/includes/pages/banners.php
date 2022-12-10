<h2 class="breadcrump">
    Banners
</h2>

<div class="controller">
    <select class="dataList"></select>
    <button onclick="showModal()" class="add-detail btn-data-list"><i class="fas fa-plus"></i><span>Add Banner</span></button>
</div>

<div class="row details">
    <div id="table" class="table">
        <table>
            <thead>
                <tr>
                    <th>Page URL</th>
                    <th>Image URL</th>
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
                <input type="text" id="banner_page_url" required>
                <label for="banner_page_url">Banner Page URL</label>
            </div>
            
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="banner_image_url" required>
                <label for="banner_image_url">Banner Image URL</label>
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
    $(document).on( 'click' , '.modal .cancel' , function(){
        $('.modal-body').removeClass('active').addClass('deactive');
        setTimeout(function(){
            $('.modal-body').removeClass('deactive');
            $('.modal').hide();
        } , 150);
        $('.modal input').val('');
        $('.save-disabled').addClass('save').removeClass('save-disabled').html('Save').removeAttr('data-banner_id');
        $('.cancel').html('Cancel');
    });
    
    $(document).on( 'click' , '.modal .save' , function(){
        if( $('.scroll input').val() == null || $('.scroll input').val() == '' )
        {
            $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;Provide Banner Credential').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();
        }
        else
        {
            $('.modal-body .scroll').animate({ scrollTop: 0 }, 150);
            
            if( jQuery('.save')[0].hasAttribute('data-banner_id') && $('.save').attr('data-banner_id') != null )
            {
                manageData( $('.save').attr('data-banner_id') );
            }
            else
            {
                manageData();
            }
            $('.save').removeClass('save').addClass('save-disabled').html('Saving');
            $('.cancel').html('Hide');
        }
    });

    function showModal( banner_id = null )
    {
        $('.modal').show();
        $('.modal-body').addClass('active');

        if( banner_id != null )
        {
            $('.save').attr('data-banner_id' , banner_id);
            $.ajax({
                method: "POST",
                url: <?php echo '"' . $url . '"';?> + 'functions.php',
                data: 'getBanner&banner_id=' + banner_id ,
                dataType: "json"
            }).done(function(response) {
                $('#banner_page_url').val(response[0]);
                $('#banner_image_url').val(response[1]);
            });
        }
    }

    function manageData( banner_id = '' )
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'saveBanner&'+ dataList + '=' + $('#' + dataList).val() +'&banner_id='+ banner_id +'&banner_page_url='+ encodeURIComponent($('#banner_page_url').val()) +'&banner_image_url='+ encodeURIComponent($('#banner_image_url').val())
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

    // -------------------- MODAL ENDS --------------------


    var dataList = 'locale_id';
    $('.dataList').attr('id' , dataList);
    
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
            data: 'getBanners&' + dataList + '=' + $('#' + dataList).val(),
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
                $('tr:last').append('<td><button class="btn edit" onclick="showModal(' + response[i][0] + ')"></button></td>');
                $('tr:last').append('<td><button class="btn delete" onclick="deleteData(' + response[i][0] + ')"></button></td>');
                $('tbody').append('</tr>');
            }
        });
    }

    function deleteData( banner_id )
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
                    data: 'deleteBanner&banner_id='+ banner_id
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
    getDataList('.dataList' , 'getLocations' , 'Location' );
</script>