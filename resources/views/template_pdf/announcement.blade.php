<!DOCTYPE html>
<html>
<head>
    <title>Generate PDF Laravel 8 - phpcodingstuff.com</title>

    <style>

        {{ file_get_contents('bootstrap-3.3.7/dist/css/bootstrap.css') }}

    </style>

</head> 
<body style="border: 2px solid black;">
    <div class="">
        
        <div class="card" >

            <div class="card-body">

                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h1>E'LON<h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h3>
                            Assalomu aleykum hurmatli professor o'qituvchilar, talabalar sizlarni
                        </h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 text-center" style="width: 1035px;">
                        <h3>
                            {{ $date }} kuni soat {{ date_format(date_create($time), 'H:i') }} da {{ $pair }}-juftlik  {{ $location }} xonada {{ $subject }} fanidan {{ $teacher }} tomonidan o'tkaziladigan {{ $group }} {{ $group_name }} guruhida  "{{ $theme }}"
                        </h3>
                    </div>
                    
                </div>

                <div class="row">
                    <div class="col-xs-12 text-center" style="font-size: 35px;">
                        <h3>
                            mavzusi doirasida bo'lib o'tadigan ochiq darsimizga taklif qilamiz
                        </h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 text-center" style="font-size: 35px;">
                        <h3>
                            {{ $data }}
                        </h3>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>