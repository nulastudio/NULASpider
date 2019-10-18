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
}
