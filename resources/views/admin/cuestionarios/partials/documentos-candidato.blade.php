{{-- Los mismos PDF que la orden: se abren aparte para no perder lo que se está editando. --}}
<a href="{{ route('admin.cuestionarios.pdf', $cuestionario) }}"
   class="btn btn-outline-danger {{ $class ?? '' }}"
   target="_blank"
   rel="noopener noreferrer"
   title="PDF del formulario, el mismo de la orden">
    <i class="bi bi-file-pdf"></i> PDF formulario
</a>
<a href="{{ route('admin.cuestionarios.pdf-autorizacion', $cuestionario) }}"
   class="btn btn-outline-secondary {{ $class ?? '' }}"
   target="_blank"
   rel="noopener noreferrer"
   title="PDF de la autorización, el mismo de la orden">
    <i class="bi bi-file-earmark-check"></i> Autorización
</a>
