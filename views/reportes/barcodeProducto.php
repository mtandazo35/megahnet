<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <style>
*{
    margin: 0;
    padding: 0;
}
body{
    padding: 20px;
}

img{
    width: 40%;
    height: 4%;
    padding-top: 15px;
}
.container{
    width: 100%;
    display: inline-block;

    justify-content: center;
   
}

.precio{
    padding-left: 70px;
}

    </style>
</head>

<body>

<div class="container">
<?php
for ($i=0; $i < 15 ; $i++) { ?>
   
   <img style="display: flex; margin:auto; aling" src="<?php echo BASE_URL . 'assets/images/barcode/' . $data['productos']['codigo'] . '.png'; ?>">
            <p style="font-size: 10px;" class="precio"><?php echo '$'. $data['productos']['precio_venta']; ?></p>
            <p style="font-size: 10px;"><?php echo $data['productos']['descripcion']; ?></p>

   
          
<?php }

?>
  </div>
            
  
</body>

</html>