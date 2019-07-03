#!/bin/sh

workdir=$(cd $(dirname $0); pwd)
targets=("win-x64" "win-x86" "linux-x64" "osx-x64");
latestTag=$(git describe --tags `git rev-list --tags --max-count=1`)

workdir=${workdir}/../Release

if [ -d ${workdir} ];then
    rm -rf ${workdir}
fi

for target in ${targets[@]}; do
    echo "\npublishing target ${target}\n"
    dotnet publish -c=Release -r=${target} -o=${workdir}/${target}/ ${workdir}/../../NULASpider.PHP/NULASpider.PHP.msbuildproj
    if [ -d ${workdir}/${target} ];then
        cd ${workdir}/${target}
        zip -r ${workdir}/${target}-${latestTag}.zip .
    fi
done

echo "\nall done!\n"
