# ⚡ GUÍA RÁPIDA DE IMPLEMENTACIÓN

## 📋 ANTES DE EMPEZAR

1. **Copia de seguridad de la BD**
   ```bash
   # Opcional pero recomendado
   ```

2. **Verifica que tienes los composer packages necesarios**
   ```bash
   composer update
   ```

---

## 🚀 PASOS DE IMPLEMENTACIÓN

### 1. Ejecutar Migraciones
```bash
php artisan migrate
```

**Esto crea:**
- `tipos_prendas`
- `proceso_nodos`
- `proceso_nodo_dependencies`
- `lote_proceso_progresos`
- `lote_proceso_progreso_horas`
- `operador_asignacions`
- Modifica `ordenes` (agrega `tipo_prenda_id`)
- Modifica `lotes` (agreg campos de estado, mermas, etc)

### 2. Ejecutar Seeders
```bash
php artisan db:seed --class=ProcessesPermissionsSeeder
```

**Esto agrega:**
- Permisos para: `tipos_prendas.*`, `procesos.*`
- Asigna permisos a roles existentes (admin, operador, supervisor)

### 3. (Opcional) Configurar Scheduler
En `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generar lotes diarios a las 00:00
    $schedule->command('lotes:generate-daily')->dailyAt('00:00');
}
```

Luego ejecutar:
```bash
php artisan schedule:work  # En desarrollo
```

O configurar en cron:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎮 PRIMEROS PASOS EN LA APP

### A. Crear un Tipo de Prenda

1. Login como Admin
2. Ir a: **Admin** → **Tipos de Prendas**
3. Click: **Crear Tipo de Prenda**
4. Llenar:
   - **Nombre:** "Polo"
   - **Descripción:** (opcional)
5. Guardar

### B. Crear Diagrama de Procesos

1. Desde **Tipos de Prendas**, haz click en **Ver Procesos (0)**
2. Click: **Crear Proceso**
3. Crear los siguientes procesos:

#### Proceso 1: Corte Puño
- Nombre: "Corte Puño"
- Tipo: "Operación"
- Orden: 0
- Entrada: 1, Salida: 1
- Tiempo: 10 min
- Dependencias: (ninguna)

#### Proceso 2: Corte Manga
- Nombre: "Corte Manga"
- Tipo: "Operación"
- Orden: 1
- Entrada: 1, Salida: 1
- Tiempo: 10 min
- Dependencias: (ninguna)

#### Proceso 3: Corte Frente
- Similar...

#### Proceso 4: Corte Trasero
- Similar...

#### Proceso 5: Costura M+F
- Nombre: "Costura Manga Frente"
- Tipo: "Operación"
- Orden: 4
- Entrada: 2, Salida: 1
- Tiempo: 15 min
- **Dependencias:** Corte Manga + Corte Frente

#### Proceso 6: Costura Torso
- Nombre: "Costura Torso"
- Tipo: "Operación"
- Orden: 5
- Entrada: 2, Salida: 1
- Tiempo: 15 min
- **Dependencias:** Costura M+F + Corte Trasero

#### Proceso 7: Acabados
- Nombre: "Acabados"
- Tipo: "Operación"
- Orden: 6
- Entrada: 2, Salida: 1
- Tiempo: 20 min
- **Dependencias:** Costura Torso + Corte Puño

### C. Crear una Orden

1. Ir a: **Admin** → **Órdenes** → **Crear**
2. Llenar:
   - **Nombre:** "100 Polos"
   - **Descripción:** "Primera orden de prueba"
   - **Cliente:** "Cliente Test"
   - **Tipo de Prenda:** "Polo" (lo que creaste)
   - **Cantidad Objetivo:** 100
   - **Fecha Objetivo:** 2025-12-25
3. Guardar

### D. Generar Lotes

Ejecutar comando:
```bash
php artisan lotes:generate-daily
```

Esto crea un lote para cada día hasta la fecha objetivo.

### E. Inicializar Procesos

Para que el operador pueda registrar progreso, necesitamos inicializar los procesos de cada lote:

```bash
php artisan lotes:initialize-processes 1
```

(Reemplaza 1 por el ID del lote que quieras inicializar)

---

## 👨‍💼 FLUJO DEL OPERADOR

### 1. Ver Dashboard
1. Login como Operador
2. Ir a: **Operador** → **Dashboard** → Seleccionar un lote
3. Ver 7 tarjetas de procesos con barras de progreso

### 2. Registrar Progreso

**Opción A: Modal (recomendado)**
- Click en una tarjeta de proceso
- Se abre un modal
- Ingresar:
  - Cantidad completada (100)
  - Mermas (0)
  - Excedentes (0)
  - Notas (opcional)
- Click: Registrar

**Opción B: Formulario Detallado**
- Ir a: **Operador** → **Progreso** → Lote
- Llenar formulario para cada proceso
- Guardar

### 3. Actualizar en Tiempo Real
- Automático cada 30 segundos
- O click en botón "Actualizar"

---

## 📊 VER PROGRESO (Admin)

1. Ir a: **Admin** → **Lotes**
2. Haz click en "Ver Detalle" de un lote
3. Ver:
   - Resumen (prendas terminadas, mermas)
   - Progreso por cada proceso
   - Quién registró y cuándo

---

## 🔗 RUTAS PRINCIPALES

```
Admin Panel:
/admin/tipos-prendas
/admin/tipos-prendas/{id}/procesos
/admin/lotes
/admin/lotes/{id}

Operador:
/operador/lotes/{lote}/dashboard
/operador/lotes/{lote}/progreso
/operador/lotes/{lote}/progreso/api (JSON)
```

---

## 🐛 TROUBLESHOOTING

### Error: "Tabla no existe"
```bash
php artisan migrate
```

### Error: "Permisos denegados"
```bash
php artisan db:seed --class=ProcessesPermissionsSeeder
```

### Lotes no aparecen
```bash
php artisan lotes:generate-daily
```

### No hay procesos en el lote
```bash
php artisan lotes:initialize-processes {lote_id}
```

### "Tipo de prenda no encontrado"
Verifica que la orden tenga `tipo_prenda_id` asignado

---

## 📈 EJEMPLO COMPLETO (5 minutos)

```bash
# 1. Migrar
php artisan migrate

# 2. Seeder permisos
php artisan db:seed --class=ProcessesPermissionsSeeder

# 3. Ir a Admin panel y crear:
#    - Tipo Prenda: "Polo"
#    - 7 Procesos (como arriba)
#    - Orden: "100 Polos"

# 4. Generar lotes
php artisan lotes:generate-daily

# 5. Inicializar procesos del lote
php artisan lotes:initialize-processes 1

# 6. Login como operador y registrar progreso
```

¡Listo! ✅

---

## 📚 DOCUMENTACIÓN COMPLETA

Ver: `IMPLEMENTATION_GUIDE.md` y `CHANGES_SUMMARY.md`
