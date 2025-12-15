<?php
    include 'admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Embedding Tamil News</title>
    <style>
        body {
            margin: 20px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        iframe {
            width: 1000px;
            width: 90%;
            height: 710px;
            border: none;
            margin-left: 300px;
        }
    </style>
</head>
<body>
    <iframe src="https://tamil.news18.com/" title="Tamil News 18"></iframe>
</body>
</html>
