# Flujo del Operador - Guía Rápida

## Descripción General

El flujo del operador permite que los usuarios con rol "operador" sean asignados a procesos específicos y registren el progreso de prendas en tiempo real. Un operador solo ve y puede trabajar en los procesos que le fueron asignados.

## Componentes del Sistema

### 1. **Asignación de Operadores (Admin/Supervisor)**

#### Ruta: `/admin/progreso/{progreso}/asignar-operador`

El administrador o supervisor puede asignar uno o más operadores a cada proceso:

1. Ir a una orden → Ver lotes → Seleccionar un lote
2. En la vista del lote, aparecen todos los procesos
3. Hacer clic en el botón **"Asignar Operador"** en cada proceso
4. Seleccionar el operador deseado de la lista
5. Hacer clic en **"Asignar Operador"**

#### Modelos Involucrados:
- `OperadorAsignacion`: Tabla intermedia que vincula usuarios (operadores) con procesos nodo

```php
OperadorAsignacion::create([
    'user_id' => $operadorId,
    'proceso_nodo_id' => $procesoNodoId,
]);
```

### 2. **Dashboard del Operador**

#### Ruta: `/operador/dashboard`

El operador ve todos sus procesos asignados en formato de tarjetas:

Información mostrada por proceso:
- Orden asociada
- Tipo de prenda
- Proceso actual
- Cantidad objetivo
- Progreso actual (cantidad completada / cantidad objetivo)
- Porcentaje de progreso con barra visual
- Estado del proceso (pendiente, en_progreso, completado)

**Características:**
- Las tarjetas son clickeables
- Grid responsivo (1 columna en móvil, 2 en tablet, 3 en desktop)
- Solo muestra procesos no completados
- Icono cuando no hay asignaciones

### 3. **Vista de Detalle del Proceso**

#### Ruta: `/operador/proceso/{progreso}`

Cuando el operador hace clic en un proceso, ve:

**Panel de información:**
- Orden
- Tipo de prenda
- Cliente
- Cantidad objetivo
- Progreso actual (visual y numérico)
- Mermas registradas
- Excedentes registrados
- Porcentaje completado
- Notas previas (si existen)

**Formulario de registro:**
- Campo: Prendas completadas (número)
- Campo: Mermas (número, opcional)
- Campo: Excedentes (número, opcional)
- Campo: Notas (texto, opcional)
- Botón: "Registrar Progreso"
- Botón: "Marcar como Completado" (aparece cuando cantidad completada >= cantidad objetivo)

### 4. **Modelos de Base de Datos**

#### OperadorAsignacion
```php
$table->id();
$table->foreignId('user_id')->constrained('users')->onDelete('cascade');
$table->foreignId('proceso_nodo_id')->constrained('proceso_nodos')->onDelete('cascade');
$table->unique(['user_id', 'proceso_nodo_id']); // Un operador por proceso
```

#### LoteProcesoProgreso (existente)
```php
$table->id();
$table->foreignId('lote_id');
$table->foreignId('proceso_nodo_id');
$table->integer('cantidad_objetivo');
$table->integer('cantidad_completada');
$table->integer('cantidad_merma');
$table->integer('cantidad_excedente');
$table->string('estado'); // pendiente, en_progreso, completado
$table->foreignId('registrado_por')->nullable();
$table->text('notas')->nullable();
$table->timestamps();
```

## Flujo de Trabajo Completo

```
1. ADMIN/SUPERVISOR
   ├─ Crea Orden
   ├─ Asigna Tipo de Prenda
   ├─ Crea Lote
   ├─ Inicializa Procesos
   └─ Asigna Operadores a cada Proceso

2. OPERADOR
   ├─ Accede a /operador/dashboard
   ├─ Ve sus procesos asignados
   ├─ Hace clic en un proceso
   ├─ Ve el detalle del proceso
   ├─ Registra prendas completadas
   ├─ Opcionalmente registra mermas y excedentes
   ├─ Repite mientras el proceso no esté completo
   └─ Marca como completado cuando alcanza cantidad objetivo

3. SUPERVISOR/ADMIN
   ├─ Ve el progreso en el lote
   ├─ Valida que todos los procesos estén completados
   └─ Cierra el lote
```

## Permisos Requeridos

El rol "operador" debe tener estos permisos:
- `operador.dashboard` - Acceder al dashboard
- `operador.registrar` - Registrar progreso en procesos

## APIs/Endpoints

### GET `/operador/dashboard`
Obtiene todos los procesos asignados al usuario autenticado

Respuesta:
```json
{
  "asignaciones": [...],
  "progresos": [...]
}
```

### GET `/operador/proceso/{progreso}`
Obtiene el detalle de un proceso específico

### POST `/operador/progreso/{progreso}/registrar`
Registra el progreso de prendas

Payload:
```json
{
  "cantidad_completada": 50,
  "cantidad_merma": 2,
  "cantidad_excedente": 1,
  "notas": "Se encontró defecto en la máquina"
}
```

### POST `/operador/progreso/{progreso}/completar`
Marca un proceso como completado

## Archivos Creados/Modificados

### Controladores:
- `OperadorController.php` - Lógica del dashboard y registro de operador
- `OperadorAsignacionController.php` - Asignación de operadores a procesos

### Vistas:
- `resources/js/Pages/Operador/Dashboard.vue` - Dashboard del operador
- `resources/js/Pages/Operador/ProcesoDetalle.vue` - Detalle y registro de proceso
- `resources/js/Pages/Admin/OperadorAsignacion/Edit.vue` - Asignación de operadores

### Modelos:
- `OperadorAsignacion.php` - Relación user-proceso

### Rutas:
- Agregadas rutas en `/operador/*` para el operador
- Agregadas rutas en `/admin/progreso/*/asignar-operador` para admin/supervisor

### Seeder:
- `RolesAndUsersSeeder.php` - Actualizado con nuevos permisos

## Consideraciones de Seguridad

- Los operadores solo pueden ver/registrar en procesos que les fueron asignados
- Se valida en el controlador que el operador tiene asignación antes de permitir acciones
- Los datos de registros se asocian al usuario autenticado (`registrado_por`)
- Única asignación por operador por proceso (constraint en BD)

## Mejoras Futuras

- Historial de registros por hora
- Alertas cuando se alcanza cierto porcentaje de merma
- Reportes del operador
- Notificaciones cuando hay nuevo proceso asignado
- Tiempos de procesamiento
