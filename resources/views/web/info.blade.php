<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pachavellam::Education - {{$pages_details->title}}</title>
    <style>
  

.card {
    margin-bottom: 100px;

    width: 90%;
    border-radius: 30px;
    margin-left: auto;
    margin-right: auto;
}

h1,h2,h3,h4,h5 {
    color:#000;
    font-weight:800;

   
}

p {
    color: #000;
    margin-left: 50px;
    margin-right: 25px;
    line-height: 1.3em;
    font-weight: 500;
}



    </style>
</head>
<body>


    <section id="terms-of-service">
        <div class="card">
            {!! $pages_details->content !!}
        </div>
    </section>
  
</body>
</html>