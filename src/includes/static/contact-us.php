<article class="static article col-12 col-sm-10 offset-sm-1">
    <header>
        <h2>Get In  Touch</h2>
    </header>

    <section class="contact">
        <header>
            <h3>Contact Us</h3>
        </header>

        <div class="row contact-form">

            <div class="col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2">
                <div class="form-input">
                    <input id="name" type="text" required>
                    <label for="name">Name</label>
                </div>
            </div>

            <div class="col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2">
                <div class="form-input">
                    <input id="email" type="email" required>
                    <label for="email">Email</label>
                </div>
            </div>

            <div class="col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2">
                <div class="form-input">
                    <textarea id="message" rows="8"></textarea>
                    <label for="message">Message</label>
                </div>
            </div>

            <div class="col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2">
                <button type="button" aria-labelledby="contact" class="btn" id="contact">Send Message</button>
            </div>

            <p></p>

        </div>

        <h4>OR</h4>

        <a href="mailto:admin@onbeca.com">admin@onbeca.com</a>

    </section>
</article>

<script>
    $( document ).on( "main localeReady" , function() {
        $( document ).trigger( "stopLoading" );
    });
    
    $(document).on( 'click' , '#contact' , function(){
        var element = $(this);
        var name = $('#name').val();
        var email_id = $('#email').val();
        var message = $('#message').val();
        var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        
        if( name != '' && email_id != '' && message != '' )
        {
            if( regex.test( email_id ) )
            {
                $.ajax({
                    type: 'POST',
                    url: <?php echo '"' . $url . '"';?> + 'dashboard/functions.php',
                    data: 'contact&name=' + encodeURIComponent(name) + '&email_id=' + encodeURIComponent(email_id) + '&message=' + encodeURIComponent(message)
                });
                element.parents('.row').children('p').html('<i class="far fa-check-circle"></i>&emsp;We will get in touch with you as soon as we process your email.').fadeIn().css("display" , "block");
                element.parent().remove();
            }
            else
            {
                element.closest('.row').children('p').html('<i class="fas fa-exclamation-triangle"></i>&emsp;Email Format Invalid').fadeIn().css("display" , "block").delay(5000).fadeOut();
            }
        }
        else
        {
            element.closest('.row').children('p').html('<i class="fas fa-exclamation-triangle"></i>&emsp;Fill all credentials').fadeIn().css("display" , "block").delay(5000).fadeOut();
        }
    });
</script>