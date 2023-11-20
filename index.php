<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloudflare helper</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

</head>

<body>
    <? include 'data.php' ?>

    <div id="cloudflare-helper-wrap">
        <h1 class="display-6">Cloudflare helper</h1>

        <form action="cloudflare.php" method="post">
            <div class="form-floating">
                <select name="cf_account" class="form-select mb-3" id="cf_account">
                    <? foreach ($credentials as $id => $account) : ?>
                        <option value=<? echo $id ?>><? echo $account['name'] ?></option>
                    <? endforeach ?>
                </select>
                <label for="cf_account">Выберите аккаунт</label>
            </div>
            <div class="form-floating">
                <textarea class="form-control mb-3" name="domains" id="domains"></textarea>
                <label for="domains">Вставьте список доменов через запятую или с новой строки</label>
            </div>
            <?php if (isset($_GET['success'])) : ?>
                <p class="alert alert-success mb-3">
                    Домены успешно добавлены.</p>
            <?php elseif (isset($_GET['error'])) : ?>
                <p class="alert alert-danger mb-3">
                    Домены не добавлены. Ошибка: <? echo $_GET['error'] ?> </p>
            <?php endif; ?>
            <button type="submit" class="btn btn-success">Добавить домены</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

</body>

</html>