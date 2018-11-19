#!/bin/sh

workdir=$(cd $(dirname $0); pwd)
targets=("win-x64" "win-x86" "linux-x64" "osx-x64");

rm -rf ${workdir}/Release/

for target in ${targets[@]}; do
	echo "\npublishing target ${target}\n"
	dotnet publish -c=Release -r=${target} -o=${workdir}/Release/${target}/ ${workdir}/NULASpider.PHP/NULASpider.PHP.msbuildproj
done

echo "all done!\n"