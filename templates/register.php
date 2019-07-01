<form method="POST" class="user" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
    <fieldset>
        <p>        
            <label for="username">Username:<span>*</span></label>
            <input type="text" id="username" name="username" placeholder="username" />
        </p>
        <p>
            <label for="email">E-mail:<span>*</span></label>
            <input type="email" id="email" name="email" placeholder="email" />
        </p>
        <p>        
            <label for="password">Password:<span>*</span></label>
            <input type="password" id="password" name="password" placeholder="password" />
        </p>
        <p>        
            <label for="confirm">Password again:<span>*</span></label>
            <input type="password" id="confirm" name="confirm" placeholder="repeat password" />
        </p>
        <p>
            <label for="firstname">First name:</label>
            <input type="text" id="firstname" name="firstname" placeholder="first name" />
        </p>
        <p>
            <label for="lastname">Last name:</label>
            <input type="text" id="lastname" name="lastname" placeholder="last name" />
        </p>
        <p>
            <label for="mobile">Mobile phone:</label>
            <input type="text" id="mobile" name="mobile" placeholder="phone number" />        
        </p>
        <p>Fields marked by <span>*</span> is required to fill in.</p>
    </fieldset>    
    <input id="button-register" class="btn btn-primary" type="submit" name="register" value="Register">
</form>