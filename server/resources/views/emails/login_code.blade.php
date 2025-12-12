<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Code de connexion</title>
</head>
<body style="font-family: Arial, sans-serif; line-height:1.5;">
<p>Salut,</p>

<p>Voici ton code de connexion :</p>

<p style="font-size:28px; font-weight:700; letter-spacing: 4px;">
    {{ $code }}
</p>

<p>Il expire {{ $expiresHuman }}.</p>

<p style="color:#666; font-size:12px;">
    Si tu n’es pas à l’origine de cette demande, ignore ce mail.
</p>
</body>
</html>
