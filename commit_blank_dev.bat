@echo off
:inicio
cls
cd C:\Users\U631378\Documents\Programas\eCommerce
SETLOCAL
SET REPO=%1
IF exist %REPO% (
    cd %REPO%
    echo %REPO% exist
) ELSE (
    git clone "https://bitbucket.telecom.com.ar/scm/cbff/%REPO%.git"
    cd %REPO%
    echo %REPO% cloned
)

git checkout develop && git pull  && git branch -d feature/CBFF-000-redeploy
git checkout -b feature/CBFF-000-redeploy
git add . && git commit --allow-empty -m "CBFF-000: Trigger Build" && git push --set-upstream origin feature/CBFF-000-redeploy
git checkout develop && git branch -d feature/CBFF-000-redeploy

ENDLOCAL
cls
exit