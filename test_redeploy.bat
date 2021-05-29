@echo off
cls
cd C:\Users\U631378\Documents\Programas\eCommerce
SETLOCAL
SET REPO=%1
SET /A PASO=%2
IF /I "%PASO%" EQU "1" (
	IF exist %REPO% (
		cd %REPO%
		echo %REPO% exist
	) ELSE (
		git clone "https://bitbucket.telecom.com.ar/scm/cbff/%REPO%.git"
		cd %REPO%
		echo %REPO% cloned
	)
	git branch -d feature/CBFF-000-promote-image
	git checkout develop && git pull && git checkout -b feature/CBFF-000-promote-image
	echo REPO: %REPO%
)
IF /I "%PASO%" EQU "2" (
	git add . && git commit -m "CBFF-000: Promote_Image Redeploy" && git push --set-upstream origin feature/CBFF-000-promote-image
	git checkout develop && git pull && git branch -d feature/CBFF-000-promote-image
	echo REPO: %REPO%
)
IF /I "%PASO%" EQU "3" (
	git checkout staging &&	git pull &&	git checkout master &&	git pull &&	git checkout develop &&	git pull
)
cls
ENDLOCAL
exit