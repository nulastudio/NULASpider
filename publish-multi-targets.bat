@echo off

set workdir=%~dp0

for %target in ("win-x64" "win-x86" "linux-x64" "osx-x64") do (
	echo "\npublishing target %target\n"
	dotnet publish -c=Release -r=%target -o=%workdir/Release/%target/ %workdir/NULASpider.PHP/NULASpider.PHP.msbuildproj
)

echo "all done!\n"