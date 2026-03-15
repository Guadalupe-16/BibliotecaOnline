<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0d0d14; color: #ffffff; margin: 0; padding: 40px 20px; }
        .contenedor { max-width: 480px; margin: 0 auto; background: #13131f; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 40px; }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo-icono { display: inline-block; width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899); text-align: center; line-height: 56px; font-size: 24px; margin-bottom: 12px; }
        h1 { text-align: center; font-size: 22px; font-weight: 600; margin: 0 0 8px; }
        .subtitulo { text-align: center; color: rgba(255,255,255,0.4); font-size: 14px; margin-bottom: 32px; }
        .pin-caja { background: rgba(99,102,241,0.1); border: 2px solid rgba(99,102,241,0.4); border-radius: 12px; text-align: center; padding: 24px; margin-bottom: 24px; }
        .pin { font-size: 42px; font-weight: 700; letter-spacing: 12px; background: linear-gradient(135deg, #6366f1, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .aviso { color: rgba(255,255,255,0.4); font-size: 13px; text-align: center; line-height: 1.6; }
        .expira { color: #f59e0b; font-weight: 600; }
        .footer { text-align: center; margin-top: 32px; color: rgba(255,255,255,0.2); font-size: 12px; }
    </style>
</head>
<body>
    <div class="contenedor">
        <div class="logo">
            <div class="logo-icono">📚</div>
            <h1>Verificación de correo</h1>
            <p class="subtitulo">Usa este PIN para activar tu cuenta en la Biblioteca Digital</p>
        </div>

        <div class="pin-caja">
            <div class="pin">{{ $pin }}</div>
        </div>

        <p class="aviso">
            Este PIN es válido por <span class="expira">15 minutos</span>.<br>
            Si no creaste una cuenta, puedes ignorar este mensaje.
        </p>

        <div class="footer">
            Biblioteca Digital Online &mdash; No respondas a este correo
        </div>
    </div>
</body>
</html>
