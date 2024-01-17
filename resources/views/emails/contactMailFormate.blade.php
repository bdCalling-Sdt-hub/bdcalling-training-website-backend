<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>bdCallingAcademy-contact-mail</title>
</head>
<body>
   <div class="" style="background: rgb(190, 172, 172);padding:20px;width:600px;border-radius:10px">
    <div style="text-align:center">
        <img src="https://www.bdcallingacademy.com/images/logo.png" height="70px" width="250px"/>
    </div>

        <h1 style="text-align: center">Contact infomation</h1>
        <h1>Name: {{$mailData["name"]}}</h1>
        <h1>Email: {{$mailData["email"]}}</h1>
        <h1>Phone number:{{$mailData["phone"]}}</h1>
        <h1>Selected Course:{{$mailData["category"]}}</h1>
        <h4><span style="font-size: 25px">Details:</span>{{$mailData["details"]}}</h4>
   </div>
</body>
</html>
