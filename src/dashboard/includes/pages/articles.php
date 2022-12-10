<div class="breadcrump">
    Article
</div>

<div class="row article-data">
    <div class="col-12">
        <div class="form-input">
            <p><strong>How to Define</strong><br/><br/>
                Links<br/>
                1. url::(link_url)::(link_name)::end<br/>
                eg&mdash; url::http://www.google.com::Google::end<br/>
                Result  on Article Page:<br>
                &#10094;a href=&#10077;http://www.google.com&#10077;&#10095;Google&#10094;/a&#10095;
            </p>
            <label for="fileList"><i class="fas fa-info-circle"></i> Note</label>
        </div>
    </div>
    
    <div class="col-12">
        <div class="row" id="row">
            
            <div class="form-input" id="toolbar">
                <ul>
                    <li><button class="btn add-element" id="add-section">Add Section</button></li>
                    <li><button id="save-article">Save Article</button></li>
                </ul>
            </div>

        </div>
    </div>
</div>

<script>
    $(function(){
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getProductInfo&product_id=<?php echo $product_id;?>',
            dataType: "json"
        }).done(function(response) {
            $('.breadcrump').append('&nbsp;&nbsp;&mdash;&nbsp;&nbsp;<span>' + response[0][1]+ '</span>');
        });
        
        // LOAD EDITABLE DATA
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'editProductArticle&product_id=<?php echo $product_id;?>',
        }).done(function(response) {
            var setData = $( response ).children('section');
            for( var i=0 ; i<setData.length ; i++ )
            {
                add( 'add-section' );
                for( var j=0 ; j<$(setData[i]).children().length ; j++ )
                {
                    var dataTags = $( $(setData[i]).children()[j] );
                    switch( dataTags.prop("tagName") )
                    {
                        case 'HEADER' :
                            add( 'add-title' , '#section-'+(i+1) );
                            $('#section-'+(i+1)+' .title:last input').val( dataTags.children().html() );
                        break;

                        case 'H4' :
                            add( 'add-title' , '#section-'+(i+1) );
                            $('#section-'+(i+1)+' .title:last input').val( dataTags.html() );
                        break;

                        case 'IMG' :
                            add( 'add-image' , '#section-'+(i+1) );
                            $('#section-'+(i+1)+' .image:last input').val( $('<div>').append($(dataTags).clone()).html() );
                        break;

                        case 'P' :
                            add( 'add-paragraph' , '#section-'+(i+1) );
                            $('#section-'+(i+1)+' .paragraph:last textarea').val( dataTags.html() );
                        break;

                        case 'ASIDE' :
                            add( 'add-aside' , '#section-'+(i+1) );
                            $('#section-'+(i+1)+' .aside:last textarea').val( dataTags.html() );
                        break;

                        case 'UL' :
                            add( 'add-list' , '#section-'+(i+1) );
                            for(var k=0 ; k<dataTags.children().length ; k++)
                            {
                                $('#section-'+(i+1)+' .list:last .form-input:last input').val( $(dataTags.children()[k] ).html() );
                                if( k != ( dataTags.children().length-1 ) )
                                {
                                    $('#section-'+(i+1)+' .list:last .more-list').click();
                                }
                            }
                        break;

                        case 'DIV' :
                            add( 'add-table' , '#section-'+(i+1) );
                            var colCount = dataTags.children('table').children().children('tr:first').children().length;
                            var rowCount = dataTags.children('table').children().children('tr').length;
                            $('#section-'+(i+1)+' .table:last .form-input input').val( colCount );
                            $('#section-'+(i+1)+' .table:last .create-table').click();
                            $('#section-'+(i+1)+' .table:last .temp-table').html(dataTags.html());
                            $('#section-'+(i+1)+' .table:last .temp-table tr').append('<td><button class="remove-row">x</button></td>');
                        break;
                    }
                }
            }
        });
    });

    function add( element , eventElement = null )
    {
        switch( element )
        {
            case 'add-section'  :   if( $( "section" ).length == 0 ||
                                    ($( ".section-data:last" ).children()['length'] != 0 &&
                                    $( ".section-data:last-child .title:last-child" ).length < 1 ) )
                    {
                        $('.article-data #row').append('<section id="section-'+ (($('section').length)+1) +'" class="col-12"><h2>Section '+ (($('section').length)+1) +'</h2><div class="row"><div class="col-11 offset-1 section-data"></div></div>'+
                        '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                        '<ul>'+
                        '<li><button class="add-element" id="add-title">Title</button></li>'+
                        '<li><button class="add-element" id="add-image">Image</button></li>'+
                        '<li><button class="add-element" id="add-paragraph">Paragraph</button></li>'+
                        '<li><button class="add-element" id="add-list">List</button></li>'+
                        '<li><button class="add-element" id="add-table">Table</button></li>'+
                        '<li><button class="add-element" id="add-aside">Aside</button></li>'+
                        '</ul>'+
                        '</section>');
                        $( "#toolbar" ).before( $( "section" ) );
                    }
            break;

            case 'add-title'  :
                    $( eventElement + ' .section-data' ).append(
                        '<div class="form-input title" id="title-'+ $('.section-data .title').length +'">'+
                        '<div class="window">'+
                        '<button class="push-up"><i class="fas fa-chevron-up"></i></button>'+
                        '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                        '</div>'+
                        '<input type="text" required>'+
                        '<label>Section Title</label>'+
                        '</div>'
                    );
            break;

            case 'add-image'  :
                    $( eventElement + ' .section-data' ).append(
                        '<div class="form-input image" id="image-'+ $('.section-data .image').length +'">'+
                        '<div class="window">'+
                        '<button class="push-up"><i class="fas fa-chevron-up"></i></button>'+
                        '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                        '</div>'+
                        '<input type="text" required>'+
                        '<label>Section Image Tag Add</label>'+
                        '</div>'
                    );
            break;

            case 'add-paragraph'  :
                    $( eventElement + ' .section-data' ).append(
                        '<div class="form-input paragraph" id="paragraph-'+ $('.section-data .paragraph').length +'">'+
                        '<div class="window">'+
                        '<button class="push-up"><i class="fas fa-chevron-up"></i></button>'+
                        '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                        '</div>'+
                        '<textarea rows="10" type="text" required></textarea>'+
                        '<label>Section Text</label>'+
                        '</div>'
                    );
            break;

            case 'add-aside'  :
                    $( eventElement + ' .section-data' ).append(
                        '<div class="form-input aside" id="aside-'+ $('.section-data .aside').length +'">'+
                        '<div class="window">'+
                        '<button class="push-up"><i class="fas fa-chevron-up"></i></button>'+
                        '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                        '</div>'+
                        '<textarea rows="10" type="text" required></textarea>'+
                        '<label>Section Aside</label>'+
                        '</div>'
                    );
            break;

            case 'add-list' :
                    $( eventElement + ' .section-data' ).append(
                        '<div class="list form-input" id="list-'+ $('.section-data .list').length +'">'+
                        '<div class="window">'+
                        '<button class="push-up"><i class="fas fa-chevron-up"></i></button>'+
                        '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                        '</div>'+
                        '<div class="form-input">'+
                        '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                        '<input type="text" required>'+
                        '<label>List Data</label>'+
                        '</div>'+
                        '<button class="more-list">Add Data</button>'+
                        '</div>'
                    );
            break;

            case 'add-table' :
                    $( eventElement + ' .section-data' ).append(
                        '<div class="table form-input" id="table-'+ $('.section-data .table').length +'">'+
                        '<div class="window">'+
                        '<button class="push-up"><i class="fas fa-chevron-up"></i></button>'+
                        '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                        '</div>'+
                        '<div class="form-input">'+
                        '<input type="number" min="0" max="20" required>'+
                        '<label>Column Count</label>'+
                        '</div>'+
                        '<button class="create-table">Add Table</button>'+
                        '</div>'
                    );
            break;
        }
        $('main').scrollTop( $('main')[0].scrollHeight );
    }

    function createTableDataTags( tagName , data )
    {
        switch( tagName )
        {
            case 'TBODY' : return '<td>'+ data +'</td>';
            break;
            
            default : return '<th>'+ data +'</th>';
            break;
        }
    }
    
    $(document).on( 'click' , '.push-up' , function(){
        var index= $(this).closest('.form-input').index();
        if( index > 0 )
        {
            $data = $(this).closest('.form-input');
            $data.insertBefore($(this).closest('.section-data').find('> .form-input').eq(index-1));
        }
    });
    
    $(document).on( 'click' , '.add-element' , function(){
        add( $(this).attr('id') , '#'+$(this).closest('section').attr('id') );
    });

    $(document).on( 'click' , '.create-table' , function(){
        var count = $( this ).parent().children('.form-input').children(' input' ).val();

        if( /^\d+$/.test( count ) )
        {
            inputs = '';
            for( var i=1 ; i<=count ; i++ )
            {
                inputs += 
                    '<div class="form-input">'+
                    '<input type="text" required>'+
                    '<label>Row Data '+ i +'</label>'+
                    '</div>';
            }
            $( this ).parent().html(
                '<div class="temp-table"><table><thead></thead><tbody></tbody><tfoot></tfoot></table></div>'+
                '<button class="remove-element"><i class="fas fa-times"></i></button>'+
                inputs+
                '<button class="add-to-table" data-count="'+ count +'">Add Data</button>'+
                '</div>'
            );
            
            $( this ).before( $( this ).parent().children('.form-input') );
        }
    });

    $(document).on( 'click' , '.add-to-table' , function(){
        var input = $( this ).parent().children('.form-input').children('input');
        var data = '';
        for( var i=0 ; i<$(this).data('count') ; i++ )
        {
            data += '<td>'+$( input[i] ).val()+'</td>';
        }
        data += '<td><button class="remove-row">x</button></td>';
        input.val('');
        $( this ).parent().children('.temp-table').children('table').children('tbody').append('<tr>'+data+'</tr>');
    });
    
    $(document).on( 'click' , 'tr>*:not(:last)' , function(){
        for( var i=0 ; i<$(this).parent().children().length-1 ; i++ )
        {
            $( $(this).closest('.table').children('.form-input')[i] ).children('input').val( $( $(this).parent().children()[i] ).html() );
        }
        $(this).closest('.table').children('button , .nowidth').remove();
        $(this).closest('.table').append('<button data-tag="'+ $(this).parent().parent()[0].nodeName +'" data-index="'+ $(this).parent().index() +'" class="change-from-table">Change Data</button><button class="reset-inputs">Reset</button>'+
        '<div class="form-input nowidth">'+
        '<label class="radio"><input type="radio" name="table" value="THEAD">&emsp;Head</label>'+
        '</div>'+
        '<div class="form-input nowidth">'+
        '<label class="radio"><input type="radio" name="table" value="TBODY" checked>&emsp;Body</label>'+
        '</div>'+
        '<div class="form-input nowidth">'+
        '<label class="radio"><input type="radio" name="table" value="TFOOT">&emsp;Foot</label>'+
        '</div>'
        );
        
        $(this).closest('.table').find('input[value="'+ $(this).parent().parent()[0].tagName +'"]').click();
    });
    
    function clearTableInputs( tableElement )
    {
        tableElement.children('.form-input').children('input').val('');
        tableElement.children('button , .nowidth').remove();
        
        var length = tableElement.children('.form-input').length;
        tableElement.append('<button data-count="'+ length +'" class="add-to-table">Add Data</button>');
    }

    $(document).on( 'click' , '.radio' , function(){
        $(this).children('input').attr('checked', true);
    });

    $(document).on( 'click' , '.reset-inputs' , function(){
        clearTableInputs( $(this).closest('.table') );
    });
    
    $(document).on( 'click' , '.change-from-table' , function(){
        var table = $(this).closest('.table');
        var tableTag = table.children('.temp-table').children();
        var changeTag = $('input[type=radio][name=table]:checked').val();
        var data = '';
        
        for( var i=0; i<table.children('.form-input').length - 3 ; i++ )
        {
            data += createTableDataTags( changeTag , $( table.children('.form-input')[i] ).children('input').val() );
        }
        data += createTableDataTags( changeTag , '<button class="remove-row">x</button>' );

        if( changeTag != tableTag.children($(this).data('tag'))[0].tagName )
        {
            tableTag.children($(this).data('tag')).children('tr:nth-child('+($(this).data('index')+1)+')').remove();
            tableTag.children( changeTag ).append('<tr>'+ data +'</tr>');
        }
        else
        {
            tableTag.children($(this).data('tag')).children('tr:nth-child('+($(this).data('index')+1)+')').html(data);
        }
        
        clearTableInputs( table );
    });

    $(document).on( 'click' , '.remove-row' , function(){
        $(this).closest('tr').remove();
    });

    $(document).on( 'click' , '.more-list' , function(){
        $( this ).parent().append(
            '<div class="form-input">'+
            '<button class="remove-element"><i class="fas fa-times"></i></button>'+
            '<input type="text" required>'+
            '<label>List Data</label>'+
            '</div>'
        );
        
        $( this ).before( $( this ).parent().children('.form-input') );
    });

    $(document).on( 'click' , '.remove-element' , function(){
        if( $(this).parent().prop("tagName") == 'SECTION' )
        {
            var init = $(this).closest('section').attr('id').split("-")[1];
            var count = $('[id^='+ $(this).closest('section').attr('id').split("-")[0] +']').length;
            init++;
            $(this).closest('section').remove();
            for( var i= init ; i < count+1 ; i++ )
            {
                $( '#'+$(this).closest('section').attr('id').split("-")[0]+'-'+i ).attr( 'id' , $(this).closest('section').attr('id').split("-")[0]+'-'+(i-1) );
            }
        }
        else
        {
            if( $(this).closest( '.form-input' ).attr("id") )
            {
                var init = $(this).closest( '.form-input' ).attr('id').split("-")[1];
                var count = $('[id^='+ $(this).closest( '.form-input' ).attr('id').split("-")[0] +']').length;
                init++;
            }
            $(this).closest( '.form-input' ).remove();
            for( var i= init ; i < count+1 ; i++ )
            {
                $( '#'+$(this).closest( '.form-input' ).attr('id').split("-")[0]+'-'+i ).attr( 'id' , $(this).closest( '.form-input' ).attr('id').split("-")[0]+'-'+(i-1) );
            }
        }
    });

    $(document).on( 'click' , '#save-article' , function(){
        if( $( '.title input' ).val() != '' && $( '.title input' ).val() != null
        || $( '.paragraph textarea' ).val() != '' && $( '.paragraph textarea' ).val() != null
        || $( '.list input' ).val() != '' && $( '.list input' ).val() != null )
        {
            var article_data = '<article class="article"><header><h2>'+$( '.breadcrump span' ).html()+'</h2></header>';
            for( i=1 ; i<$('section').length+1 ; i++  )
            {
                var title = false;
                article_data += '<section>';
                for( j=0 ; j<$('#section-'+[i]+' .form-input[id]').length ; j++  )
                {
                    var element = $($('#section-'+[i]+' .form-input[id]')[j]).attr('id');
                    switch( element.split('-')[0] )
                    {
                        case 'title'    :
                            if( !title )
                            {
                                article_data += '<header><h3>'+$('#'+element+' input').val()+'</h3></header>';
                                title = true;
                            }
                            else{
                                article_data += '<h4>'+$('#'+element+' input').val()+'</h4>';
                            }
                        break;

                        case 'image'    :
                            article_data += $('#'+element+' input').val();
                        break;

                        case 'paragraph'    :
                            article_data += '<p>'+$('#'+element+' textarea').val()+'</p>';
                        break;

                        case 'aside'    :
                            article_data += '<aside>'+$('#'+element+' textarea').val()+'</aside>';
                        break;

                        case 'list'    :
                            article_data += '<ul>';
                            for(var k=0 ; k<$('#'+element+' .form-input input').length ; k++)
                            {
                                article_data += '<li>'+$($('#'+element+' .form-input input')[k]).val()+'</li>';
                            }
                            article_data += '</ul>';
                        break;

                        case 'table' :
                            deleteCol = $('#'+element+' table tr .remove-row').closest('tr');
                            for( var k=0 ; k<deleteCol.length ; k++ )
                            {
                                $( deleteCol )[k].deleteCell(-1);
                            }
                            $('#'+element+' table button').remove();
                            article_data += '<div class="table">' + $('#'+element+' .temp-table').html() + '</div>';
                        break;
                    }
                }
                article_data += '</section>';
            }
            article_data += '</article>';
            var article_data = encodeURIComponent(article_data);
            $('#toolbar').html('<style>.savingloader{padding: 1em 1em 0 0}.article-saving{color: #3B9CD1;font-size: 1.5em;position:relative;top:-.6em; font-weight:500; margin-right: .25em;}.lds-ring{display: inline-block; position: relative; width: 32px; height: 32px;}.lds-ring div{box-sizing: border-box; display: block; position: absolute; width: 20px; height: 20px; margin: 2px; border: 2px solid #3B9CD1; border-radius: 50%; animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite; border-color: #3B9CD1 transparent transparent transparent;}.lds-ring div:nth-child(1){animation-delay: -0.45s;}.lds-ring div:nth-child(2){animation-delay: -0.3s;}.lds-ring div:nth-child(3){animation-delay: -0.15s;}@keyframes lds-ring{0%{transform: rotate(0deg);}100%{transform: rotate(360deg);}}</style><div class="savingloader"><span class="article-saving">Saving</span><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>');
            $.ajax({
                method: "POST",
                url: <?php echo '"' . $url . '"';?> + "functions.php",
                data: 'manageProductArticle&product_id=<?php echo $product_id;?>&article_data=' + article_data ,
            }).done(function(response){
                $('#toolbar').html('<style>.div{padding: 1.8em 1em 0 0}.article-saved{color: #123040;font-size: 1.5em;position:relative;top:-.6em; font-weight:500; margin-right: .25em;}.article-saved svg{color:#00CC66;}</style><div class="div"><span class="article-saved">Done&nbsp;&nbsp;<i class="fas fa-check-circle"></i></span></div>');
                setTimeout(function(){
                    window.location.replace(<?php echo '"'.$url.'articles/'.$product_id.'"';?>);
                } , 500);
            });
        }
        else
        {
            $('.warning').remove();
            $('.article-data .row:first').append('<div class="warning">Fill All Datas</div>');
            $('.warning').stop().slideDown(200);
            setTimeout(function(){
                $('.warning').slideUp(200);
            } , 5000);
            $('main').scrollTop( $('main')[0].scrollHeight );
        }
    });
</script>