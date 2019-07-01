<form id="login" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>        
        <p>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="user" />

        </p>
        <p>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="password" />
        </p>
        <p>
            <input type="checkbox" id="remember" name="remember" />
            <label for="remember">Remember password?</label>
        </p>
    </fieldset>
    <input class="btn btn-primary" id="button-login" type="submit" name="login" value="Log in">
</form>