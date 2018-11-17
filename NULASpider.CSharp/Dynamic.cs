using System;
using System.IO;
using System.Runtime.CompilerServices;
using Pchp.Core;
using Peachpie.Library;

public static class Dynamic
{
    public static PhpValue loadSingleScript(Context ctx, string scriptFile)
    {
        var script = ctx.ScriptingProvider.CreateScript(new Context.ScriptOptions()
        {
            Context = ctx,
            Location = new Location(scriptFile, 0, 0),
            EmitDebugInformation = false,
            IsSubmission = false,
        }, File.ReadAllText(scriptFile));

        return script.Evaluate(ctx, ctx.Globals, null);
    }
}