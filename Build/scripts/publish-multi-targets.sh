#!/bin/sh

workdir=$(cd $(dirname $0); pwd)
targets=("win-x64");
latestTag=$(git describe --tags `git rev-list --tags --max-count=1`)

rm -rf ${workdir}/../Release/

for target in ${targets[@]}; do
    echo "\npublishing target ${target}\n"
    dotnet publish -c=Release -r=${target} -o=${workdir}/../Release/${target}/ ${workdir}/../../NULASpider.PHP/NULASpider.PHP.msbuildproj /p:PublishPath=${workdir}/../Release/${target}/
    cd ${workdir}/../Release/${target}/
    zip -r ${workdir}/../Release/${target}-${latestTag}.zip .
done

echo "\nall done!\n"
