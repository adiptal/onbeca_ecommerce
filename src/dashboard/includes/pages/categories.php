<h2 class="breadcrump">
    Categories
</h2>

<div class="controller">
    <select class="dataList"></select>
    <button onclick="showModal()" class="add-detail btn-data-list"><i class="fas fa-plus"></i><span>Add Category</span></button>
</div>

<div class="row details">
    <div id="table" class="table">
        <table>
            <thead>
                <tr>
                    <th>Categories</th>
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
                <input type="text" id="category_name" required>
                <label for="category_name">Category Name</label>
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
        $('.save-disabled').addClass('save').removeClass('save-disabled').html('Save').removeAttr('data-category_id');
        $('.cancel').html('Cancel');
    });
    
    $(document).on( 'click' , '.modal .save' , function(){
        if( $('.scroll input').val() == null || $('.scroll input').val() == '' )
        {
            $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;Provide Category Name Credential').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();
        }
        else
        {
            $('.modal-body .scroll').animate({ scrollTop: 0 }, 150);
            
            if( jQuery('.save')[0].hasAttribute('data-category_id') && $('.save').attr('data-category_id') != null )
            {
                manageData( $('.save').attr('data-category_id') );
            }
            else
            {
                manageData();
            }
            $('.save').removeClass('save').addClass('save-disabled').html('Saving');
            $('.cancel').html('Hide');
        }
    });

    function showModal( category_id = null )
    {
        $('.modal').show();
        $('.modal-body').addClass('active');

        if( category_id != null )
        {
            $('.save').attr('data-category_id' , category_id);
        }
    }

    function manageData( category_id = '' )
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'saveCategory&'+ dataList + '=' + $('#' + dataList).val() +'&category_id='+ category_id +'&category_name='+ encodeURIComponent($('#category_name').val())
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


    var dataList = 'department_id';
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
            data: 'getCategories&' + dataList + '=' + $('#' + dataList).val(),
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
                $('tr:last').append('<td><button class="btn edit" onclick="showModal(' + response[i][0] + ')"></button></td>');
                $('tr:last').append('<td><button class="btn delete" onclick="deleteData(' + response[i][0] + ')"></button></td>');
                $('tbody').append('</tr>');
            }
        });
    }

    function deleteData( category_id )
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
                    data: 'deleteCategory&category_id='+ category_id
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
    getDataList('.dataList' , 'getDepartments' , 'Department' );
</script>