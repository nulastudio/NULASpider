@echo off

set Configuration=Debug

if not "%2" == "" (
    set Configuration=%2
)

dotnet build ../../../peachpie/src/Peachpie.NET.Sdk/ /p:Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.Runtime/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.Library/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.Library.Graphics/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.Library.Network/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.Library.XmlDom/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.Library.Scripting/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.Library.MsSql/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.Library.MySql/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/PDO/Peachpie.Library.PDO/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/PDO/Peachpie.Library.PDO.MySQL/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/PDO/Peachpie.Library.PDO.Sqlite/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/PDO/Peachpie.Library.PDO.SqlSrv/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.App/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.CodeAnalysis/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.AspNetCore.Web/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
dotnet build ../../../peachpie/src/Peachpie.AspNetCore.Mvc/ /p:TargetFrameworks=netstandard2.0,Version=%1,Configuration=%Configuration%
