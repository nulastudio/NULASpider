#!/bin/sh

workdir=$(cd $(dirname $0); pwd)
targets=("win-x64" "win-x86" "linux-x64" "osx-x64");
latestTag=$(git describe --tags `git rev-list --all --max-count=1`)
latestComment=$(git log 392d3a62cb9af63f625e20892c56de023ba287cc -1 --pretty=%B)

betas=("alpha" "beta" "rc");

export releaseTitle=latestTag
export releaseNote=${latestComment}
export preRelease='false'

for v in ${betas[@]}; do
    if [[ $latestTag =~ $v ]]
    then
        preRelease='true'
        break
    fi
done

if [[ $releaseNote =~ "# Release" ]]
then
    releaseNote=${releaseNote#*\#\ Release}
fi

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
