using System;
using System.Text;
using UtfUnknown;

namespace nulastudio.Encoding
{
    public class Encoding
    {
        public static void registerProvider()
        {
            System.Text.Encoding.RegisterProvider(CodePagesEncodingProvider.Instance);
        }
    }
}
