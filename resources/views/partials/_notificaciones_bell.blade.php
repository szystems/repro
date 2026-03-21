{{-- Campana de notificaciones --}}
<div class="dropdown d-inline-block">
    <a href="#" class="header-action-link position-relative" id="notificacionesBell" data-bs-toggle="dropdown"
        data-bs-auto-close="outside" aria-expanded="false">
        <i class="bi bi-bell fs-5"></i>
        <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle d-none" 
              id="notificacionesCount" style="font-size: 0.65rem; padding: 2px 5px;"></span>
    </a>
    <div class="dropdown-menu dropdown-menu-end shadow-lg" style="width: 360px; max-height: 420px; overflow-y: auto;" 
         aria-labelledby="notificacionesBell">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <strong>Notificaciones</strong>
            <a href="#" id="marcarTodasLeidas" class="text-decoration-none small text-primary d-none">
                Marcar todas como leídas
            </a>
        </div>
        <div class="dropdown-divider"></div>
        <div id="notificacionesLista">
            <div class="text-center text-muted py-3">
                <i class="bi bi-bell-slash fs-4 d-block mb-1"></i>
                <small>Sin notificaciones</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cargarNotificaciones() {
    fetch('{{ route("notificaciones.index") }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const countBadge = document.getElementById('notificacionesCount');
        const lista = document.getElementById('notificacionesLista');
        const marcarTodas = document.getElementById('marcarTodasLeidas');

        if (data.count > 0) {
            countBadge.textContent = data.count > 99 ? '99+' : data.count;
            countBadge.classList.remove('d-none');
            marcarTodas.classList.remove('d-none');
        } else {
            countBadge.classList.add('d-none');
            marcarTodas.classList.add('d-none');
        }

        if (data.notificaciones.length === 0) {
            lista.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-bell-slash fs-4 d-block mb-1"></i><small>Sin notificaciones</small></div>';
            return;
        }

        lista.innerHTML = data.notificaciones.map(n => `
            <a href="#" class="dropdown-item py-2 notificacion-item" data-id="${n.id}" data-url="${n.url}" style="white-space: normal;">
                <div class="d-flex align-items-start gap-2">
                    <span class="text-${n.color}"><i class="bi ${n.icono} fs-5"></i></span>
                    <div class="flex-grow-1">
                        <div class="small">${n.mensaje}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">${n.tiempo}</div>
                    </div>
                </div>
            </a>
        `).join('<div class="dropdown-divider my-0"></div>');

        document.querySelectorAll('.notificacion-item').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const url = this.dataset.url;
                fetch('{{ url("notificaciones") }}/' + id + '/leer', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).then(() => { window.location.href = url; });
            });
        });
    })
    .catch(() => {});
}

document.getElementById('marcarTodasLeidas')?.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    fetch('{{ route("notificaciones.leer-todas") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    }).then(() => cargarNotificaciones());
});

document.addEventListener('DOMContentLoaded', () => cargarNotificaciones());
setInterval(cargarNotificaciones, 30000);
</script>
@endpush
