<!DOCTYPE html>
<html>
<head>
    <title>Generate PDF Laravel 8 - phpcodingstuff.com</title>

    <style>

        {{ file_get_contents('bootstrap-3.3.7/dist/css/bootstrap.css') }}

    </style>

</head> 
<body>
    <div class="">
        
        <div class="card">

            <div class="card-body">

                <div class="row">
                    <div class="col-xs-5">
                    </div>
                    
                    <div class="col-xs-6">
                        <h3>
                            Osiyo Xalqaro Universiteti o'quv ishlari boyicha prorektor M.N.Nurullayevga

                            <br>

                            {{ $departament }} kafedrasi o'qituvchisi {{ $role }} {{ $teacher }} tomonidan

                        </h3>
                    </div>
                </div>
                {{-- <div class="row">
                    <div class="col-xs-5">
                    </div>

                    <div class="col-xs-6" style="font-size: 30px;">
                        <h3>
                        <h3>    
                    </div>
                </div> --}}

                <div class="row">
                    <div class="col-xs-12 text-center" style="margin-top: 20px;">
                        <h3>
                            Bildirishnoma
                        </h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12" style="margin-top: 20px;">
                        <h3 style="font-weight: normal;">
                            &nbsp;&nbsp;&nbsp;&nbsp; {{ $date }}-yil sanasida {{ $group }}-{{ $group_name }}  talabalarini {{ $pair }}-juftlikda {{ $subject }} fanidan   {{ $theme }} mavzusida
                            amaliy dars mashg'ulotini sayyor dars sifatida {{ $location }}da o'tishga ruxsat berishingizni so'rayman.
                        </h3>
                    </div>
                </div>

                <div class="row" style="margin-top: 500px;">
                    <div class="col-xs-3">
                        <h3>
                            {{ $date }}-yil
                        </h3>
                    </div>
                    <div class="col-xs-3">
                        <h3>
                            _____________
                        </h3>
                    </div>
                    <div class="col-xs-5">
                        <h3>
                            {{ $teacher }}
                        </h3>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>