@echo off
cd /d "%~dp0"
if not exist "config.php" (
 echo Extraia os dois arquivos na pasta do Fluxon, ao lado de config.php.
 pause
 exit /b 1
)
"C:\xampp\php\php.exe" "medir-conexao.php"
pause
