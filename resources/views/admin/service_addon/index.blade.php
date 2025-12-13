@extends('template')

@section('style')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
    
    :root {
        --text-color: #15172a;
        --blue-main: #1e3a8a;
        --blue-light: #2563eb;
        --blue-bg: #f0f6ff;
        --white: #ffffff;
        --border-color: #e5e7eb;
        --danger-color: #dc3545;
        --danger-hover: #b52b3a;
    }

    body {
        font-family: "Inter", sans-serif;
        background: var(--blue-bg);
        color: var(--text-color);
        margin: 0;
        padding: 0;
    }

    .page-wrapper { padding: 24px; }

.page-breadcrumb {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    background: var(--white);
    border-radius: 12px;
    padding: 12px 20px;
    box-shadow: 0 2px 8px rgba(30, 58, 138, 0.08);
    z-index: 10;
    position: relative;
}

   .breadcrumb-title {
    font-weight: 600;
    color: var(--text-color);
    font-size: 16px;
}

    /* ===================== */
    /* Buttons */
    /* ===================== */
.btn-custom {
    background: var(--blue-light);
    color: #fff !important;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    display: inline-block !important;
    position: relative !important;
    z-index: 20 !important;
    text-decoration: none !important;
    transition: all 0.3s ease !important;
}
    

.btn-custom:hover {
    background: var(--blue-main) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(30, 58, 138, 0.25);
}

    .card {
        background: var(--white);
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(30, 58, 138, 0.08);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .card:hover { box-shadow: 0 6px 16px rgba(37, 99, 235, 0.15); }
    .card-body { padding: 25px; }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
        font-size: 14px;
    }
    thead { background: var(--blue-main); color: var(--white); }
    th, td { text-align: center; vertical-align: middle; padding: 12px 8px; }
    tbody tr { background-color: var(--white); transition: background 0.25s ease; }
    tbody tr:hover { background-color: #e0edff; }

    .fixed_header2 { position: sticky; top: 0; background: var(--blue-main); color: white; z-index: 10; }

    .addons-box {
        padding: 15px;
        background: #f9fbff;
        border-top: 1px solid #dbeafe;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    /* Modal */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .modal-backdrop.active { display: flex; }
    .modal-content-custom {
        background: var(--white);
        padding: 20px 25px;
        border-radius: 12px;
        max-width: 400px;
        width: 100%;
        box-shadow: 0 6px 18px rgba(30,58,138,0.25);
        text-align: center;
        animation: fadeInScale 0.3s ease forwards;
    }
    @keyframes fadeInScale {
        0% { opacity: 0; transform: scale(0.8); }
        100% { opacity: 1; transform: scale(1); }
    }
    
    @media (max-width: 768px) {
    .page-breadcrumb {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .btn-custom {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        
        <div class="page-breadcrumb">
            <div class="breadcrumb-title">Servislar</div>
            <a href="{{ route('superadmin.service.create') }}" class="btn btn-custom">+ Yangi Service</a>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Servis nomi</th>
                                <th>Narxi</th>
                                <th>Vaqti</th>
                                <th>Izohi</th>
                                <th>Qo‘shimcha</th>
                                <th>Harakatlar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($services as $key=>$service)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->price }}</td>
                                <td>{{ $service->deadline }}</td>
                                <td>{{ $service->description }}</td>
                                <td>
                                    <button class="btn btn-custom toggle-addons" data-id="{{ $service->id }}">▼ Qo‘shimchalar</button>
                                </td>
                                <td>
                                    <a class="btn btn-custom" href="{{ route('service.addon.create', $service->id) }}">+ Qo‘shimcha</a>
                                    <a class="btn btn-warning" href="{{ route('superadmin.service.edit',['service'=>$service->id]) }}">O'zgartirish</a>
                                    <form action="{{ route('superadmin.service.destroy',['service'=>$service->id]) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-delete" data-name="{{ $service->name }}">O'chirish</button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="addons-row" id="addons-{{ $service->id }}" style="display:none;">
                                <td colspan="7">
                                    <div class="addons-box">
                                        <table class="table table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nomi</th>
                                                    <th>Izoh</th>
                                                    <th>Narx</th>
                                                    <th>Vaqti</th>
                                                    <th>Harakat</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($service->addons as $k => $addon)
                                                <tr>
                                                    <td>{{ $k+1 }}</td>
                                                    <td>{{ $addon->name }}</td>
                                                    <td>{{ $addon->description }}</td>
                                                    <td>{{ $addon->price }}</td>
                                                    <td>{{ $addon->deadline }}</td>
                                                    <td>
                                                        <a href="{{ route('superadmin.service_addon.edit',[$service->id,$addon->id]) }}" class="btn btn-warning btn-sm">O'zgartirish</a>
                                                        <form action="{{ route('superadmin.service_addon.destroy',[$service->id,$addon->id]) }}" method="POST" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">O'chirish</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-content-custom">
        <h5>Haqiqatan ham o'chirmoqchimisiz?</h5>
        <p id="modal-text">Bu amalni qaytarib bo'lmaydi.</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="cancelBtn">Bekor qilish</button>
            <button class="btn-confirm" id="confirmBtn">O'chirish</button>
        </div>
    </div>
</div>
@endsection

@section('scripte_include_end_body')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Qo‘shimcha row toggle
    document.querySelectorAll('.toggle-addons').forEach(btn=>{
        btn.addEventListener('click', function(){
            let id=this.dataset.id;
            let row=document.getElementById('addons-'+id);
            if(row.style.display==='none'||row.style.display===''){
                row.style.display='table-row';
                this.innerHTML='▲ Yopish';
            }else{
                row.style.display='none';
                this.innerHTML='▼ Qo‘shimchalar';
            }
        });
    });

    // Delete modal
    const modal=document.getElementById('deleteModal');
    const cancelBtn=document.getElementById('cancelBtn');
    const confirmBtn=document.getElementById('confirmBtn');
    let currentForm=null;

    document.querySelectorAll('.btn-delete').forEach(button=>{
        button.addEventListener('click', function(){
            const name=this.dataset.name;
            document.getElementById('modal-text').textContent=`"${name}" filialini o'chirishni tasdiqlaysizmi?`;
            modal.classList.add('active');
            currentForm=this.closest('form');
        });
    });

    cancelBtn.addEventListener('click', ()=>{ modal.classList.remove('active'); currentForm=null; });
    confirmBtn.addEventListener('click', ()=>{ if(currentForm) currentForm.submit(); });
    window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') modal.classList.remove('active'); });
});
</script>
@endsection
