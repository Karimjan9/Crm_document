<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
	<script src="{{ url('assets/js/jquery.min.js') }}"></script>
</head>
<body>
    
    <div id="test">

    </div>

    <script>
        
        let data = [
            {
                "id" : 1,
                "name" : "IT kafedra",
                "child" : [
                    {
                        "id" : 2,
                        "name" : "Cyber",
                        "child" : [
                            {
                                "id" : 3,
                                "name" : "Cyber HTTP",
                                "child" : [
                                    {
                                        "id" : 4,
                                        "name" : "junior"
                                    },
                                    {
                                        "id" : 5,
                                        "name" : "midle"
                                    },
                                    {
                                        "id" : 6,
                                        "name" : "senior"
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        "id" : 7,
                        "name" : "Testir",
                        "child" : [
                            {
                                "id" : 8,
                                "name" : "Test ingener",
                                "child" : [
                                    {
                                        "id" : 9,
                                        "name" : "Test yadro",
                                        "child" : [
                                            {
                                                "id" : 10,
                                                "name" : "junior"
                                            },
                                            {
                                                "id" : 11,
                                                "name" : "midle"
                                            },
                                            {
                                                "id" : 12,
                                                "name" : "senior"
                                            }
                                        ]
                                    },
                                    {
                                        "id" : 13,
                                        "name" : "Test http",
                                        "child" : [
                                            {
                                                "id" : 14,
                                                "name" : "junior"
                                            },
                                            {
                                                "id" : 15,
                                                "name" : "midle"
                                            },
                                            {
                                                "id" : 16,
                                                "name" : "senior"
                                            }
                                        ]
                                    },
                                ]
                            }
                        ]
                    }
                ]
            }
        ];

        let html = `<option></option>`;

        for (const item of data) {
            
            html += `<option value="${ item.id }" data-json='${ JSON.stringify(item) }'>${ item.name }</option>`
        }

        $('#test').html(`<select id="er" onclick="set_child(this)">${html}</select>`)

        function set_child(object) {
            
            let item = $(object).find("option:selected").data("json");
            
            if(item){
                list(item.child);
            }

        }

        function list(items) {

            let html = '<option></option>';
            for (const item of items) {
            
                html += `<option value="${ item.id }" data-json='${ JSON.stringify(item) }'>${ item.name }</option>`
            }

            $('#test').append(`<select onclick="set_child(this)">${html}</select>`)
        }


    </script>
</body>
</html>
