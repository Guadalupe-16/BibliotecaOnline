# Propuesta de infraestructura como código (Terraform) — BibliotecaOnline

> Issue: #138 · Spec: [`specs/003-infraestructura-cicd/`](../../specs/003-infraestructura-cicd/spec.md)
> Fecha: 2026-09-25

**Estado de todo el documento: PLANEADO.**

No existe hosting, ni cuentas de nube, ni recursos creados (ver
[`estrategia-despliegue.md`](estrategia-despliegue.md)). Este documento es solo diseño. El
repositorio **no contiene** archivos `.tf`, `.tfvars` ni `.tfstate`, y los bloques HCL de abajo son
ilustrativos: no se han ejecutado con `terraform plan` ni `terraform apply`.

---

## 1. Objetivo

Describir en código, versionado y revisable por PR, la infraestructura de los ambientes **Staging**
y **Production** definidos en la estrategia de despliegue, para que:

- crear o recrear un ambiente sea reproducible;
- cualquier cambio de infraestructura pase por el mismo flujo Issue → Spec → PR → revisión que el
  código;
- Staging y Production difieran solo en variables (tamaño, dominio), no en estructura.

## 2. Proveedor de referencia

Se toma **AWS** como referencia porque es el proveedor con más documentación de Terraform y
módulos oficiales. La estructura (módulos, variables, estado remoto) aplica igual a otros
proveedores (DigitalOcean, Azure, GCP); la elección final depende de costo y créditos educativos,
y se decidirá en una spec propia antes de implementar.

## 3. Recursos previstos por ambiente

| Recurso | Propósito | Staging | Production |
|---|---|---|---|
| Red (VPC + subred pública y privada) | Aislar la base de datos de internet | 1 zona | 2 zonas |
| Servidor de aplicación (EC2) | PHP 8.2 + Nginx + PHP-FPM + worker de colas | `t3.micro` | `t3.small` |
| Base de datos (RDS MySQL 8) | Datos de la app | `db.t3.micro`, sin réplica | `db.t3.micro`, backups automáticos 7 días |
| Almacenamiento (S3) | Respaldos de `scripts/backup.sh` y archivos subidos | Bucket propio | Bucket propio con versionado |
| Security groups | 80/443 públicos al servidor; 3306 solo desde el servidor | ✓ | ✓ |
| DNS + TLS (Route 53 + ACM, o Let's Encrypt en el servidor) | Dominio HTTPS | `staging.<dominio>` | `<dominio>` |

## 4. Estructura de archivos propuesta

```text
infra/
├── modules/
│   ├── network/        # VPC, subredes, security groups
│   ├── app_server/     # EC2, IAM role, user_data (instalación PHP/Nginx)
│   ├── database/       # RDS MySQL
│   └── storage/        # S3 para respaldos
└── envs/
    ├── staging/
    │   ├── main.tf         # llama a los módulos
    │   ├── backend.tf      # estado remoto
    │   └── staging.tfvars  # NO versionado si contiene datos sensibles
    └── production/
        └── ...
```

## 5. Ejemplo ilustrativo (no ejecutado)

```hcl
# infra/envs/staging/main.tf — ILUSTRATIVO, PLANEADO
module "network" {
  source      = "../../modules/network"
  environment = "staging"
  az_count    = 1
}

module "database" {
  source         = "../../modules/database"
  environment    = "staging"
  subnet_ids     = module.network.private_subnet_ids
  instance_class = var.db_instance_class
  # La contraseña NO va en código: se genera y guarda en AWS Secrets Manager
}

module "app_server" {
  source        = "../../modules/app_server"
  environment   = "staging"
  subnet_id     = module.network.public_subnet_ids[0]
  instance_type = var.app_instance_type
  db_endpoint   = module.database.endpoint
}

output "app_public_ip" {
  value = module.app_server.public_ip
}
```

### Variables previstas

| Variable | Staging | Production |
|---|---|---|
| `app_instance_type` | `t3.micro` | `t3.small` |
| `db_instance_class` | `db.t3.micro` | `db.t3.micro` |
| `domain_name` | `staging.<dominio>` | `<dominio>` |
| `backup_retention_days` | 1 | 7 |

### Salidas previstas

`app_public_ip`, `app_url`, `db_endpoint` (sin credenciales), `backups_bucket_name`.

## 6. Estado y secretos

- **Estado remoto:** bucket S3 con cifrado y versionado + tabla DynamoDB para bloqueo. Nunca se
  versiona `terraform.tfstate` (contiene datos sensibles).
- **Secretos:** contraseñas de base de datos y `APP_KEY` en AWS Secrets Manager o en GitHub
  Secrets por *Environment*; nunca en `.tf` ni en `.tfvars` versionados.
- **Credenciales de Terraform en CI:** OIDC de GitHub Actions hacia un rol IAM (sin llaves de acceso
  de larga duración).
- `.gitignore` deberá incluir `*.tfstate*`, `.terraform/` y `*.tfvars` cuando exista `infra/`.

## 7. Integración con el pipeline

Encaja en el pipeline de [`estrategia-despliegue.md`](estrategia-despliegue.md) §3:

| Evento | Acción de Terraform |
|---|---|
| PR que modifica `infra/` | `terraform fmt -check`, `terraform validate` y `terraform plan` publicado como comentario |
| Merge a `develop` | `terraform apply` en Staging |
| PR `develop → main` aprobado | `terraform apply` en Production con aprobación manual (GitHub Environment `production`) |

El despliegue de la aplicación (código) sigue siendo un paso separado del de infraestructura.

## 8. Riesgos y costos

| Riesgo | Mitigación |
|---|---|
| Costo inesperado | Instancias pequeñas, alarmas de presupuesto, destruir Staging cuando no se use |
| `apply` accidental | Solo desde CI con aprobación; nadie aplica desde su máquina |
| Pérdida del estado | Estado remoto versionado y con bloqueo |
| Secretos filtrados | Secrets Manager + OIDC; revisión de PR sobre `infra/` |

## 9. Siguientes pasos (todos PLANEADO)

1. Elegir proveedor y confirmar presupuesto o créditos educativos.
2. Abrir un issue y una spec (`specs/NNN-infraestructura-terraform/`) para la implementación.
3. Crear `infra/` con `terraform validate` en CI, sin aplicar.
4. Aplicar primero solo Staging y validar el pipeline completo antes de Production.
