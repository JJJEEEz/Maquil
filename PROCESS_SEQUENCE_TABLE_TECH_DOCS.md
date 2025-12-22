## 🔧 Documentación Técnica - ProcessSequenceTable

### 📋 Resumen

Componente Vue 3 reutilizable que proporciona una tabla interactiva para gestionar secuencias de procesos con soporte para drag-and-drop, tooltips y acciones de edición/eliminación.

---

## 📦 Importación y Uso

```vue
<script setup>
import ProcessSequenceTable from '@/Components/ProcessSequenceTable.vue';
import { ref } from 'vue';

const nodos = ref([
  { id: 1, nombre: 'Proceso 1', orden: 1, tipo: 'operacion', ... },
  { id: 2, nombre: 'Proceso 2', orden: 2, tipo: 'operacion', ... }
]);

function handleEdit(nodo) {
  // Lógica de edición
}

function handleDelete(nodoId) {
  // Lógica de eliminación
}

function handleReorder(reorderedNodos) {
  // Los nodos ya están reordenados
  // Enviar cambios al backend
}
</script>

<template>
  <ProcessSequenceTable
    :nodos="nodos"
    @edit="handleEdit"
    @delete="handleDelete"
    @reorder="handleReorder"
  />
</template>
```

---

## 🎯 Props

### `nodos` (Array) - REQUERIDO

Array de objetos ProcesoNodo con la siguiente estructura:

```javascript
{
  id: 1,                              // ID único
  nombre: "Corte",                   // Nombre del proceso
  orden: 1,                          // Número de orden actual
  tipo: "operacion",                 // 'operacion' o 'inspeccion'
  cantidad_entrada: 100,             // Cantidad entrada
  cantidad_salida: 100,              // Cantidad salida
  tiempo_estimado_minutos: 15,       // Tiempo en minutos
  descripcion: "Cortar la tela",    // Descripción (opcional)
  dependencias: [                    // Array de dependencias
    { id: 2, nombre: "Costura" }
  ]
}
```

---

## 📡 Eventos Emitidos

### `@edit`
Emitido cuando el usuario haz clic en un proceso o en el botón de editar.

```vue
@edit="handleEdit"

function handleEdit(nodo) {
  // nodo contiene el objeto completo del proceso
  console.log(nodo.id, nodo.nombre);
}
```

### `@delete`
Emitido cuando el usuario hace clic en el botón de eliminar.

```vue
@delete="handleDelete"

function handleDelete(nodoId) {
  // nodoId es el ID del proceso a eliminar
  console.log(nodoId);
}
```

### `@reorder`
Emitido cuando el usuario termina de arrastrar un proceso a una nueva posición.

```vue
@reorder="handleReorder"

function handleReorder(reorderedNodos) {
  // reorderedNodos es el array completo reordenado
  // Los órdenes ya están actualizados internamente
  const ordenesActualizadas = reorderedNodos.map((nodo, index) => ({
    id: nodo.id,
    orden: index + 1
  }));
  
  // Enviar al backend
  await fetch('/api/reorder', {
    method: 'POST',
    body: JSON.stringify({ ordenes: ordenesActualizadas })
  });
}
```

---

## 🎨 Estructura Interna

### Data
```javascript
draggedIndex     // Índice de la fila siendo arrastrada
dropIndex        // Índice donde se soltará la fila
localNodos       // Copia local del array de nodos
```

### Computed
```javascript
sortedNodos      // Nodos ordenados por el campo 'orden'
```

### Methods
```javascript
dragStart(event, index)   // Inicia el drag
dragOver(event)           // Maneja drag over
drop(event, dropIdx)      // Maneja el drop y actualiza orden
dragEnd()                 // Finaliza el drag
```

---

## 🎯 Funcionalidades de Drag & Drop

### Cómo Funciona

1. **DragStart**: Se registra el índice de la fila arrastrada
2. **DragOver**: Previene el comportamiento por defecto
3. **Drop**: 
   - Intercambia los valores de `orden` entre dos nodos
   - Reactualiza el array local
   - Emite evento `@reorder` con los nodos reordenados
4. **DragEnd**: Limpia los índices

### Orden Automático
El componente usa el campo `orden` de cada nodo para determinar la posición.
Cuando arrastra, intercambia los valores de orden:

```javascript
// Antes
Nodo A: orden = 1
Nodo B: orden = 2

// Después de arrastrar A debajo de B
Nodo A: orden = 2
Nodo B: orden = 1
```

---

## 💬 Tooltips

### Implementación

Los tooltips se implementan con CSS y HTML puro (sin librerías externas):

```html
<div class="group">
  <!-- Contenido principal -->
  <div class="group-hover:block"><!-- Tooltip --></div>
</div>
```

### Contenido del Tooltip

```
- ID del proceso
- Tipo (operación/inspección)
- Cantidades de entrada y salida
- Tiempo estimado en minutos
- Descripción (si existe)
```

### Estilos

```css
/* Tooltip */
.group-hover:block    /* Muestra al hover */
position: absolute    /* Posicionamiento flotante */
z-50                  /* Por encima de todo */
w-72                  /* Ancho fijo */
bg-gray-900           /* Fondo oscuro */
rounded-lg            /* Bordes redondeados */

/* Flecha indicadora */
border-l-4           /* Crea la flecha con bordes */
border-r-4
border-t-4
```

---

## 🎨 Clases CSS y Personalización

### Clases Disponibles para Override

```css
/* Contenedor principal */
.process-sequence-container

/* Tabla */
table
thead, tbody, tr, td

/* Estados de filas */
tr.opacity-50           /* Fila siendo arrastrada */
tr.bg-gray-200          /* Fila siendo arrastrada (oscuro) */
tr.border-t-2           /* Visual de drop zone */

/* Tooltip */
.group-hover:block
.bg-gray-900            /* Fondo del tooltip */
```

### Personalizar Estilos

```vue
<style scoped>
/* Override de estilos */
:deep(table) {
  /* Tus estilos aquí */
}

:deep(tr[draggable="true"]) {
  /* Estilos para filas arrastrables */
}
</style>
```

---

## 🚀 Performance

### Optimizaciones

1. **computed `sortedNodos`**: Evita recalcular el orden en cada render
2. **Actualización local**: Los cambios se reflejan inmediatamente en la UI
3. **Un solo request al backend**: Todo se envía en una solicitud POST
4. **Sin re-renders innecesarios**: Usa refs para datos que no necesitan reactividad completa

### Límites Recomendados

- **Máximo 100 procesos**: Rendimiento óptimo
- **Máximo 50 dependencias por proceso**: Sin lag

---

## 🔒 Seguridad

### Validación Frontend

```javascript
// Valida que el draggedIndex sea diferente del dropIdx
if (draggedIndex.value !== null && draggedIndex.value !== dropIdx)
```

### Validación Backend (IMPORTANTE)

```php
// En ProcesoNodoController::reorder()
Route::post('tipos-prendas/{tipoPrenda}/procesos/reorder', ...)

// Validación requerida:
$validated = $request->validate([
    'ordenes' => 'required|array',
    'ordenes.*.id' => 'required|exists:proceso_nodos,id',
    'ordenes.*.orden' => 'required|integer|min:1',
]);

// Verificación de propiedad:
ProcesoNodo::where('id', $item['id'])
    ->where('tipo_prenda_id', $tipoPrenda->id)  // ← Crucial
    ->update(['orden' => $item['orden']]);
```

---

## 🧪 Testing

### Pruebas Unitarias Recomendadas

```javascript
describe('ProcessSequenceTable', () => {
  it('debería renderizar nodos en orden', () => {
    // Test
  });

  it('debería emitir reorder event al soltar', () => {
    // Test
  });

  it('debería mostrar tooltip al hover', () => {
    // Test
  });

  it('debería emitir delete al click en botón', () => {
    // Test
  });

  it('debería emitir edit al click en nombre', () => {
    // Test
  });
});
```

### Pruebas de Integración

1. Crear proceso
2. Arrastrar a nueva posición
3. Recargar página
4. Verificar que orden persiste

---

## 🐛 Debugging

### Ver Estado de Datos

```javascript
// En el componente
console.log('sortedNodos:', sortedNodos.value);
console.log('localNodos:', localNodos.value);
console.log('draggedIndex:', draggedIndex.value);
console.log('dropIndex:', dropIndex.value);
```

### Ver Requests

```javascript
// En DevTools → Network
// POST /tipos-prendas/{id}/procesos/reorder
// Payload: { ordenes: [...] }
```

### Ver Errores

```javascript
// F12 → Console
// Buscar errores de validación
// Buscar errores de Inertia
```

---

## 🔄 Casos de Uso

### Caso 1: Reordenar una secuencia de 5 procesos

```
Inicial:  [Corte(1), Costura(2), Planchado(3), Inspección(4), Empaque(5)]
Arrastro Planchado(3) entre Costura(2) e Inspección(4)
Resultado: [Corte(1), Costura(2), Planchado(3), Inspección(4), Empaque(5)]
           (El orden interno se ajusta correctamente)
```

### Caso 2: Con dependencias

```
Proceso A (orden 1) → depende de B
Proceso B (orden 2)

Si arrastro A después de B:
- El orden se invierte (A→2, B→1)
- Las dependencias se mantienen
- El usuario debe resolver manualmente conflictos
```

---

## 🚀 Próximas Mejoras

- [ ] Validación visual de dependencias circulares
- [ ] Soporte para multi-select y drag de múltiples filas
- [ ] Historial de cambios (undo/redo)
- [ ] Búsqueda y filtrado
- [ ] Exportación a CSV
- [ ] Importación desde CSV
- [ ] Animaciones más suaves
- [ ] Soporte para touch devices mejorado

---

## 📞 Soporte

Para issues o mejoras:
1. Revisa la consola del navegador
2. Verifica la red (Network tab)
3. Comprueba los permisos del usuario
4. Valida la estructura de datos de `nodos`

---

## 📝 Historial de Cambios

### v1.0.0 (2025-12-22)
- ✨ Implementación inicial
- 🎯 Drag & Drop funcional
- 💬 Tooltips implementados
- ⚡ Acciones de edición/eliminación
- 🎨 Soporte tema oscuro
