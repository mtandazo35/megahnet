<script>
    function blockIp(ip) {
  fetch('ip.php', {  // Assuming your PHP file is named block_ip.php
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

// Call this function with the IP you want to block
blockIp('192.168.1.100');  // Replace with the desired IP

</script>