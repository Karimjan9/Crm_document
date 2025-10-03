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
                    
                    <div class="col-xs-7" style="font-size: 30px;">
                        Osiyo Xalqaro Universiteti oqiv ishlari boyicha prorektor Nurullayev M.N.
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-5">
                    </div>
                    
                    <div class="col-xs-7" style="font-size: 30px;">
                        {{ $block_1 }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 text-center" style="font-size: 35px;">
                        Bildirishnoma 
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12" style="font-size: 20px;">
                        {{ $date }}-yil sohasida 
                        {{ $block_2 }}
                        fanidan amaliy dars mashqulofini sayyor dars sifafida ofashga ruxsaf berishingiz sorayman.
                    </div>
                </div>

                <div class="row" style="margin-top: 500px;">
                    <div class="col-xs-2">
                        {{ $date }}-yil
                    </div>
                    <div class="col-xs-4">
                        _____________
                    </div>
                    <div class="col-xs-5">
                        _____________
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>

{{-- 118   3712500
119   3662500
120   3712500
121   21812500
122   34812500
123   3812500
124   3692500
125   3662500
126    3512500 --}}


{{-- 127   1900000  1712500
128   2800000   2612500
129   2600000   2412500
130   2200000   2012500 --}}
