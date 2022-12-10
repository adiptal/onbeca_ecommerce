<!-- CARD HEADING -->
<h2>Sign In</h2>
<!-- END CARD HEADING -->


<div class="form-input">
    <input type="email" id="user_email" required>
    <label for="user_email">Email</label>
</div>

<div class="form-input">
    <input type="password" id="user_password" required>
    <label for="user_password">Password</label>
</div>

<div class="button-bg">
    <button class="btn-link" onclick="loadFunction( 'signUp' , true )">Create Account</button>
    <button class="btn-link" onclick="loadFunction( 'forgot' , true )">Forgot ?</button>
    <button class="btn-form" onclick="signIn()"><i class="fas fa-sign-in-alt"></i> Sign-In</button>
</div>

<div class="form-input">
    <label class="error" id="error"></label>
</div>