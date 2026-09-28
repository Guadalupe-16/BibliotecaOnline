# Análisis de seguridad con Snyk — BibliotecaOnline

> Issue: #170 · Fecha: 2026-09-28 · Ejecutado por: Javier Antonio Romo Bernal
> Commit analizado: `develop` @ `a18fd09` · Snyk CLI 1.1307.4 · Organización Snyk: `eljavierb0`

Estados: **IMPLEMENTADO** (existe), **VALIDADO** (se ejecutó con el resultado indicado),
**PLANEADO** (propuesta).

---

## 1. Por qué Snyk

SonarQube (#153–#155) analiza la **calidad del código propio**, y su Community Build no busca
inyecciones ni revisa dependencias. Snyk cubre lo que falta:

| Qué analiza | Producto de Snyk | ¿Lo cubre SonarQube Community? |
|---|---|---|
| Vulnerabilidades conocidas (CVE) en dependencias PHP y JS | Snyk Open Source | No |
| Vulnerabilidades del sistema operativo de la imagen Docker | Snyk Container | No |
| Código propio (SAST) | Snyk Code | Parcial (sin *taint analysis*) |

Los dos se complementan: SonarQube para mantenibilidad y confiabilidad, Snyk para la cadena de
suministro (dependencias e imagen).

## 2. Instalación y configuración — VALIDADO

```bash
brew install snyk-cli        # macOS; en otros sistemas: npm install -g snyk
snyk auth                    # abre el navegador y guarda la sesión en ~/.config (fuera del repo)
snyk whoami                  # verifica la cuenta
```

- Cuenta gratuita en https://app.snyk.io, con inicio de sesión mediante GitHub.
- **Snyk Code viene desactivado:** se activa en *Settings → Snyk Code → Enable Snyk Code*. Sin ese
  paso, `snyk code test` responde `SNYK-CODE-0005`.
- El repositorio **no contiene tokens ni configuración con secretos**. La sesión local vive en la
  configuración del usuario y el token de CI se guarda como secret (§7).

## 3. Comandos ejecutados

| Análisis | Comando | Nota |
|---|---|---|
| Dependencias PHP | `snyk test --file=composer.lock` | No se usa `--all-projects`, porque también entra a `vendor/` y analiza los `composer.lock` de librerías de terceros |
| Dependencias JS | `snyk test --file=package-lock.json --dev` | `--dev` es necesario: casi todas las dependencias JS del proyecto son `devDependencies` (sin él solo se analizan 3 paquetes) |
| Código fuente | `snyk code test` | 125 archivos PHP, 52 HTML/Blade y 20 JS analizados |
| Imagen Docker (#156) | `docker save bibliotecaonline:<v> -o img.tar` y `snyk container test docker-archive:img.tar --file=Dockerfile --exclude-app-vulns` | Se exporta a archivo porque Snyk no encuentra el socket de Docker de Colima |
| Dashboard | `snyk monitor` / `snyk container monitor` / `snyk code test --report` | Publica cada análisis como proyecto en app.snyk.io |

Resultados completos (vulnerabilidades únicas, CVE y versión que las corrige):
[`snyk/resultados-2026-09-28.json`](snyk/resultados-2026-09-28.json).

## 4. Resultados — VALIDADO

| Análisis | Analizado | Vulnerabilidades únicas | Critical | High | Medium | Low |
|---|---|---|---|---|---|---|
| Dependencias PHP | 99 paquetes | **33** | 1 | 12 | 17 | 3 |
| Dependencias JS | 329 paquetes | **87** | 11 | 39 | 35 | 2 |
| Imagen Docker (`php:8.2-apache`, Debian) | 185 paquetes del SO | **206** | 0 | 11 | 3 | 192 |
| Código (Snyk Code) | 198 archivos | **13** | — | 0 | 2 | 11 |

### 4.1 Dependencias PHP

Todas llegan como dependencias transitivas de `laravel/framework@12.52.0` (la versión fijada en
`composer.lock`).

| Paquete | Vulns | Más grave | Riesgo en BibliotecaOnline |
|---|---|---|---|
| `symfony/mailer@7.4.4` | 1 | **Critical**: inyección de argumentos (CVE-2026-45068) | Bajo hoy: el correo usa `MAIL_MAILER=log`/`array`. Sube a alto si se configura el transporte `sendmail` |
| `guzzlehttp/guzzle@7.10.0` + `guzzlehttp/psr7@2.8.0` | 13 (9 + 4) | High: exposición de datos sensibles y destino incorrecto | **Relevante**: `OpenLibraryService` hace peticiones HTTP a una API externa con este cliente |
| `league/commonmark@2.8.0` | 12 | High: complejidad algorítmica (DoS) y XSS | Bajo: la app no renderiza Markdown de usuarios |
| `symfony/http-kernel@7.4.5` | 1 | High: autorización incorrecta (CVE-2026-45075) | Relevante: es el núcleo HTTP de Laravel |
| `symfony/routing@7.4.4` | 1 | High: expresión regular incorrecta | Relevante: enrutamiento de todas las peticiones |
| `laravel/framework@12.52.0` | 2 | Medium: inyección CRLF | Relevante |
| `symfony/mime`, `symfony/polyfill-intl-idn` | 3 | Medium | Bajo |

### 4.2 Dependencias JS

Hay que separar **lo que llega al navegador** de lo que solo se usa para compilar y probar:

| Grupo | Paquetes | Vulns | Riesgo |
|---|---|---|---|
| **En el bundle del navegador** | `axios@1.13.5` (en `resources/js/bootstrap.js`) | 31 (3 critical, 7 high, 21 medium) | **El más relevante**: prototype pollution, SSRF y fuga de datos en encabezados. Muchas aplican a Node y no al navegador, pero se corrigen actualizando |
| Transitivas de `axios` | `follow-redirects`, `form-data` | 2 | Igual que `axios` |
| Compilación | `vite@7.3.1`, `rollup`, `esbuild`, `postcss`, `picomatch`, `nanoid` | 16 | Medio: afectan al servidor de desarrollo de Vite (p. ej. lectura de archivos fuera del proyecto si `npm run dev` se expone en red), no a producción |
| Pruebas | `vitest@4.1.0`, `@vitest/coverage-v8`, `@vitest/mocker`, `undici` (vía `jsdom`) | 25 | Bajo: solo en la máquina del desarrollador y en CI |
| Lint | `js-yaml`, `brace-expansion`, `uri-js`, `flatted` (vía ESLint) | 11 | Bajo |
| Scripts de desarrollo | `shell-quote` (vía `concurrently`, usado por `composer dev`) | 2 | Bajo |

### 4.3 Imagen Docker

- Las 206 vulnerabilidades son del sistema operativo Debian de la imagen base `php:8.2-apache`
  (`libxml2`, `zlib`, `expat`, `acl`, `systemd`, …).
- **Ninguna tiene parche publicado por Debian todavía** (`fixedIn` vacío): no se resuelven con
  `apt-get upgrade`.
- Snyk sugiere una base Alpine, con muchas menos vulnerabilidades. Solo propone una variante `-rc`,
  que no es apta para producción.

### 4.4 Código (Snyk Code)

| Hallazgos | Archivo | Valoración |
|---|---|---|
| 11 × Low "Hardcoded password" | `tests/Feature/*`, `resources/js/tests/login.test.js` | **Aceptables**: son contraseñas de ejemplo dentro de pruebas |
| Medium "Hardcoded password" | `app/Models/User.php:48` | **Falso positivo**: `'password' => 'hashed'` es el *cast* de Laravel que cifra la contraseña |
| Medium "Hardcoded password" | `resources/views/components/⚡chat-soporte.blade.php:12` | **Falso positivo**: `'password'` es una palabra clave del chatbot que responde un texto de ayuda |

**Conclusión:** Snyk Code no encontró vulnerabilidades reales en el código propio (0 High).

### 4.5 Revisión manual complementaria

| Hallazgo | Severidad | Detalle |
|---|---|---|
| Credenciales fijas en `RolesSeeder` | **Alta si se despliega** | `superadmin@biblioteca.com` / `Superadmin123!` (y admin/usuario) están en el repositorio. El entorno de liberación del #156 ejecuta ese seeder, así que cualquier despliegue arranca con una cuenta superadmin de contraseña pública. Snyk Code no lo marcó (su regla de contraseñas no detectó el patrón `Hash::make('...')` del seeder), por eso se agrega desde la revisión manual |

## 5. Remediación medida — VALIDADO

Se probó en una copia aparte (sin cambiar este PR) qué se resuelve actualizando dependencias **dentro
de las versiones permitidas** por `composer.json` y `package.json`:

```bash
composer update       # laravel/framework 12.52.0 -> 12.69.2 (sin cambio de versión mayor)
npm update            # axios 1.13.5 -> 1.20.0, vite 7.3.1 -> 7.3.6, vitest 4.1.0 -> 4.1.11
```

| | Antes | Después |
|---|---|---|
| Dependencias PHP | 33 | **0** |
| Dependencias JS | 87 | **2** (`uri-js@4.4.1` en 2 CVE sin versión corregida; solo lo usa ESLint → `ajv`) |
| PHPUnit | 118 ✓ | 118 ✓ |
| Vitest | 18 ✓ | 18 ✓ |
| ESLint / Prettier / `npm run build` | ✓ | ✓ |

**Hallazgo lateral:** al repetir PHPUnit muchas veces apareció una prueba **inestable**, que existe
también sin actualizar: `LibroTest::test_scope_buscar_filtra_por_nombre_de_autor` falla ~1 de cada
20 corridas con `UniqueConstraintViolationException` en `categorias.nombre`. `CategoriaFactory`
genera el nombre con `faker->word()` sobre una columna única. Explica el fallo transitorio de
PHPUnit en el PR #163. Se corrige aparte (`fix/`).

## 6. Recomendaciones

| Prioridad | Acción | Resuelve |
|---|---|---|
| **Alta** | PR `chore/` con `composer update` y `npm update`, validado con la suite completa | 118 de 120 vulnerabilidades de dependencias |
| **Alta** | En despliegues, no sembrar `RolesSeeder` con contraseñas fijas: tomarlas de variables de entorno o forzar cambio en el primer ingreso | §4.5 |
| Media | Cambiar el workflow de Snyk a **bloqueante** (`--severity-threshold=high` sin `continue-on-error`) una vez actualizadas las dependencias | Evita que vuelvan a entrar vulnerabilidades altas |
| Media | Evaluar una imagen base Alpine con PHP-FPM para la liberación (#156) | 206 del SO (y reduce los 781 MB) |
| Baja | Marcar en Snyk los 2 falsos positivos de Snyk Code como *ignored* con justificación | Ruido en reportes |
| Baja | Corregir `CategoriaFactory` con `faker->unique()->word()` | Prueba inestable |
| Baja | Revisar `uri-js` cuando publique corrección (o cuando ESLint deje de depender de `ajv@6`) | 2 restantes |

## 7. Integración con GitHub Actions — IMPLEMENTADO

`.github/workflows/snyk.yml` (*Seguridad — Snyk*):

| Aspecto | Configuración |
|---|---|
| Disparadores | PR y push a `main`/`develop`, **cada lunes** (detecta CVE nuevos sin cambios de código) y manual |
| Análisis | `composer.lock`, `package-lock.json --dev` y `snyk code test` |
| Resultados | Tabla por severidad en el resumen del run + artefacto `snyk-resultados` (JSON, 30 días) |
| Modo | **Informativo** (`continue-on-error`): con las dependencias actuales todos los PRs saldrían en rojo. Se vuelve bloqueante tras la actualización (§6) |
| Token | Secret `SNYK_TOKEN`. **Sin el secret el job termina en verde con un aviso** y no rompe el CI |

**Para activarlo** (solo la dueña del repositorio puede crear secrets):

1. En app.snyk.io: *Account settings → Auth Token* (o un *Service account* de la organización) → copiar.
2. En GitHub: *Settings → Secrets and variables → Actions → New repository secret* → nombre
   `SNYK_TOKEN`.

El análisis de la imagen Docker no se incluye en este workflow para no duplicar la construcción del
pipeline del #156. **PLANEADO:** agregar `snyk container test` como paso del job
`Construir liberación`.

## 8. Evidencia

- Dashboard de Snyk (*Projects*, agrupado por target): [`snyk/dashboard.png`](snyk/dashboard.png).
  Los totales coinciden con §4:

  | Target en el dashboard | Proyectos | C / H / M / L |
  |---|---|---|
  | `Guadalupe-16/BibliotecaOnline` | composer + npm | 12 / 51 / 52 / 5 (= PHP 1/12/17/3 + JS 11/39/35/2) |
  | `img.tar` (imagen Docker exportada) | container | 0 / 11 / 3 / 192 |
  | `Guadalupe-16/BibliotecaOnline` | Snyk Code | 0 / 0 / 2 / 11 |
- Resultados completos: [`snyk/resultados-2026-09-28.json`](snyk/resultados-2026-09-28.json)

## 9. Limitaciones

- Plan gratuito de Snyk: número limitado de pruebas por mes (Open Source, Code y Container). Alcanza
  para PRs y un análisis semanal de un proyecto de este tamaño.
- Los proyectos del dashboard están en la cuenta personal de quien ejecutó el análisis (el
  repositorio pertenece a otra cuenta). Para un dashboard compartido, la dueña del repositorio
  tendría que importarlo desde su propia organización de Snyk.
- Snyk reporta vulnerabilidades **conocidas y publicadas**; no sustituye la revisión de autorización
  (ver el caso de las rutas de Open Library en `docs/cicd/sonarqube/guadalupe-resultados.md`).
