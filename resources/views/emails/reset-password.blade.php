<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Réinitialisation du mot de passe</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 0; margin: 0;">
    <div style="max-width: 480px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #eee; padding: 32px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{{ $logoUrl }}" alt="Logo AEDDI" style="max-width: 120px; margin-bottom: 16px;">
        </div>
        <h2 style="color: #4b2aad; text-align: center;">Réinitialisation du mot de passe</h2>
        <p>Bonjour,</p>
        <p>Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe :</p>
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $url }}" style="background: #4b2aad; color: #fff; padding: 12px 32px; border-radius: 4px; text-decoration: none; font-weight: bold;">Réinitialiser mon mot de passe</a>
        </div>
        <p style="color: #888; font-size: 13px;">Si vous n'avez pas demandé de réinitialisation, ignorez cet email.</p>
        <p style="color: #888; font-size: 13px;">AEDDI</p>
</body>
</html>