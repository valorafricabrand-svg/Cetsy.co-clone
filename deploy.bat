@echo off
echo Setting up Railway deployment...

REM Check if Railway CLI is installed
railway --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Railway CLI not found. Installing...
    powershell -Command "& {irm get.railway.app/windows | iex}"
    if %errorlevel% neq 0 (
        echo Failed to install Railway CLI. Please install manually from https://railway.app/cli
        pause
        exit /b 1
    )
)

REM Login to Railway
echo Logging into Railway...
railway login
if %errorlevel% neq 0 (
    echo Login failed. Please check your credentials.
    pause
    exit /b 1
)

REM Deploy the application
echo Deploying application...
railway up --service ***
if %errorlevel% neq 0 (
    echo Deployment failed. Please check the error messages above.
    pause
    exit /b 1
)

echo Deployment successful!
pause