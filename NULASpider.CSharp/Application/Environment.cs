using System;
using System.IO;

namespace nulastudio
{
    public class Environment
    {
        public static string getRootDirectory()
        {
            return System.Environment.CurrentDirectory.TrimEnd(Path.DirectorySeparatorChar);
        }
        public static string getTempDirectory()
        {
            return Path.GetTempPath().TrimEnd(Path.DirectorySeparatorChar);
        }
    }
}
