<table class="table table-striped">
    <thead>
        <tr>
            <th scope="col">Field name</th>
            <th scope="col">Field value</th>
        </tr>
    </thead>
    <tbody>    
        <?php
        $userinfo = $user->get_user_info($_SESSION['username']);
        foreach ($userinfo as $key => $val) :
            //Do not show password data
            if ($key == "password")
                continue;
            ?>
            <tr>
                <th scope="row"><?= $key ?></th>
                <td><?= $val ?></td>
            </tr>
<?php endforeach; ?>
    </tbody>
</table>

<div class="row">
    <div class="col-12 text-center">
        <a href="<?= $base_url ?>/?logout">Logout</a>
    </div>
</div>