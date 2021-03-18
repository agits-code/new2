<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <style>
       header {
           background: darkgrey;
           padding : 2em;
           text-align: center;
       }
    </style>
</head>
<body>
     <header>
      <!--  <h1>hello,  <?= $name;?></h1> -->
     </header>

    <ul>
        <?php foreach ($files as $file) : ?>
            <?php foreach ($file as $key => $value) : ?>

            <li><strong><?= $key; ?> : </strong><?= $value;?></li>


            <?php endforeach; ?>
        <hr>
        <?php endforeach; ?>
    </ul>
</body>
</html>