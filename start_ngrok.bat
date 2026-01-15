@echo off
echo ========================================
echo NGROK Setup for Local Webhook
echo ========================================
echo.

REM Check if ngrok is installed
where ngrok >nul 2>nul
if %errorlevel% neq 0 (
    echo [!] ngrok not found!
    echo.
    echo Install ngrok:
    echo 1. Download from: https://ngrok.com/download
    echo 2. Or run: choco install ngrok
    echo 3. Sign up free at ngrok.com and get auth token
    echo 4. Run: ngrok config add-authtoken YOUR_TOKEN
    echo.
    pause
    exit /b 1
)

echo [*] Starting ngrok tunnel to localhost:80...
echo.
echo After ngrok starts, copy the "Forwarding" URL
echo Example: https://xxxx-xx-xx-xx-xx.ngrok-free.app
echo.
echo Your webhook URL will be:
echo [NGROK_URL]/Chatbot/datsun-chatbot/public/api/webhook/whatsapp/vivo%%20mobile
echo.
echo Set this in Evolution API webhook settings!
echo.
echo ========================================

ngrok http 80
