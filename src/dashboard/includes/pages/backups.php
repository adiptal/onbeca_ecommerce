<div class="breadcrump">
    Backup
</div>

<div class="row details">
    <div class="col-md-6">
        <div class="col-12 note">
            <h2><i class="fas fa-info-circle"></i> Note</h2>
            <p>System will help by generating Online Backup of Articles and Images</p>
            <button onclick="backupFiles()">Backup</button>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-12 note">
            <select id="fileList"></select>
            <button id="restore">Restore</button>
        </div>
    </div>

</div>

<script>
    $.getScript( <?php echo '"' . $url . '"';?> + "assets/js/jquery.nice-select.min.js" , function(){
        $('select').niceSelect();
    });

    function getFileList()
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getFileList'
        }).done(function(response) {
            $('#fileList').html('<option selected disabled>Backed Up Files</option>');
            for( var i=0 ; i<response.length ; i++ )
            {
                $('#fileList').append('<option>'+ response[i] +'</option>');
            }
            $('select').niceSelect('update');
        });
    }
    getFileList();

    function backupFiles()
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'backupFiles'
        }).done(function(response) {
            Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 3000
            }).fire({
                type: 'success',
                title: 'File Backup Created Successfully'
            })
            getFileList();
        });
    }

    $(document).on('click' , '#restore' , function(){
        if( $('#fileList').val() != '' )
        {
            $.ajax({
                method: "POST",
                url: <?php echo '"' . $url . '"';?> + 'functions.php',
                data: 'restoreFiles&fileName=' + $('#fileList').val()
            }).done(function(response) {
                Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 3000
                }).fire({
                    type: 'success',
                    title: 'File Restored Successfully'
                })
            });
        }
    });
</script>