<!-- CARD HEADING -->
<h2>Forgot</h2>
<!-- END CARD HEADING -->



<div class="form-input">
    <input type="email" id="user_email" required>
    <label for="user_email">Email</label>
</div>

<div class="button-bg">
    <button class="btn-link" onclick="loadFunction( 'signIn' , true )"><i class="fas fa-angle-left"></i> Back to Signin</button><br/>
    <button class="btn-form" onclick="forgot()"><i class="fas fa-unlock-alt"></i> Forgot</button>
</div>

<div class="form-input">
    <label class="error" id="error"></label>
</div>