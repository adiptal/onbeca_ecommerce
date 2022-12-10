<!-- CARD HEADING -->
<h2>Sign Up</h2>
<!-- END CARD HEADING -->


<div class="form-input">
    <input type="text" id="user_first_name" required>
    <label for="user_first_name">First Name</label>
</div>

<div class="form-input">
    <input type="text" id="user_last_name" required>
    <label for="user_last_name">Last Name</label>
</div>

<div class="form-input">
    <input type="email" id="user_email" required>
    <label for="user_email">Email</label>
</div>

<div class="button-bg">
    <button class="btn-link" onclick="loadFunction( 'signIn' , true )"><i class="fas fa-angle-left"></i> Back to Signin</button><br/>
    <button class="btn-form" onclick="signUp()"><i class="fas fa-sign-in-alt"></i> SignUp</button>
</div>

<div class="form-input">
    <label class="error" id="error"></label>
</div>