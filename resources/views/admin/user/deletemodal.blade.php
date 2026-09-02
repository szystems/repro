

  <!-- Modal -->
  <div class="modal fade" id="deleteModal-{{ $user->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModal" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title text-danger" id="deleteModal">
                <i class="bi bi-trash-fill text-danger"></i> Eliminar Usuario
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <p>¿Está seguro de eliminar a <strong>{{ $user->name }}</strong>?</p>
              <p class="small text-muted mb-0">No se borra el historial: el usuario deja de poder entrar. Si era titular de una empresa, asigne otro usuario principal.</p>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-info" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> Cancelar
              </button>
              <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                  <i class="bi bi-trash"></i> Eliminar
                </button>
              </form>
          </div>
      </div>
  </div>
</div>
