#! /bin/bash

Configuration=Debug

if [[ $2 ]]; then
    Configuration=$2
fi

dotnet build ../peachpie/src/Peachpie.NET.Sdk/ /p:Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.Runtime/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.Library/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.Library.Graphics/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.Library.Network/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.Library.XmlDom/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.Library.Scripting/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.Library.MsSql/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.Library.MySql/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/PDO/Peachpie.Library.PDO/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/PDO/Peachpie.Library.PDO.MySQL/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/PDO/Peachpie.Library.PDO.SqlSrv/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.App/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.CodeAnalysis/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.AspNetCore.Web/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
dotnet build ../peachpie/src/Peachpie.AspNetCore.Mvc/ /p:TargetFrameworks=netcoreapp2.0,Version=$1,Configuration=${Configuration}
