@echo off
cls
cd C:\Users\U631378\Documents\Programas\eCommerce
SETLOCAL
SET REPO=%1
SET branch=%2
IF exist %REPO% (
    cd %REPO%
    echo %REPO% exist
) ELSE (
    git clone "https://bitbucket.telecom.com.ar/scm/cbff/%REPO%.git"
    cd %REPO%
    echo %REPO% cloned
)

git checkout develop

IF /I "%branch%" NEQ "" (
	git branch -d %branch%
    git pull && git checkout -b %branch%
	echo REPO: %REPO%
)
code -g C:\Users\U631378\Documents\Programas\eCommerce\%REPO%

ENDLOCAL
exit