using System;
using System.IO;
using System.Runtime.CompilerServices;
using Pchp.Core;
using Peachpie.Library;

public static class Dynamic
{
    public static PhpValue loadSingleScript(Context ctx, string scriptFile)
    {
        var script = Context.DefaultScriptingProvider.CreateScript(new Context.ScriptOptions()
        {
            Context = ctx,
            Location = new Location(scriptFile, 0, 0),
            EmitDebugInformation = false,
            IsSubmission = false,
            AdditionalReferences = new string[] {
                typeof(System.Object).Assembly.Location,                                  // mscorlib
                typeof(Pchp.Core.Context).Assembly.Location,                              // Peachpie.Runtime
                typeof(Pchp.Library.Strings).Assembly.Location,                           // Peachpie.Library
                typeof(Peachpie.Library.XmlDom.DOMDocument).Assembly.Location,            // Peachpie.Library.XmlDom
                typeof(Peachpie.Library.Scripting.PhpFunctions).Assembly.Location,        // Peachpie.Library.Scripting
                typeof(Peachpie.Library.Network.CURLFunctions).Assembly.Location,         // cURL
                typeof(Peachpie.Library.Graphics.PhpGd2).Assembly.Location,               // GD2
                typeof(Peachpie.Library.MySql.MySql).Assembly.Location,                   // MySql
                typeof(Peachpie.Library.MsSql.MsSql).Assembly.Location,                   // MsSql
                typeof(Peachpie.Library.PDO.PDO).Assembly.Location,                       // PDO
                typeof(Peachpie.Library.PDO.MySQL.PDOMySQLDriver).Assembly.Location,      // PDO.MySQL
                typeof(Peachpie.Library.PDO.Sqlite.PDOSqliteDriver).Assembly.Location,    // PDO.Sqlite
                typeof(Peachpie.Library.PDO.SqlSrv.PDOSqlServerDriver).Assembly.Location, // PDO.SqlServer
                typeof(nulastudio.Spider.Application).Assembly.Location,                  // NULASpider.CSharp
                System.Reflection.Assembly.GetEntryAssembly().Location,                   // NULASpider.PHP
            },
        }, File.ReadAllText(scriptFile));

        return script.Evaluate(ctx, ctx.Globals, null);
    }
}