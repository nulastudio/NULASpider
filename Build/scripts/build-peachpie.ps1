$Configuration="Debug"

if (!$args[1]) {
    Write-Host "Usage: ./build-peachpie.ps1 <Version> <Configuration>"
    exit
}

$Version=$args[0]

if ($args[1]) {
    $Configuration=$args[1]
}

dotnet build ../../nula-peachpie/src/Peachpie.NET.Sdk/ /p:Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.Runtime/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.Library/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.Library.Graphics/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.Library.Network/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.Library.XmlDom/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.Library.Scripting/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.Library.MsSql/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.Library.MySql/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/PDO/Peachpie.Library.PDO/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/PDO/Peachpie.Library.PDO.MySQL/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/PDO/Peachpie.Library.PDO.Sqlite/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/PDO/Peachpie.Library.PDO.SqlSrv/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.App/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.CodeAnalysis/ /p:TargetFrameworks=netstandard2.0,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.AspNetCore.Web/ /p:TargetFrameworks=netstandard2.1,Version=${Version},Configuration=${Configuration}
dotnet build ../../nula-peachpie/src/Peachpie.AspNetCore.Mvc/ /p:TargetFrameworks=netstandard2.1,Version=${Version},Configuration=${Configuration}
