<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <title>Document</title>
</head>
<body>
    

<?php 

//include_once ("conexion.php");




?>

<div class="container p-4 border border-dark">
<table id="datatable" class="table">
  <thead>
    <tr>
      <th scope="col">NOMBRE INTERFAZ</th>
      <th scope="col">LIMIT MAX</th>

    </tr>
  </thead>
  <tbody id="data">
    
  </tbody>
</table>
</div>




</body>
</html>
<script type="text/javascript">


let url = "queues.php"; // Update to point to your queues endpoint

fetch(url)
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => mostrarData(data))
  .catch(error => console.error('There was a problem with the fetch operation:', error));

const mostrarData = (data) => {
  console.log(data);
  let datos = "";

  // Check if the response has an error
  if (data.error) {
    datos = `<tr><td colspan="2">${data.error}</td></tr>`;
  } else {
    for (let i = 0; i < data.length; i++) {
      datos += `
        <tr>
          <td>${data[i].name}</td>
          <td>${data[i][".id"]}</td> <!-- Assuming 'max-limit' is a field you want to display -->
        </tr>
      `;
    }
  }

  document.getElementById('data').innerHTML = datos; // Insert rows into the table
};

</script>