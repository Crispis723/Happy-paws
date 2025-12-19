# 📚 MÓDULO PRODUCTOS - EXPLICACIÓN COMPLETA

## 🏗️ ESTRUCTURA DEL MÓDULO

El módulo Productos funciona como un ciclo completo:

```
USUARIO ABRE LA PÁGINA
    ↓
index() → MOSTRAR LISTADO
    ├─ index.blade.php (tabla con todos los productos)
    │
    ├─ Si click "Nuevo"
    │   ↓
    │   create() → MOSTRAR FORMULARIO
    │   └─ create.blade.php (formulario vacío)
    │       ↓
    │       Si submit
    │       ↓
    │       store() → GUARDAR EN BD
    │
    ├─ Si click "Ver"
    │   ↓
    │   show() → MOSTRAR DETALLE
    │   └─ show.blade.php (datos del producto)
    │
    ├─ Si click "Editar"
    │   ↓
    │   edit() → MOSTRAR FORMULARIO CON DATOS
    │   └─ edit.blade.php (formulario con datos precargados)
    │       ↓
    │       Si submit
    │       ↓
    │       update() → ACTUALIZAR EN BD
    │
    └─ Si click "Eliminar"
        ↓
        destroy() → ELIMINAR DE BD

```

---

## 📂 ARCHIVOS DEL MÓDULO Y UBICACIONES

```
c:\Proyecto\sistema\
│
├── app/Models/
│   └── Producto.php                    ← MODELO (ya existe)
│
├── app/Http/Controllers/
│   └── ProductoController.php          ← CONTROLADOR (ya existe, mejorado)
│
└── resources/views/productos/
    ├── index.blade.php                 ← LISTADO (actualizado)
    ├── create.blade.php                ← CREAR (nuevo)
    ├── edit.blade.php                  ← EDITAR (nuevo)
    └── show.blade.php                  ← DETALLE (nuevo)
```

---

## 🔄 FLUJO DE DATOS DETALLADO

### 1️⃣ **LISTAR PRODUCTOS (GET /productos)**

**¿QUÉ PASA?**
```
Usuario abre página /productos
    ↓
Controller: index()
    - Obtiene todos los productos paginados
    - Carga relaciones (unidad, afectacionTipo)
    - Pasa a vista 'productos.index'
    ↓
Vista: index.blade.php
    - Itera sobre $productos
    - Muestra tabla con:
      * Código
      * Nombre
      * Unidad (con badge)
      * Stock (con color según cantidad)
      * Precio
      * Botones (Ver, Editar, Eliminar)
    - Muestra paginación
```

**CÓDIGO DEL CONTROLADOR:**
```php
public function index(Request $request)
{
    // with(['unidad', 'afectacionTipo']) = Cargar relaciones (evita N+1)
    // orderBy('id', 'desc') = Ordenar más nuevo primero
    // paginate(10) = 10 productos por página
    $productos = Producto::with(['unidad', 'afectacionTipo'])
                          ->orderBy('id', 'desc')
                          ->paginate(10);
    
    return view('productos.index', compact('productos'));
}
```

**¿QUÉ SIGNIFICA CADA PARTE?**
- `with()` = Eager loading (cargar relaciones con la query, NO después)
- `orderBy()` = Ordenar resultados
- `paginate()` = Dividir en páginas

**VISTA:**
```blade
@foreach($productos as $producto)
    <tr>
        <td>{{ $producto->codigo }}</td>
        <td>{{ $producto->nombre }}</td>
        <td>{{ $producto->unidad->descripcion }}</td>
        <!-- Más columnas... -->
    </tr>
@endforeach
{{ $productos->links() }}  {{-- Paginación --}}
```

---

### 2️⃣ **CREAR PRODUCTO (GET /productos/create)**

**¿QUÉ PASA?**
```
Usuario click "Nuevo Producto"
    ↓
Controller: create()
    - Obtiene todas las unidades
    - Obtiene todos los tipos de afectación
    - Pasa ambas listas a la vista
    ↓
Vista: create.blade.php
    - Muestra formulario vacío
    - Los dropdowns tienen las opciones
```

**CÓDIGO DEL CONTROLADOR:**
```php
public function create()
{
    // all() = Obtener TODOS los registros
    $unidades = \App\Models\Unidad::all();
    $afectacionTipos = \App\Models\AfectacionTipo::all();
    
    // compact() = Pasar variables a la vista
    return view('productos.create', compact('unidades', 'afectacionTipos'));
}
```

**¿QUÉ SIGNIFICA?**
- `::all()` = SQL: `SELECT * FROM unidades`
- `compact('var1', 'var2')` = Enviar variables a vista como array

**VISTA:**
```blade
<select name="unidad_codigo">
    @foreach($unidades as $unidad)
        <option value="{{ $unidad->codigo }}">
            {{ $unidad->codigo }} - {{ $unidad->descripcion }}
        </option>
    @endforeach
</select>
```

---

### 3️⃣ **GUARDAR PRODUCTO (POST /productos)**

**¿QUÉ PASA?**
```
Usuario llena formulario y click "Crear"
    ↓
Formulario hace POST a /productos
    ↓
Controller: store()
    1. VALIDAR datos
    2. Si hay imagen: guardarla
    3. CREAR registro en BD
    4. REDIRIGIR al listado
```

**CÓDIGO DETALLADO:**

```php
public function store(Request $request)
{
    // PASO 1: VALIDAR
    // validateProducto() = Método que define reglas
    $data = $this->validateProducto($request);

    // PASO 2: MANEJAR IMAGEN
    if ($request->hasFile('imagen')) {
        // hasFile() = ¿Hay un file llamado "imagen" en la solicitud?
        
        $file = $request->file('imagen');
        
        // Crear nombre único: timestamp + aleatorio + extensión
        // Ej: 1703009987_ABC123XYZ.jpg
        $filename = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . 
                   $file->getClientOriginalExtension();
        
        // Mover archivo a carpeta pública
        $file->move(public_path('uploads/productos/'), $filename);
        
        // Agregar nombre a datos a guardar
        $data['imagen'] = $filename;
    }

    // PASO 3: CREAR EN BD
    // create() = INSERT en tabla productos
    Producto::create($data);

    // PASO 4: REDIRIGIR
    return redirect()->route('productos.index')
                    ->with('success', 'Producto creado exitosamente.');
}
```

**¿QUÉ SIGNIFICA CADA PARTE?**

```
$request->hasFile('imagen')
    ↓
¿Existe un archivo en $_FILES con name="imagen"?

$request->file('imagen')
    ↓
Obtener el objeto UploadedFile

time()
    ↓
Timestamp actual (Ej: 1703009987)

Str::random(10)
    ↓
10 caracteres aleatorios (Ej: ABc3Xyz5Qw)

$file->getClientOriginalExtension()
    ↓
Extensión del archivo (jpg, png, etc)

$file->move(destination, filename)
    ↓
Mover archivo a destino con nombre

public_path()
    ↓
Ruta absoluta a carpeta /public del servidor

Producto::create($data)
    ↓
INSERT INTO productos (campos) VALUES (valores)
    ↓
Solo funciona si los campos están en $fillable
```

**FLUJO DE LA IMAGEN:**
```
Usuario selecciona archivo: documento.jpg
    ↓
El archivo está en: $request->file('imagen')
    ↓
Crear nombre único: 1703009987_ABC123.jpg
    ↓
Mover a: /public/uploads/productos/1703009987_ABC123.jpg
    ↓
Guardar nombre en BD: $data['imagen'] = '1703009987_ABC123.jpg'
    ↓
En vista: <img src="{{ asset('uploads/productos/' . $producto->imagen) }}">
    ↓
URL real: /public/uploads/productos/1703009987_ABC123.jpg
```

---

### 4️⃣ **VER DETALLE (GET /productos/{id})**

**¿QUÉ PASA?**
```
Usuario click "Ver"
    ↓
Controller: show(1)
    - Busca producto con ID=1
    - Si no existe → ERROR 404
    - Si existe → Pasa a vista show.blade.php
    ↓
Vista: show.blade.php
    - Muestra todos los datos del producto
    - Imagen (si existe)
    - Botones: Volver, Editar, Eliminar
```

**CÓDIGO:**
```php
public function show($id)
{
    try {
        // findOrFail() = Buscar por ID
        // Si NO existe → lanza excepción (error)
        // with() = cargar relaciones
        $producto = Producto::with(['unidad', 'afectacionTipo'])
                            ->findOrFail($id);
        
        return view('productos.show', compact('producto'));
    } catch (\Exception $e) {
        // Si hay error, redirigir al listado
        return redirect()->route('productos.index')
                        ->with('error', 'Producto no encontrado.');
    }
}
```

---

### 5️⃣ **EDITAR PRODUCTO (GET /productos/{id}/edit)**

**¿QUÉ PASA?**
```
Usuario click "Editar"
    ↓
Controller: edit(1)
    - Busca producto con ID=1
    - Obtiene unidades para dropdown
    - Obtiene afectación tipos para dropdown
    - Pasa TODO a vista edit.blade.php
    ↓
Vista: edit.blade.php
    - Muestra formulario CON DATOS precargados
    - Los dropdowns muestran valor actual como "selected"
    - Muestra imagen actual
```

**CÓDIGO:**
```php
public function edit($id)
{
    // Obtener producto
    $producto = Producto::findOrFail($id);
    
    // Obtener opciones para dropdowns
    $unidades = \App\Models\Unidad::all();
    $afectacionTipos = \App\Models\AfectacionTipo::all();
    
    // Enviar a vista
    return view('productos.edit', 
               compact('producto', 'unidades', 'afectacionTipos'));
}
```

**VISTA (ejemplo):**
```blade
<input name="nombre" value="{{ old('nombre', $producto->nombre) }}">
<!-- old() = Si hay error de validación, usar valor enviado
     Si no, usar valor actual de BD -->

<select name="unidad_codigo">
    @foreach($unidades as $unidad)
        <option value="{{ $unidad->codigo }}" 
                {{ old('unidad_codigo', $producto->unidad_codigo) == $unidad->codigo ? 'selected' : '' }}>
            {{ $unidad->descripcion }}
        </option>
    @endforeach
</select>

<img src="{{ asset('uploads/productos/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
```

---

### 6️⃣ **ACTUALIZAR PRODUCTO (PUT /productos/{id})**

**¿QUÉ PASA?**
```
Usuario modifica datos y click "Guardar Cambios"
    ↓
Formulario hace PUT a /productos/1
    ↓
Controller: update(1)
    1. VALIDAR datos nuevos
    2. Si hay imagen nueva: guardarla
    3. Si hay imagen nueva: ELIMINAR la vieja
    4. ACTUALIZAR registro en BD
    5. REDIRIGIR al detalle
```

**CÓDIGO:**
```php
public function update(Request $request, $id)
{
    // PASO 1: VALIDAR (el $id ignora unique para este registro)
    $data = $this->validateProducto($request, $id);
    
    // PASO 2: OBTENER producto actual
    $producto = Producto::findOrFail($id);
    
    // PASO 3: MANEJAR imagen nueva
    if ($request->hasFile('imagen')) {
        $file = $request->file('imagen');
        $filename = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . 
                   $file->getClientOriginalExtension();
        $file->move(public_path('uploads/productos/'), $filename);
        $data['imagen'] = $filename;
        
        // ELIMINAR imagen vieja
        $oldImage = 'uploads/productos/' . $producto->imagen;
        if (file_exists($oldImage)) {
            @unlink($oldImage);  // @ = ignorar si hay error
        }
    }
    
    // PASO 4: ACTUALIZAR en BD
    // update() = UPDATE en SQL
    $producto->update($data);
    
    // PASO 5: REDIRIGIR
    return redirect()->route('productos.show', $producto->id)
                    ->with('success', 'Producto actualizado exitosamente.');
}
```

**¿POR QUÉ PASAR $id A validateProducto()?**
```
Sin $id:
  unique('productos') = Código debe ser único SIEMPRE
  Si el código es "PRD-001" y ya existe en BD → ERROR
  Incluso si es el MISMO producto → ERROR

Con $id (ignore($id)):
  unique('productos')->ignore($id)
  Si el código es "PRD-001" y ya existe en BD:
    - Si es el MISMO producto (ID=1) → OK
    - Si es OTRO producto → ERROR
```

---

### 7️⃣ **ELIMINAR PRODUCTO (DELETE /productos/{id})**

**¿QUÉ PASA?**
```
Usuario click "Eliminar"
    ↓
Se pide confirmación
    ↓
Controller: destroy(1)
    1. Buscar producto
    2. Eliminar imagen (si existe)
    3. ELIMINAR registro de BD
    4. REDIRIGIR al listado
```

**CÓDIGO:**
```php
public function destroy($id)
{
    try {
        // Obtener producto
        $producto = Producto::findOrFail($id);
        
        // Eliminar imagen
        if ($producto->imagen) {
            $imagePath = 'uploads/productos/' . $producto->imagen;
            if (file_exists($imagePath)) {
                @unlink($imagePath);  // Eliminar archivo
            }
        }

        // Eliminar registro de BD
        // delete() = DELETE en SQL
        $producto->delete();

        return redirect()->route('productos.index')
                        ->with('success', 'Producto eliminado exitosamente.');
    } catch (\Exception $e) {
        return redirect()->route('productos.index')
                        ->with('error', 'Error al eliminar el producto.');
    }
}
```

---

## 🔐 VALIDACIÓN DE DATOS

```php
protected function validateProducto(Request $request, $id = null)
{
    return $request->validate([
        
        // UNIDAD - Debe existir en BD
        'unidad_codigo' => 'required|exists:unidades,codigo',
        // required = No puede estar vacío
        // exists:tabla,columna = Verificar que existe en otra tabla
        
        // AFECTACIÓN - Debe existir en BD
        'afectacion_tipo_codigo' => 'required|exists:afectacion_tipos,codigo',
        
        // CÓDIGO - Único por producto
        'codigo' => [
            'required',
            'string',
            'max:50',
            \Illuminate\Validation\Rule::unique('productos')
                ->ignore($id),  // Ignorar el MISMO producto en edición
        ],
        
        // NOMBRE - Requerido y texto
        'nombre' => 'required|string|max:100',
        // max:100 = Máximo 100 caracteres
        
        // DESCRIPCIÓN - Opcional
        'descripcion' => 'nullable|string|max:500',
        // nullable = Puede estar vacío
        
        // IMAGEN - Opcional, validada
        'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        // image = Debe ser imagen válida
        // mimes:jpg,jpeg,png = Solo estos formatos
        // max:2048 = Máximo 2MB (en KB)
        
        // PRECIO - Número positivo
        'precio_unitario' => 'required|numeric|min:0|max:999999.99',
        // numeric = Número (puede tener decimales)
        // min:0 = No puede ser negativo
        // max = Cantidad máxima
        
        // STOCK - Número no negativo
        'stock' => 'required|numeric|min:0',
    ]);
}
```

---

## 📋 RESUMEN DE MÉTODOS PRINCIPALES

| Método | Ruta | Tipo | ¿Qué hace? |
|--------|------|------|-----------|
| index | /productos | GET | Listar productos |
| create | /productos/create | GET | Mostrar formulario crear |
| store | /productos | POST | Guardar nuevo |
| show | /productos/{id} | GET | Ver detalle |
| edit | /productos/{id}/edit | GET | Mostrar formulario editar |
| update | /productos/{id} | PUT | Actualizar |
| destroy | /productos/{id} | DELETE | Eliminar |

---

## 🔗 RELACIONES EN EL MODELO

```php
// UN Producto pertenece a UNA Unidad
public function unidad()
{
    return $this->belongsTo(Unidad::class, 'unidad_codigo', 'codigo');
    //                                     ↑ columna en productos
    //                                                         ↑ columna en unidades
}

// UN Producto pertenece a UN Tipo de Afectación
public function afectacionTipo()
{
    return $this->belongsTo(AfectacionTipo::class, 'afectacion_tipo_codigo', 'codigo');
}
```

**¿QUÉ SIGNIFICA?**
- `belongsTo()` = "Este pertenece a uno"
- El 2º parámetro = columna en ESTA tabla
- El 3º parámetro = columna en la OTRA tabla

**CÓMO USAR EN CONTROLADOR:**
```php
$producto = Producto::find(1);

// Acceder a la unidad
$producto->unidad->descripcion;  // "Kilogramo"

// Acceder al tipo de afectación
$producto->afectacionTipo->descripcion;  // "IGV 18%"

// Con eager loading (mejor rendimiento)
$productos = Producto::with(['unidad', 'afectacionTipo'])->get();
```

---

## 🚀 FLUJO COMPLETO DE EJEMPLO

**USUARIO QUIERE CREAR UN PRODUCTO NUEVO**

```
1. Usuario abre: http://miapp.com/productos
   → HTTP GET /productos
   → Controller: index()
   → Vista: index.blade.php (muestra lista)

2. Usuario click "Nuevo Producto"
   → HTTP GET /productos/create
   → Controller: create()
   → Vista: create.blade.php (formulario vacío)

3. Usuario llena:
   - Código: PRD-001
   - Nombre: Producto A
   - Precio: 50.00
   - Stock: 100
   - Imagen: imagen.jpg
   
4. Usuario click "Crear"
   → HTTP POST /productos
   → Datos enviados en body:
      {
        "codigo": "PRD-001",
        "nombre": "Producto A",
        "precio_unitario": "50.00",
        "stock": "100",
        "imagen": <file>
      }

5. Controller: store()
   - validate() → ¿Datos correctos? SI
   - Guardar imagen en /public/uploads/productos/
   - Producto::create() → INSERT en BD
   → Redirige a /productos con mensaje "Éxito"

6. Vista: index.blade.php
   - Se recarga
   - Muestra nuevo producto en tabla
```

---

Este es el flujo completo del módulo Productos. ¿Entienden todas las partes?
