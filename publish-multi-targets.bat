@echo off

set workdir=%~dp0

for %%i in (win-x64 win-x86 linux-x64 osx-x64) do (
    echo\
    echo publishing target %%i
    echo\
    dotnet publish -c=Release -r=%%i -o=%workdir%/Release/%%i/ %workdir%/NULASpider.PHP/NULASpider.PHP.msbuildproj
)

echo\
echo all done!
echo\