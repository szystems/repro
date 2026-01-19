{{-- Partial para editar Sección 2: Educación y Formación --}}
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-mortarboard"></i> Educación y Formación Académica
    </h6>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="nivel_educativo" class="form-label">Nivel Educativo Máximo</label>
                <select class="form-control @error('seccion_' . $seccion . '.nivel_educativo') is-invalid @enderror" 
                        id="nivel_educativo" 
                        name="seccion_{{ $seccion }}[nivel_educativo]">
                    <option value="">Seleccione...</option>
                    <option value="primaria" {{ old('seccion_' . $seccion . '.nivel_educativo', $respuestas['nivel_educativo'] ?? '') == 'primaria' ? 'selected' : '' }}>
                        Primaria
                    </option>
                    <option value="secundaria" {{ old('seccion_' . $seccion . '.nivel_educativo', $respuestas['nivel_educativo'] ?? '') == 'secundaria' ? 'selected' : '' }}>
                        Secundaria
                    </option>
                    <option value="bachillerato" {{ old('seccion_' . $seccion . '.nivel_educativo', $respuestas['nivel_educativo'] ?? '') == 'bachillerato' ? 'selected' : '' }}>
                        Bachillerato
                    </option>
                    <option value="tecnico" {{ old('seccion_' . $seccion . '.nivel_educativo', $respuestas['nivel_educativo'] ?? '') == 'tecnico' ? 'selected' : '' }}>
                        Técnico
                    </option>
                    <option value="universitario" {{ old('seccion_' . $seccion . '.nivel_educativo', $respuestas['nivel_educativo'] ?? '') == 'universitario' ? 'selected' : '' }}>
                        Universitario
                    </option>
                    <option value="postgrado" {{ old('seccion_' . $seccion . '.nivel_educativo', $respuestas['nivel_educativo'] ?? '') == 'postgrado' ? 'selected' : '' }}>
                        Postgrado
                    </option>
                </select>
                @error('seccion_' . $seccion . '.nivel_educativo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="especialidad" class="form-label">Especialidad/Carrera</label>
                <input type="text" 
                       class="form-control @error('seccion_' . $seccion . '.especialidad') is-invalid @enderror" 
                       id="especialidad" 
                       name="seccion_{{ $seccion }}[especialidad]" 
                       value="{{ old('seccion_' . $seccion . '.especialidad', $respuestas['especialidad'] ?? '') }}">
                @error('seccion_' . $seccion . '.especialidad')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="institucion_educativa" class="form-label">Institución Educativa</label>
                <input type="text" 
                       class="form-control @error('seccion_' . $seccion . '.institucion_educativa') is-invalid @enderror" 
                       id="institucion_educativa" 
                       name="seccion_{{ $seccion }}[institucion_educativa]" 
                       value="{{ old('seccion_' . $seccion . '.institucion_educativa', $respuestas['institucion_educativa'] ?? '') }}">
                @error('seccion_' . $seccion . '.institucion_educativa')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="anio_graduacion" class="form-label">Año de Graduación</label>
                <input type="number" 
                       class="form-control @error('seccion_' . $seccion . '.anio_graduacion') is-invalid @enderror" 
                       id="anio_graduacion" 
                       name="seccion_{{ $seccion }}[anio_graduacion]" 
                       value="{{ old('seccion_' . $seccion . '.anio_graduacion', $respuestas['anio_graduacion'] ?? '') }}"
                       min="1950"
                       max="{{ date('Y') + 5 }}">
                @error('seccion_' . $seccion . '.anio_graduacion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-12">
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="titulo_en_tramite" 
                           name="seccion_{{ $seccion }}[titulo_en_tramite]" 
                           value="1"
                           {{ old('seccion_' . $seccion . '.titulo_en_tramite', $respuestas['titulo_en_tramite'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="titulo_en_tramite">
                        Título en trámite
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Idiomas --}}
    <div class="mt-4">
        <h6 class="text-secondary mb-3">Idiomas</h6>
        <div class="row">
            @php
                $idiomas = ['español', 'inglés', 'francés', 'portugués', 'alemán', 'italiano', 'chino', 'japonés'];
                $nivelesIdioma = ['basico' => 'Básico', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado', 'nativo' => 'Nativo'];
            @endphp
            
            @foreach($idiomas as $idioma)
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ ucfirst($idioma) }}</label>
                    <select class="form-control" 
                            name="seccion_{{ $seccion }}[idiomas][{{ $idioma }}]">
                        <option value="">No aplica</option>
                        @foreach($nivelesIdioma as $valor => $etiqueta)
                            <option value="{{ $valor }}" 
                                {{ old('seccion_' . $seccion . '.idiomas.' . $idioma, ($respuestas['idiomas'][$idioma] ?? '')) == $valor ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>
    
    {{-- Cursos y Certificaciones --}}
    <div class="mt-4">
        <h6 class="text-secondary mb-3">
            Cursos y Certificaciones
            <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="agregarCurso()">
                <i class="bi bi-plus"></i> Agregar
            </button>
        </h6>
        
        <div id="cursosContainer">
            @php
                $cursos = old('seccion_' . $seccion . '.cursos_certificaciones', $respuestas['cursos_certificaciones'] ?? []);
            @endphp
            
            @if(empty($cursos))
                <div class="row curso-item mb-3">
                    <div class="col-md-3">
                        <input type="text" 
                               class="form-control" 
                               name="seccion_{{ $seccion }}[cursos_certificaciones][0][nombre]" 
                               placeholder="Nombre del curso">
                    </div>
                    <div class="col-md-3">
                        <input type="text" 
                               class="form-control" 
                               name="seccion_{{ $seccion }}[cursos_certificaciones][0][institucion]" 
                               placeholder="Institución">
                    </div>
                    <div class="col-md-2">
                        <input type="number" 
                               class="form-control" 
                               name="seccion_{{ $seccion }}[cursos_certificaciones][0][anio]" 
                               placeholder="Año"
                               min="1950"
                               max="{{ date('Y') + 5 }}">
                    </div>
                    <div class="col-md-2">
                        <input type="text" 
                               class="form-control" 
                               name="seccion_{{ $seccion }}[cursos_certificaciones][0][duracion]" 
                               placeholder="Duración">
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <div class="form-check me-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="seccion_{{ $seccion }}[cursos_certificaciones][0][completado]" 
                                       value="1">
                                <label class="form-check-label">Completado</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarCurso(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                @foreach($cursos as $index => $curso)
                    <div class="row curso-item mb-3">
                        <div class="col-md-3">
                            <input type="text" 
                                   class="form-control" 
                                   name="seccion_{{ $seccion }}[cursos_certificaciones][{{ $index }}][nombre]" 
                                   value="{{ $curso['nombre'] ?? '' }}"
                                   placeholder="Nombre del curso">
                        </div>
                        <div class="col-md-3">
                            <input type="text" 
                                   class="form-control" 
                                   name="seccion_{{ $seccion }}[cursos_certificaciones][{{ $index }}][institucion]" 
                                   value="{{ $curso['institucion'] ?? '' }}"
                                   placeholder="Institución">
                        </div>
                        <div class="col-md-2">
                            <input type="number" 
                                   class="form-control" 
                                   name="seccion_{{ $seccion }}[cursos_certificaciones][{{ $index }}][anio]" 
                                   value="{{ $curso['anio'] ?? '' }}"
                                   placeholder="Año"
                                   min="1950"
                                   max="{{ date('Y') + 5 }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" 
                                   class="form-control" 
                                   name="seccion_{{ $seccion }}[cursos_certificaciones][{{ $index }}][duracion]" 
                                   value="{{ $curso['duracion'] ?? '' }}"
                                   placeholder="Duración">
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center">
                                <div class="form-check me-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="seccion_{{ $seccion }}[cursos_certificaciones][{{ $index }}][completado]" 
                                           value="1"
                                           {{ ($curso['completado'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">Completado</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarCurso(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function agregarCurso() {
    const container = document.getElementById('cursosContainer');
    const index = container.children.length;
    const seccion = {{ $seccion }};
    
    const html = `
        <div class="row curso-item mb-3">
            <div class="col-md-3">
                <input type="text" 
                       class="form-control" 
                       name="seccion_${seccion}[cursos_certificaciones][${index}][nombre]" 
                       placeholder="Nombre del curso">
            </div>
            <div class="col-md-3">
                <input type="text" 
                       class="form-control" 
                       name="seccion_${seccion}[cursos_certificaciones][${index}][institucion]" 
                       placeholder="Institución">
            </div>
            <div class="col-md-2">
                <input type="number" 
                       class="form-control" 
                       name="seccion_${seccion}[cursos_certificaciones][${index}][anio]" 
                       placeholder="Año"
                       min="1950"
                       max="{{ date('Y') + 5 }}">
            </div>
            <div class="col-md-2">
                <input type="text" 
                       class="form-control" 
                       name="seccion_${seccion}[cursos_certificaciones][${index}][duracion]" 
                       placeholder="Duración">
            </div>
            <div class="col-md-2">
                <div class="d-flex align-items-center">
                    <div class="form-check me-2">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="seccion_${seccion}[cursos_certificaciones][${index}][completado]" 
                               value="1">
                        <label class="form-check-label">Completado</label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarCurso(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function eliminarCurso(button) {
    const cursoItem = button.closest('.curso-item');
    cursoItem.remove();
}
</script>
@endpush