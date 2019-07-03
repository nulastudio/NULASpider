@echo off

for /f "delims=" %%i in ('git rev-list --all --max-count=1') do set latestCommit=%%i
for /f "delims=" %%i in ('git describe --tags %latestCommit%') do set latestTag=%%i

set workdir=%~dp0..\Release\

if exist %workdir% (
    rd /s/q %workdir%
)

for %%i in (win-x64 win-x86 linux-x64 osx-x64) do (
    echo\
    echo publishing target %%i
    echo\
    dotnet publish -c=Release -r=%%i -o=%workdir%%%i\ %workdir%..\..\NULASpider.PHP\NULASpider.PHP.msbuildproj
    if exist %workdir%%%i (
        cd %workdir%%%i
        %workdir%..\tools\windows\7za\x86\7za.exe -r a %workdir%%%i-%latestTag%.zip .
    )
)

echo\
echo all done!
echo\