@echo off

for /f "delims=" %%i in ('git rev-list --tags --max-count=1') do set latestCommit=%%i
for /f "delims=" %%i in ('git describe --tags %latestCommit%') do set latestTag=%%i

set workdir=%~dp0

for %%i in (win-x64 win-x86 linux-x64 osx-x64) do (
    echo\
    echo publishing target %%i
    echo\
    dotnet publish -c=Release -r=%%i -o=%workdir%Release\%%i\ %workdir%NULASpider.PHP\NULASpider.PHP.msbuildproj
    cd %workdir%Release\%%i
    %workdir%Build\tools\windows\7za\x86\7za.exe -r a %%i-%latestTag%.zip .
    move %workdir%Release\%%i\%%i-%latestTag%.zip %workdir%Release\%%i-%latestTag%.zip
)

echo\
echo all done!
echo\