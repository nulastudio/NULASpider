#!/bin/bash

workdir=$(cd $(dirname $0); pwd)
basedir=$(cd $(dirname ${workdir}); pwd)
rootdir=$(cd $(dirname ${basedir}); pwd)

langs=("zh-CN" "en");

if [ -d ${rootdir}/docs/build ];then
    rm -rf ${rootdir}/docs/build
fi

for lang in ${langs[@]}; do
    echo "
building language ${lang}
"
    cd ${rootdir}/docs
    sphinx-build -E -a source build/${lang} -D language=${lang}
done

echo "
all done!
"
