<style>
.ayuda-articulo h5 { margin-top: 1.25rem; scroll-margin-top: 5rem; }
.ayuda-flujo-steps { display: flex; flex-direction: column; gap: 0.75rem; }
.ayuda-paso {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 0.75rem 1rem; background: var(--bs-light, #f8f9fa); border-radius: 0.5rem;
    border-left: 3px solid var(--bs-primary);
}
.ayuda-paso-num {
    flex-shrink: 0; width: 2rem; height: 2rem; border-radius: 50%;
    background: var(--bs-primary); color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem;
}
.ayuda-link-card { transition: box-shadow 0.15s, transform 0.15s; }
.ayuda-link-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.ayuda-botones-tabla td, .ayuda-botones-tabla th { vertical-align: middle; }
.btn-ayuda-contextual { white-space: nowrap; }

/* Mock UI wireframes */
.ayuda-mock {
    border: 2px dashed var(--bs-border-color, #dee2e6);
    border-radius: 0.5rem; overflow: hidden; background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.ayuda-mock-bar {
    display: flex; align-items: center; gap: 0.35rem;
    padding: 0.35rem 0.75rem; background: #e9ecef; border-bottom: 1px solid #dee2e6;
}
.ayuda-mock-dot { width: 8px; height: 8px; border-radius: 50%; background: #adb5bd; }
.ayuda-mock-label { margin-left: auto; font-size: 0.7rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.03em; }
.ayuda-mock-body { display: flex; min-height: 120px; }
.ayuda-mock-sidebar {
    width: 48px; background: #343a40; padding: 0.5rem 0.35rem; flex-shrink: 0;
}
.ayuda-mock-sidebar-item {
    height: 6px; background: rgba(255,255,255,0.25); border-radius: 2px; margin-bottom: 0.4rem;
}
.ayuda-mock-sidebar-item.active { background: rgba(255,255,255,0.7); width: 80%; }
.ayuda-mock-sidebar-item.short { width: 60%; }
.ayuda-mock-content { flex: 1; padding: 0.75rem; background: #f8f9fa; }
.ayuda-mock-header { margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #dee2e6; }
.ayuda-mock-pin { position: relative; display: inline-block; }
.ayuda-mock-pin::after {
    content: attr(data-pin); position: absolute; top: -8px; right: -8px;
    width: 1.25rem; height: 1.25rem; border-radius: 50%;
    background: var(--bs-danger); color: #fff; font-size: 0.65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2); pointer-events: none;
}
.ayuda-mock-accordion .accordion-button { font-size: 0.875rem; pointer-events: none; }

/* TOC sticky */
.ayuda-sidebar-sticky { position: sticky; top: 1rem; }
.ayuda-toc-list .nav-link { font-size: 0.875rem; color: var(--bs-body-color); }
.ayuda-toc-list .nav-link:hover { color: var(--bs-primary); }

/* Audiencia chips */
.ayuda-chip { font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 1rem; font-weight: 600; }
.ayuda-chip-repro { background: #212529; color: #fff; }
.ayuda-chip-empresa { background: #0d6efd; color: #fff; }
.ayuda-chip-ambos { background: #6f42c1; color: #fff; }

/* Flujo diagrama */
.ayuda-flujo-diagrama { overflow-x: auto; padding: 0.5rem 0; }
.ayuda-flujo-track { display: flex; align-items: center; gap: 0.25rem; min-width: max-content; justify-content: center; }
.ayuda-flujo-node {
    text-align: center; min-width: 72px; font-size: 0.75rem;
}
.ayuda-flujo-node strong { display: block; font-size: 0.8rem; }
.ayuda-flujo-node small { color: #6c757d; }
.ayuda-flujo-icon {
    display: inline-flex; width: 2.25rem; height: 2.25rem; border-radius: 50%;
    align-items: center; justify-content: center; color: #fff; margin-bottom: 0.25rem;
}
.ayuda-flujo-arrow { color: #adb5bd; font-size: 1rem; padding: 0 0.15rem; }

/* Screenshot anotado */
.ayuda-screenshot-frame { position: relative; border: 1px solid #dee2e6; border-radius: 0.375rem; overflow: hidden; }
.ayuda-screenshot-pin {
    position: absolute; transform: translate(-50%, -50%);
    width: 1.5rem; height: 1.5rem; border-radius: 50%;
    background: var(--bs-danger); color: #fff; font-weight: 700; font-size: 0.75rem;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3); border: 2px solid #fff;
}

/* Módulo index */
.ayuda-modulo-card .list-group-item { border-left: 0; border-right: 0; }
.ayuda-modulo-card .list-group-item:first-child { border-top: 0; }

/* Glosario */
.ayuda-glosario-icon { width: 1.75rem; text-align: center; color: var(--bs-primary); }

@media (max-width: 991px) {
    .ayuda-sidebar-sticky { position: static; }
    .ayuda-flujo-track { justify-content: flex-start; }
}
</style>
