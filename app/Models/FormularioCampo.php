<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class FormularioCampo extends Model
{
    protected $table = 'formulario_campos';
    
    protected $fillable = [
        'tipo_formulario',
        'seccion',
        'campo',
        'etiqueta',
        'tipo_campo',
        'opciones',
        'placeholder',
        'ayuda',
        'requerido',
        'orden',
        'activo',
        'validaciones'
    ];
    
    protected $casts = [
        'opciones' => 'array',
        'requerido' => 'boolean',
        'activo' => 'boolean'
    ];
    
    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_formulario', $tipo);
    }
    
    public function scopePorSeccion($query, string $seccion)
    {
        return $query->where('seccion', $seccion);
    }
    
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }
    
    // Métodos estáticos útiles
    public static function getCamposPorTipoYSeccion(string $tipo, string $seccion): Collection
    {
        return static::porTipo($tipo)
            ->porSeccion($seccion)
            ->activos()
            ->ordenados()
            ->get();
    }
    
    public static function getSeccionesPorTipo(string $tipo): array
    {
        return static::porTipo($tipo)
            ->activos()
            ->distinct('seccion')
            ->pluck('seccion')
            ->toArray();
    }
    
    // Métodos de instancia
    public function getValidacionesArray(): array
    {
        if (!$this->validaciones) {
            return [];
        }
        
        return explode('|', $this->validaciones);
    }
    
    public function esRequerido(): bool
    {
        return $this->requerido;
    }
    
    public function tieneOpciones(): bool
    {
        return in_array($this->tipo_campo, ['select', 'radio', 'checkbox']) && !empty($this->opciones);
    }
    
    public function renderInput(string $valor = '', array $attributes = []): string
    {
        $baseAttributes = [
            'name' => $this->campo,
            'id' => $this->campo,
            'class' => 'form-control',
            'placeholder' => $this->placeholder,
        ];
        
        if ($this->requerido) {
            $baseAttributes['required'] = 'required';
        }
        
        $attributes = array_merge($baseAttributes, $attributes);
        $attributesString = collect($attributes)
            ->map(fn($value, $key) => $key . '="' . htmlspecialchars($value) . '"')
            ->implode(' ');
        
        switch ($this->tipo_campo) {
            case 'textarea':
                return '<textarea ' . $attributesString . '>' . htmlspecialchars($valor) . '</textarea>';
            case 'select':
                $options = collect($this->opciones ?? [])
                    ->map(fn($label, $value) => 
                        '<option value="' . htmlspecialchars($value) . '"' . 
                        ($valor == $value ? ' selected' : '') . '>' . 
                        htmlspecialchars($label) . '</option>'
                    )->implode('');
                return '<select ' . $attributesString . '>' . $options . '</select>';
            case 'date':
                return '<input type="date" ' . $attributesString . ' value="' . htmlspecialchars($valor) . '">';
            case 'number':
                return '<input type="number" ' . $attributesString . ' value="' . htmlspecialchars($valor) . '">';
            case 'boolean':
                $checked = filter_var($valor, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '';
                return '<input type="checkbox" ' . $attributesString . ' value="1" ' . $checked . '>';
            default:
                return '<input type="text" ' . $attributesString . ' value="' . htmlspecialchars($valor) . '">';
        }
    }
}
