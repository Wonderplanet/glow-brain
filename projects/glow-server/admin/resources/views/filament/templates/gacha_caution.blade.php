<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="ja">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="../assets/reset.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/simplebar.css">
    <script type="text/javascript" src="../assets/simplebar.min.js"></script>
    <script type="text/javascript" src="../assets/index.js" defer></script>
</head>

<body>

    <div id="scrollable-contents">
        <div class="detail">
            {!! htmlspecialchars_decode($text) !!}
        </div>
    </div>

    <script defer>
        new SimpleBar(document.getElementById("scrollable-contents"), {
            autoHide: true
        })
    </script>
</body>

</html>
