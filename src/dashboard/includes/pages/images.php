<h2 class="breadcrump">
    Images
</h2>

<div class="controller">
    <input name="file" id="file" type="file">
    <button class="add-detail"><i class="fas fa-plus"></i><span>Add Image</span></button>
</div>

<div class="row details col-12"></div>

<script src="<?php echo $url;?>assets/js/jquery.lazy.min.js"></script>
<script>
    $(document).on('click', '.add-detail', function(){
        $('#file').click();
    });

    $(document).on('change', '#file', function(){
        var formData = new FormData();
        formData.append('uploadImage', '');
        formData.append('file', $('input[name=file]')[0].files[0]);
        $(this).val('');
        $.ajax({
            type: 'POST',
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: formData,
            cache : false,
            contentType: false,
            processData: false
        }).done(function (response) {
            if( response == 'success' )
            {
                Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 3000
                }).fire({
                    type: 'success',
                    title: 'Added New Image'
                })
                getImageStack();
            }
            else
            {
                Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 3000
                }).fire({
                    type: 'error',
                    title: response
                })
            }
        });
    });

    var instance;
    async function registerLazyJS()
    {
        if( instance )
            instance.destroy();

        instance = $('.lazy').lazy({
            chainable: false,
            bind: "event",
            afterLoad: function(element) {
                element.removeClass('lazy');
            }
        });
    }

    function getImageStack()
    {
        $('.details').append('<div class="loader"></div>');
        $.ajax({
            type: 'POST',
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getImageStack=',
        }).done(function (response) {
            $('.details').html('');
            for( var i=0 ; i<response.length ; i++ )
            {
                $('.details').append('<div class="col-md-3"><img alt="'+response[i]+'" src="data:image/png;base64," class="lazy" data-src="'+ <?php echo '"' . $url . '"';?> +'includes/blogimages/'+response[i]+'" width="100%"/></div>');
            }
            registerLazyJS();
            $('.details .loader').remove();
        });
    }
    getImageStack();
</script>