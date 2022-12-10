<!-- CARD HEADING -->
<h2>Reset Password</h2>
<!-- END CARD HEADING -->


<div class="form-input">
    <input type="password" id="user_password" required>
    <label for="user_password">Password</label>
</div>

<div class="form-input">
    <input type="password" id="user_confirm" required>
    <label for="user_confirm">Confirm Password</label>
</div>

<div class="button-bg">
    <button class="btn-form" onclick="resetPassword()"><i class="fas fa-sync-alt"></i> Reset</button>
</div>

<div class="form-input">
    <label class="error" id="error"></label>
</div>