<h2 class="breadcrump">
    Users
</h2>

<div class="controller">
    <button onclick="showModal()" class="add-detail"><i class="fas fa-plus"></i><span>Add User</span></button>
</div>

<div class="row details">
    <div id="table" class="table">
        <table>
            <thead>
                <tr>
                    <th>Users</th>
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
                <input type="text" id="user_first_name" required>
                <label for="user_first_name">User First Name</label>
            </div>
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="user_last_name" required>
                <label for="user_last_name">User Last Name</label>
            </div>
            <div class="form-input col-md-10 offset-md-1">
                <input type="text" id="user_email" required>
                <label for="user_email">User Email</label>
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
        $('.save-disabled').addClass('save').removeClass('save-disabled').html('Save').removeAttr('data-user_id');
        $('.cancel').html('Cancel');
    });
    
    $(document).on( 'click' , '.modal .save' , function(){
        if( $('.scroll input').val() == null || $('.scroll input').val() == '' )
        {
            $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;Provide Users Credential').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();
        }
        else
        {
            $('.modal-body .scroll').animate({ scrollTop: 0 }, 150);
            
            if( jQuery('.save')[0].hasAttribute('data-user_id') && $('.save').attr('data-user_id') != null )
            {
                manageData( $('.save').attr('data-user_id') );
            }
            else
            {
                manageData();
            }
            $('.save').removeClass('save').addClass('save-disabled').html('Saving');
            $('.cancel').html('Hide');
        }
    });

    function showModal( user_id = null )
    {
        $('.modal').show();
        $('.modal-body').addClass('active');

        if( user_id != null )
        {
            $('.save').attr('data-user_id' , user_id);
        }
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getUserInfo&user_id=' + user_id ,
            dataType: "json"
        }).done(function(response) {
            $('#user_email').val(response[0][0]);
            $('#user_first_name').val(response[0][1]);
            $('#user_last_name').val(response[0][2]);
        });
    }

    function manageData( user_id = '' )
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'saveUser&user_id='+ user_id +'&user_email='+ encodeURIComponent($('#user_email').val()) +'&user_first_name='+ encodeURIComponent($('#user_first_name').val()) +'&user_last_name='+ encodeURIComponent($('#user_last_name').val()),
            dataType: "json",
            timeout: 10000
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
            data: 'getUsers',
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

    function deleteData( user_id )
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
                    data: 'deleteUser&user_id='+ user_id
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