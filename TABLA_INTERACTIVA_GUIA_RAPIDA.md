## 📊 Tabla Interactiva de Secuencia de Procesos - Guía Rápida

### 🎯 ¿Qué cambió?

El árbol de procesos anterior ahora es una **tabla interactiva y moderna** con funcionalidades avanzadas:

---

### ✨ Características Clave

#### 1. **Arrastrar y Soltar (Drag & Drop)**
```
┌─────────────────────────────────────┐
│ #  │ Proceso 1     │ Tipo │ ... │  │
├─────────────────────────────────────┤
│ 1  │ Corte         │ OP   │ ... │  │  ← Arrastra esta fila
├─────────────────────────────────────┤
│ 2  │ Costura       │ OP   │ ... │  │  ← Suéltala aquí
├─────────────────────────────────────┤
│ 3  │ Planchado     │ OP   │ ... │  │
└─────────────────────────────────────┘
```

**Resultado:** El orden se actualiza automáticamente (2→1, 1→2, 3→3)

---

#### 2. **Tooltips Informativos**
Pasa el mouse sobre cualquier proceso para ver detalles:

```
       ┌─────────────────────┐
       │ Corte               │
       │ ─────────────────── │
       │ ID: 1               │
       │ Tipo: operación     │
       │ Entrada: 100        │
       │ Salida: 100         │
       │ Tiempo: 15 min      │
       │ Desc: Cortar tela   │
       └─────────────────────┘
          ▲
          │
    Pasa mouse aquí
```

---

#### 3. **Columnas de la Tabla**

| Columna | Descripción |
|---------|-------------|
| **#** | Número de orden automático (1, 2, 3...) |
| **Proceso** | Nombre del proceso (clickeable para editar) |
| **Tipo** | Tipo de operación (operación/inspección) |
| **Entrada/Salida** | Cantidad entrada → cantidad salida |
| **Tiempo** | Tiempo estimado en minutos |
| **Dependencias** | Procesos de los que depende |
| **Acciones** | Botones editar/eliminar |

---

#### 4. **Acciones Disponibles**

```
CLICK EN NOMBRE         → Abre editor
┌──────┐
│EDITAR│ (lápiz)       → Abre editor
└──────┘

┌──────┐
│DELETE│ (papelera)    → Elimina (con confirmación)
└──────┘

ARRASTRA FILA          → Reordena
```

---

### 🚀 Flujo de Uso

#### Reordenar procesos:
```
1. Navega a: Admin > Diagrama de Procesos > Selecciona tipo de prenda
2. Localiza la sección "Secuencia de Procesos"
3. Arrastra cualquier fila a su nueva posición
4. ✅ Guardado automático
```

#### Editar un proceso:
```
1. Haz CLICK en el nombre del proceso
   O haz CLICK en el botón EDITAR (lápiz)
2. Se abre el modal de edición
3. Modifica los campos necesarios
4. GUARDAR
5. ✅ Cambios guardados
```

#### Eliminar un proceso:
```
1. Haz CLICK en el botón ELIMINAR (papelera)
2. Confirma la acción
3. ✅ Proceso eliminado
```

#### Ver detalles:
```
1. PASA MOUSE sobre el nombre del proceso
2. Aparece tooltip con información completa
```

---

### 🎨 Características de Diseño

✅ **Tema Oscuro/Claro**: Cambia automáticamente según preferencia del sistema  
✅ **Responsive**: Funciona en desktop, tablet y mobile  
✅ **Animaciones**: Transiciones suaves para mejor UX  
✅ **Accesibilidad**: Colores y contrastes adecuados  
✅ **Feedback Visual**: La fila arrastrada se atenúa, línea azul muestra dónde caerá

---

### 📱 Pantallas Tipo

#### Vista Normal (Luz)
```
┌───────────────────────────────────────────────────────────────────┐
│ 💡 Arrastra los procesos para cambiar el orden...                 │
├───┬──────────────┬────┬──────────┬────────┬──────────────┬────────┤
│ # │ Proceso      │ Tipo│ Ent/Sal  │ Tiempo │ Dependencias│Acciones│
├───┼──────────────┼────┼──────────┼────────┼──────────────┼────────┤
│ 1 │ Corte        │ OP  │ 100→100  │ 15 min │ -            │ ✏️ 🗑️ │
│ 2 │ Costura      │ OP  │ 100→100  │ 20 min │ Corte        │ ✏️ 🗑️ │
│ 3 │ Planchado    │ OP  │ 100→100  │ 10 min │ Costura      │ ✏️ 🗑️ │
│ 4 │ Inspección   │ INS │ 100→95   │ 5 min  │ Planchado    │ ✏️ 🗑️ │
└───┴──────────────┴────┴──────────┴────────┴──────────────┴────────┘
```

#### Vista Oscura
```
(Mismo diseño pero con fondo gris oscuro y texto claro)
```

---

### ⚙️ Tecnología Detrás

- **Frontend**: Vue 3 (Composition API)
- **Arrastrar**: HTML5 Drag & Drop API
- **Estilos**: Tailwind CSS
- **Backend**: Laravel (método `reorder` en controlador)
- **Base de Datos**: Actualización atómica de campo `orden`

---

### 🔍 Validaciones

✅ El ID debe existir  
✅ El proceso debe pertenecer a la TipoPrenda correcta  
✅ El número de orden es válido  
✅ Sin duplicados de ID  
✅ Transacciones atómicas (todo se guarda o nada)

---

### 💡 Tips

- **Rápido**: Sin recargas de página
- **Eficiente**: Un solo request al backend por reordenación
- **Seguro**: Validación en servidor
- **Intuitivo**: Interfaz similar a aplicaciones modernas
- **Accesible**: Funciona sin JavaScript habilitado parcialmente

---

### 🆘 Solución de Problemas

| Problema | Solución |
|----------|----------|
| Los cambios no se guardan | Comprueba que hayas soltado la fila en su nueva posición |
| No puedo arrastrar | Asegúrate de hacer clic y mantener presionado en la fila |
| El tooltip no aparece | Pasa el mouse directamente sobre el nombre del proceso |
| No puedo editar | Haz clic en el botón lápiz en la columna de acciones |

---

### 📞 Soporte

Si encuentras algún problema:
1. Recarga la página (F5)
2. Comprueba la consola del navegador (F12)
3. Verifica que tengas permisos de edición de procesos

¡Disfruta de tu nueva tabla interactiva! 🎉
