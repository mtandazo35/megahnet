<script>
function allowIp(ip) {
  fetch('ipsuccess.php', {  // Asegúrate de que el nombre del archivo es correcto
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ ip: ip }),
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log(data.success);
    } else {
      console.error(data.error);
    }
  })
  .catch(error => console.error('Error:', error));
}

// Llama a esta función con la IP que deseas permitir
allowIp('192.168.40.101');  // Reemplaza con la IP deseada


</script>