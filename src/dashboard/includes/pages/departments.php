<h2 class="breadcrump">
    Departments
</h2>

<div class="controller">
    <button onclick="showModal()" class="add-detail"><i class="fas fa-plus"></i><span>Add Department</span></button>
</div>

<div class="row details">
    <div id="table" class="table">
        <table>
            <thead>
                <tr>
                    <th>Departments</th>
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
                <input type="text" id="department_name" required>
                <label for="department_name">Department Name</label>
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
        $('.save-disabled').addClass('save').removeClass('save-disabled').html('Save').removeAttr('data-department_id');
        $('.cancel').html('Cancel');
    });
    
    $(document).on( 'click' , '.modal .save' , function(){
        if( $('.scroll input').val() == null || $('.scroll input').val() == '' )
        {
            $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;Provide Department Name Credential').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();
        }
        else
        {
            $('.modal-body .scroll').animate({ scrollTop: 0 }, 150);
            
            if( jQuery('.save')[0].hasAttribute('data-department_id') && $('.save').attr('data-department_id') != null )
            {
                manageData( $('.save').attr('data-department_id') );
            }
            else
            {
                manageData();
            }
            $('.save').removeClass('save').addClass('save-disabled').html('Saving');
            $('.cancel').html('Hide');
        }
    });

    function showModal( department_id = null )
    {
        $('.modal').show();
        $('.modal-body').addClass('active');

        if( department_id != null )
        {
            $('.save').attr('data-department_id' , department_id);
        }
    }

    function manageData( department_id = '' )
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'saveDepartment&department_id='+ department_id +'&department_name='+ encodeURIComponent($('#department_name').val())
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


    function getData()
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getDepartments',
            dataType: "json"
        }).done(function(response) {
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

    function deleteData( department_id )
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
                    data: 'deleteDepartment&department_id='+ department_id
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
    getData();
</script>