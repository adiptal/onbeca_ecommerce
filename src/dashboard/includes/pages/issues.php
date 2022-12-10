<h2 class="breadcrump">
    Issues
</h2>

<div class="row details">
    <div id="table" class="table">
        <table>
            <thead>
                <tr>
                    <th>Table</th>
                    <th>Function</th>
                    <th>Issue</th>
                    <th>Registered At</th>
                    <th>Solve</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script>
    function getData()
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getIssues',
            dataType: "json"
        }).done(function(response) {
            $('tbody').html('');
            for( var i=0 ; i < response.length ; i++ )
            {
                $('tbody').append('<tr>');
                $('tr:last').append('<td>' + response[i][1] + '</td>');
                $('tr:last').append('<td>' + response[i][2] + '</td>');
                $('tr:last').append('<td>' + response[i][3] + '</td>');
                $('tr:last').append('<td>' + response[i][4] + '</td>');
                $('tr:last').append('<td><button class="btn check" onclick="deleteData(' + response[i][0] + ')"></button></td>');
                $('tbody').append('</tr>');
            }
        });
    }

    function deleteData( issue_id )
    {
        Swal({
            title: 'Is Issue Solved?',
            text: "If issue gets encountered again, it will be re-registered as issue automatically.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgba(40,40,40,.85)',
            cancelButtonColor: '#2D7DD2',
            confirmButtonText: 'Solved'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    method: "POST",
                    url: <?php echo '"' . $url . '"';?> + 'functions.php',
                    data: 'solvedIssue&issue_id='+ issue_id
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
    setInterval(getData , 60000);
</script>