<?php
$template = "userdata";
if (!$user->is_logged_in()) {
    if ($user->empty_db()) {
        $template = "register";
    } else {
        $template = "login";
    }
}
?>
<main role="main" class="container">

    <header>
        <h1><?= Helper::getTitle($template) ?></h1>
        <p class="lead">Simple login template</p>
    </header>

    <div class="row">
        <div class="col-12">
            <?php $loginerror = Helper::getErrorMessage();
            if ($loginerror) :
                ?>
                <div class="error">
                <?= $loginerror; ?>
                </div>    
            <?php
            endif;
            include ('templates/' . $template . '.php');
            ?>

        </div>
    </div>
</main><!-- /.container -->
