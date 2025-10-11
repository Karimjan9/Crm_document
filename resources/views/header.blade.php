<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>
            <div class="search-bar flex-grow-1">
                <div class="position-relative search-bar-box"></div>
            </div>

            @role('user')
                <div class="top-menu ms-auto">
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item mobile-search-icon">
                            <a class="nav-link" href="#"><i class="bx bx-search"></i></a>
                        </li>
                    </ul>
                </div>
            @endrole

            <div class="user-box dropdown">
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret"
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ url('avatar-2.png') }}" class="user-img" alt="user avatar">
                    <div class="user-info ps-3">
                        <p class="user-name mb-0">{{ auth()->user()->name }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('destroy') }}">
                            <i class='bx bx-log-out-circle'></i>
                            <span>{{ __("exit") }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <div id="notification_modals"></div>

    @role('user')
        <script>
            function notification_main() {
                $.ajax('/user/get_notification', {
                    type: 'GET',
                    success: function (data, status) {
                        let html = ``;
                        let html_modal = ``;

                        for (const iterator of data.notifications) {
                            html += `
                            <a class="dropdown-item" href="javascript:;">
                                <div class="d-flex align-items-center">
                                    <div class="notify bg-light-primary text-primary"><i class="bx bx-group"></i></div>
                                    <div class="flex-grow-1" data-bs-toggle="modal" data-bs-target="#exampleExtraLargeModal_${ iterator['id'] }">
                                        <h6 class="msg-name">${ iterator['title'] }<span class="msg-time float-end">${ iterator['date'] }</span></h6>
                                        <p class="msg-info">${ iterator['message'] }</p>
                                    </div>
                                </div>
                            </a>
                            `;
                            html_modal += `
                            <div class="nm modal fade" id="exampleExtraLargeModal_${ iterator['id'] }" tabindex="-1" aria-hidden="true" style="display: none;" data-id="${ iterator['id'] }">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">${ iterator['title'] }</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            ${ iterator['message'] }
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        }

                        $('#notification_list').html(html);
                        $('#notification_modals').html(html_modal);
                        $('#alert_notification_count').html(`<span class="alert-count">${data.count}</span>`);

                        var myModalEl = document.getElementsByClassName('nm');
                        for (const iterator of myModalEl) {
                            iterator.addEventListener('show.bs.modal', function () {
                                $.ajax('/user/set_status_notification', {
                                    type: 'POST',
                                    data: {
                                        "_token": "{{ csrf_token() }}",
                                        "id": $(iterator).data('id')
                                    },
                                });
                            });
                            iterator.addEventListener('hide.bs.modal', function () {
                                notification_main();
                            });
                        }
                    },
                    error: function (jqXhr, textStatus, errorMessage) {
                        console.log(errorMessage);
                    }
                });
            }

            notification_main();
            setInterval(() => { notification_main(); }, 100000);
        </script>
    @endrole
</header>

<div id="pwdModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-center">Parolni o`zgartirish</h5>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="text-center">
                                <form action="{{route('change-password')}}" method="post" class="row g-3">
                                    @csrf
                                    <fieldset>
                                        <div class="form-group">
                                            <label class="form-label">Yangi parol (minimum 4 ta simvol)</label>
                                            <input class="form-control input-lg password" required name="password" type="password">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Yangi parol (yana bir bor)</label>
                                            <input class="form-control input-lg password_confirmation" required name="password_confirmation" type="password">
                                        </div>
                                        <div class="error"></div>
                                    </fieldset>
                                    <div class="modal-footer">
                                        <div class="col-md-12">
                                            <button class="btn btn-info text-white" type="submit" id="submit">Saqlash</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$('[name="password"]').on("keyup change blur", function() {
    if($(this).val().length >= 4 && $(this).val().length <= 20) {
        $(this).attr('style', '');
    } else {
        $(this).attr('style', 'border: 4px solid red;');
        $("#submit").attr("disabled", "disabled");
    }
});
$(".password_confirmation").on("keyup", function() {
    var value_input1 = $(".password").val();
    var value_input2 = $(this).val();
    if(value_input1 != value_input2) {
        $(".error").html("Parollar bir xil emas!");
        $("#submit").attr("disabled", "disabled");
    } else {
        $("#submit").removeAttr("disabled");
        $(".error").html("");
    }
});
</script>


<style>
header {
    font-family: "Inter","ui-sans-serif","system-ui","-apple-system",
                 "Segoe UI","Roboto","Helvetica Neue","Arial",
                 "Noto Sans","sans-serif","Apple Color Emoji",
                 "Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
    background-color: #ffffff;
    color: #15172a;
    border-bottom: 1px solid rgba(21, 23, 42, 0.08);
    box-shadow: 0 1px 6px rgba(21, 23, 42, 0.1);
}
.topbar {
    background-color: #fff;
    height: 64px;
    display: flex;
    align-items: center;
    padding: 0 16px;
}
.navbar-nav .nav-link,
.user-box .user-info p {
    color: #15172a !important;
    font-weight: 500;
    letter-spacing: 0.3px;
}
.navbar-nav .nav-link:hover {
    color: #4e54c8 !important;
    transition: 0.3s ease;
}
.user-box .user-img {
    border-radius: 50%;
    border: 2px solid #15172a10;
    width: 40px;
    height: 40px;
    object-fit: cover;
    transition: all 0.3s ease;
}
.user-box:hover .user-img {
    border-color: #4e54c8;
}
.dropdown-menu {
    font-size: 14px;
    border-radius: 10px;
    border: 1px solid rgba(21, 23, 42, 0.1);
    box-shadow: 0 5px 25px rgba(21, 23, 42, 0.1);
}
.dropdown-menu .dropdown-item {
    color: #15172a;
    font-weight: 500;
}
.dropdown-menu .dropdown-item:hover {
    background-color: #f2f3f8;
    color: #4e54c8;
}
.mobile-toggle-menu i,
.navbar-nav i {
    color: #15172a;
    font-size: 20px;
    transition: 0.3s ease;
}
.mobile-toggle-menu i:hover,
.navbar-nav i:hover {
    color: #4e54c8;
}
.modal-content {
    font-family: inherit;
    border-radius: 12px;
    border: 1px solid rgba(21, 23, 42, 0.1);
    box-shadow: 0 5px 25px rgba(21, 23, 42, 0.15);
}
.modal-header h5 {
    color: #15172a;
    font-weight: 600;
}
.btn-info {
    background-color: #15172a;
    border: none;
    font-weight: 500;
}
.btn-info:hover {
    background-color: #202446;
}
.form-control {
    border: 1px solid rgba(21, 23, 42, 0.3);
    color: #15172a;
    background-color: #fff;
    font-family: inherit;
    border-radius: 8px;
}
.form-control:focus {
    border-color: #4e54c8;
    box-shadow: 0 0 0 3px rgba(78,84,200,0.15);
}
.error {
    color: #e63946;
    font-weight: 500;
}
</style>
