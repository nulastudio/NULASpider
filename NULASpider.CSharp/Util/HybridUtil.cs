using System.Text;
using System.Linq;
using System;
using Pchp.Core;
using Pchp.Library;

public class HybridUtil
{
    /// <summary>
    /// 将PHP类型转换回CLR类型，主要用于解决将PHP类型传进object类型的C#类库时的奇怪问题（比如传进去后变为NULL）
    /// </summary>
    /// <param name="ctx">PhpContext</param>
    /// <param name="value">PHP值</param>
    /// <returns>CLR类型</returns>
    public static object toObject(Context ctx, PhpValue value)
    {
        return value.ToClr();
    }

    public static string base64encode(Context ctx, byte[] bytes)
    {
        return System.Convert.ToBase64String(bytes);
    }

    public static byte[] base64decode(Context ctx, string base64)
    {
        return System.Convert.FromBase64String(base64);
    }

    public static PhpString byteArray2String(Context ctx, byte[] bytes)
    {
        return new PhpString(bytes);
    }

    public static bool loadClass(Context ctx, string @class)
    {
        return ctx.GetDeclaredType(@class, true) != null;
    }

    public static PhpValue getStrItem(Context ctx, PhpValue obj, PhpValue key)
    {
        return getItem(ctx, obj.ToClr(), key.ToString(ctx));
    }
    public static PhpValue getIntItem(Context ctx, PhpValue obj, PhpValue key)
    {
        return getItem(ctx, obj.ToClr(), key.ToInt());
    }
    private static PhpValue getItem(Context ctx, dynamic obj, dynamic key)
    {
        return PhpValue.FromClr(obj[key]);
    }

    public static PhpString toBlob(Context ctx, PhpString @string)
    {
        // byte[] bytes = phpArray.Values.Select((val) => {
        //     return (byte)val.ToInt();
        // }).ToArray();
        return new PhpString(new PhpString.Blob(@string.ToBytes(ctx)));
    }
}
