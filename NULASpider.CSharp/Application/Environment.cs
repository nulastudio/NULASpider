using System;
using System.IO;
using System.Reflection;

namespace nulastudio
{
    public class Environment
    {
        public static string getRootDirectory()
        {
            // 程序根目录
            return Path.GetDirectoryName(Assembly.GetEntryAssembly().Location).TrimEnd(Path.DirectorySeparatorChar);
        }
        public static string getWorkingDirectory()
        {
            // 启动目录
            return System.Environment.CurrentDirectory.TrimEnd(Path.DirectorySeparatorChar);
        }
        public static string getTempDirectory()
        {
            // TEMP目录
            return Path.GetTempPath().TrimEnd(Path.DirectorySeparatorChar);
        }
    }
}
