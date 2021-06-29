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
	REM git branch -d feature/CBFF-000-promote-image
	REM git checkout develop && git pull && git checkout -b feature/CBFF-000-promote-image
	echo REPO: %REPO%
)
IF /I "%PASO%" EQU "2" (
	cd %REPO%
	REM git add . && git commit -m "CBFF-000: Promote_Image Redeploy" && git push --set-upstream origin feature/CBFF-000-promote-image
	REM git checkout develop && git pull && git branch -d feature/CBFF-000-promote-image
	echo REPO: %REPO%
)
IF /I "%PASO%" EQU "3" (
	cd %REPO%
	git checkout staging &&	git pull &&	git checkout master &&	git pull &&	git checkout develop &&	git pull
)
IF /I "%PASO%" EQU "4" (
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
)
cls
ENDLOCAL
exit