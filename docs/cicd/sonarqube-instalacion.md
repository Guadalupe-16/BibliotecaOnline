# SonarQube local — instalación y ejecución

> Issue: #153 · Verificado el 2026-09-27 en macOS 26.6 (Apple Silicon, 16 GB RAM)

Guía para levantar SonarQube en tu máquina y analizar BibliotecaOnline. Es la base de los análisis
de los issues #154 y #155.

Estados: **IMPLEMENTADO** (existe), **VALIDADO** (se ejecutó con el resultado indicado),
**PLANEADO** (propuesta).

---

## 1. Stack

| Pieza | Versión | Dónde corre | Estado |
|---|---|---|---|
| SonarQube Community Build | 26.9.0.129388 | Contenedor (`sonarqube/docker-compose.yml`) | **VALIDADO** |
| PostgreSQL | 17 (alpine) | Contenedor, solo en la red interna de Docker | **VALIDADO** |
| SonarScanner CLI | 8.1.0.6389 | Nativo con Homebrew (Mac) **o** contenedor `sonarsource/sonar-scanner-cli:12.2` (Linux/Intel) | **VALIDADO** (nativo) |
| Docker en Mac | Colima 0.10.3 + Docker 29.5 + Compose 5.5 | VM ligera de Colima (2 CPU, 4 GB) | **VALIDADO** |

Archivos del repositorio:

| Archivo | Para qué |
|---|---|
| `sonarqube/docker-compose.yml` | Levanta SonarQube + PostgreSQL; incluye el scanner en contenedor (perfil `scan`) |
| `sonar-project.properties` | Qué se analiza, qué son pruebas, qué se excluye y dónde buscar cobertura |
| `.gitignore` | Ignora `/.scannerwork` y `/coverage` |

## 2. Requisitos

- Docker con Compose. En Mac se recomienda **Colima** (sin interfaz, gratis). Docker Desktop también
  funciona.
- Al menos 4 GB de RAM libres para la VM de Docker (SonarQube usa ~2.5 GB).
- En Mac Apple Silicon: `sonar-scanner` nativo (ver §5).

## 3. Instalación (una sola vez)

### 3.1 Docker con Colima (Mac)

```bash
brew install colima docker docker-compose
colima start --cpu 2 --memory 4 --disk 30
```

Para que `docker compose` encuentre el plugin, agrega en `~/.docker/config.json`:

```json
"cliPluginsExtraDirs": ["/opt/homebrew/lib/docker/cli-plugins"]
```

> Si antes tuviste Docker Desktop y `docker pull` falla con
> `docker-credential-desktop: executable file not found`, elimina la línea `"credsStore": "desktop"`
> de `~/.docker/config.json`.

### 3.2 Scanner nativo (Mac)

```bash
brew install sonar-scanner
sonar-scanner --version     # debe decir "Mac OS X ... aarch64" en Apple Silicon
```

## 4. Levantar SonarQube

```bash
cd BibliotecaOnline
colima start                                              # si no está corriendo
docker compose -f sonarqube/docker-compose.yml up -d
```

Espera 1–2 minutos y abre **http://localhost:9001**.

- **Puerto 9001, no 9000**: PHP-FPM (PHP de Homebrew / Laravel Valet) ocupa el 9000 por defecto.
  Para usar otro puerto: `SONAR_PORT=9100 docker compose -f sonarqube/docker-compose.yml up -d`.
- Primer ingreso: usuario `admin`, contraseña `admin`; SonarQube obliga a cambiarla.
- Verificación rápida: `curl http://localhost:9001/api/system/status` → `"status":"UP"`.

### Generar el token de análisis

1. Avatar (arriba a la derecha) → **My Account → Security**.
2. **Generate Tokens**: nombre `bibliotecaonline-local`, tipo **Global Analysis Token**.
3. Cópialo. **Nunca** lo escribas en `sonar-project.properties`, en el compose ni en un commit.

Para no dejar el token en pantalla ni en el historial de la terminal:

```bash
read -s "SONAR_TOKEN?Token de SonarQube: " && export SONAR_TOKEN   # zsh
```

## 5. Ejecutar el análisis

**Mac Apple Silicon (recomendado):**

```bash
sonar-scanner -Dsonar.host.url=http://localhost:9001
```

**Linux o Mac Intel (scanner en contenedor):**

```bash
docker compose -f sonarqube/docker-compose.yml run --rm scanner
```

Ambos leen `sonar-project.properties` y el token de la variable `SONAR_TOKEN`. El resultado queda
en **http://localhost:9001/dashboard?id=BibliotecaOnline**.

> **No usar el scanner en contenedor en Mac Apple Silicon.** La imagen solo existe para `amd64`;
> corre emulada dentro de los 4 GB de Colima y el análisis de JavaScript/CSS falla con
> `The bridge server is unresponsive` (verificado el 2026-09-27). El scanner nativo usa la memoria
> del equipo y terminó en 14.5 s.

### Qué se analiza (`sonar-project.properties`)

| Propiedad | Valor |
|---|---|
| Fuentes | `app`, `bootstrap/app.php`, `config`, `database`, `routes`, `resources` |
| Pruebas | `tests` (PHPUnit y Playwright), `resources/js/tests` (Vitest) |
| Excluido | `vendor`, `node_modules`, `public/build`, `storage`, `bootstrap/cache`, `docs/coverage`, reportes de Playwright |
| Lenguajes detectados | PHP (incluye Blade), JavaScript, CSS/HTML |

### Cobertura (opcional)

SonarQube la muestra si existen estos reportes antes de analizar:

| Lenguaje | Comando | Requisito | Estado |
|---|---|---|---|
| PHP | `php artisan test --coverage-clover coverage/clover.xml` | Xdebug o PCOV | **PLANEADO**: la máquina de la verificación no tiene ninguno |
| JS | `npx vitest run --coverage --coverage.reporter=lcov` | `npm ci` | **PLANEADO** |

Sin reportes, el scanner muestra dos `WARN` de archivo no encontrado; son esperados.

## 6. Resultado de referencia — VALIDADO

Primer análisis de `develop` (`d6deea0`), 2026-09-27, scanner nativo:

| Métrica | Valor |
|---|---|
| Quality Gate (Sonar way) | **Passed** |
| Líneas de código (ncloc) | 2 366 en 101 archivos |
| Bugs | 20 (Reliability **C**) |
| Vulnerabilities | 0 (Security **A**) |
| Security Hotspots | 0 (Review **A**) |
| Code Smells | 49 (Maintainability **A**) |
| Deuda técnica | 124 min (~2 h) |
| Duplicación | 1.3 % |
| Cobertura | 0.0 % (sin reportes importados, ver §5) |

Reglas con más hallazgos (69 en total: 41 PHP, 28 Blade/HTML):

| Regla | Hallazgos | Qué significa |
|---|---|---|
| `php:S113` | 32 | Archivo sin salto de línea final |
| `Web:InputWithoutLabelCheck` | 17 | Campos de formulario sin `<label>` asociado (accesibilidad) |
| `Web:S6853` | 8 | Etiqueta de formulario no asociada a un control |
| `Web:S5255` / `php:S3358` / `php:S1192` / `php:S1848` | 2 c/u | Otros (ternarios anidados, literales repetidos, objetos creados sin usar, etc.) |

El análisis detallado de un PR concreto se hace en #154 y #155.

## 7. Detener y limpiar

```bash
docker compose -f sonarqube/docker-compose.yml stop     # detiene, conserva datos
docker compose -f sonarqube/docker-compose.yml down     # borra contenedores, conserva volúmenes
docker compose -f sonarqube/docker-compose.yml down -v  # borra TODO (proyectos, usuarios, tokens)
colima stop                                             # apaga la VM de Docker
```

## 8. Solución de problemas

| Síntoma | Causa | Solución |
|---|---|---|
| `ERR_CONNECTION_RESET` en el navegador | Puerto del host ocupado (p. ej. PHP-FPM en 9000) o SonarQube aún arrancando | Usar 9001 (default) o `SONAR_PORT`; esperar 1–2 min |
| `docker-credential-desktop: executable file not found` | Restos de Docker Desktop en `~/.docker/config.json` | Quitar `"credsStore": "desktop"` |
| `The bridge server is unresponsive` | Scanner en contenedor emulado (amd64) sin memoria suficiente | Scanner nativo (§3.2) |
| `Not authorized` / 401 | Falta `SONAR_TOKEN` o está revocado | Generar un token nuevo y exportarlo |
| SonarQube se reinicia solo | Menos de 4 GB para la VM | `colima stop && colima start --memory 6` |

## 9. Seguridad

- No hay secretos versionados: el token se pasa por variable de entorno y la contraseña de
  PostgreSQL solo aplica dentro de la red de Docker (se puede cambiar con `SONAR_DB_PASSWORD`).
- `.scannerwork/` y `coverage/` están en `.gitignore`.
- SonarQube queda expuesto solo en tu máquina (`localhost:9001`); no es un servidor compartido.
- **PLANEADO**: ejecutar el análisis en CI requeriría un SonarQube accesible desde GitHub Actions
  (o SonarQube Cloud) y el token en GitHub Secrets.
