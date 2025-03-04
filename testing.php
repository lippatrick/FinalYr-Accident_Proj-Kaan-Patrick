
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<button id="sendSmsButton" onclick="sendSms()">Send SMS</button>
<script>
    function sendSms() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'smsSend.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert(xhr.responseText); // Show response message (success/failure)
            }
        };
        
        xhr.send(); // Send request to PHP file
    }
</script>

</body>
</html>