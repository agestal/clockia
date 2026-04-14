@include('admin.catalogos-simple._form-card', [
    'item' => $tipoRecurso,
    'isEdit' => $isEdit,
    'submitLabel' => $submitLabel,
    'backRoute' => route('admin.tipos-recurso.index'),
    'nombrePlaceholder' => 'Ejemplo: Sala',
    'nombreHelp' => 'Nombre claro para identificar el tipo de recurso.',
    'descripcionPlaceholder' => 'Descripción opcional para uso interno',
])
