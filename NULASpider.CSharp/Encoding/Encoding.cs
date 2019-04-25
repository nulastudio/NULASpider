using System.IO;
using System;
using System.Text;
using UtfUnknown;
using Pchp.Core;

namespace nulastudio.Encoding
{
    public class Encoding
    {
        public static void registerProvider()
        {
            System.Text.Encoding.RegisterProvider(CodePagesEncodingProvider.Instance);
        }
        public static string detect(Context ctx, PhpString content)
        {
            var result = CharsetDetector.DetectFromBytes(content.ToBytes(ctx));
            return result.Detected?.EncodingName;
        }
    }
}
