#!/bin/bash

workdir=$(cd $(dirname $0); pwd)
workdir=${workdir}/../Release
targets=("win-x64" "win-x86" "linux-x64" "osx-x64");
latestTag=$(git describe --tags `git rev-list --all --max-count=1`)
latestComment=$(git log -1 --pretty=%B)

betas=("alpha" "beta" "rc");

releaseTitle=${latestTag}
releaseNote=${latestComment}

for v in ${betas[@]}; do
    if [[ $latestTag =~ $v ]]
    then
        echo "" > ${workdir}/preRelease
        break
    fi
done

if [[ $releaseNote =~ "# Release" ]]
then
    releaseNote=${releaseNote#*\#\ Release}
fi

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

echo ${releaseTitle} > ${workdir}/releaseTitle
echo "${releaseNote}" > ${workdir}/releaseNote

echo "\nall done!\n"
