using System;
using System.IO;
using System.Runtime.CompilerServices;
using System.Threading;
using System.Threading.Tasks;
using Pchp.Core;
using Pchp.Library.Spl;
using Peachpie.Library;
using System.Threading.Tasks.Schedulers;
using Pchp.Core.Reflection;
using ConsoleTableExt;
using System.Data;
using System.Collections.Generic;
using System.Drawing;
using Console = Colorful.Console;

namespace nulastudio.Spider
{
    public class Application
    {
        private static Context ctx;
        private static dynamic spider;
        private static PhpArray configs;
        private static readonly object downloadStatusObj = new object();
        private static readonly object processStatusObj = new object();
        private static long downloading = 0;
        private static long downloaded = 0;
        private static long processing = 0;
        private static long processed = 0;
        private static bool inited = false;
        private static bool finished = false;

        internal static List<string> getFiles(string dir, string extension = null)
        {
            List<string> result = new List<string>();
            DirectoryInfo di = new DirectoryInfo(dir);
            foreach (DirectoryInfo _dir in di.GetDirectories())
            {
                result.AddRange(getFiles(_dir.FullName, extension));
            }
            foreach (FileInfo file in di.GetFiles())
            {
                if (!string.IsNullOrWhiteSpace(extension) && file.Extension.Equals(extension))
                {
                    result.Add(file.FullName);
                }
            }
            return result;
        }

        public static void run(Context ctx, dynamic spider)
        {
            Application.ctx = ctx;
            Application.spider = spider;
            Application.configs = (PhpArray)(spider.__get((PhpValue)"configs"));
            bool hasUI = ((PhpValue)Application.configs["UI"]).ToBoolean();
            Thread downloadThread = new Thread(new ParameterizedThreadStart(downloadTask));
            Thread processThread = new Thread(new ParameterizedThreadStart(processTask));
            Thread monitorThread = new Thread(new ParameterizedThreadStart(monitorTask));
            downloadThread.IsBackground = true;
            processThread.IsBackground = true;
            monitorThread.IsBackground = true;
            downloadThread.Start(Application.spider);
            processThread.Start(Application.spider);
            if (hasUI)
            {
                monitorThread.Start(Application.spider);
            }

            // Thread.Sleep(3000);
            // 检测是否已完成任务
            // 有UI的情况下检测finished
            // 无UI的情况下检测downloading和processing
            // FIXME: 有可能存在死循环，异常退出后采集过后的url加不进去，一直没有init
            while (!inited || ((hasUI && !finished) || (!hasUI && (downloading != 0 || processing != 0))))
            {
                Thread.Sleep(500);
            }
            ;
        }

        private static void downloadTask(dynamic spider)
        {
            PhpValue thread = Application.configs["thread"];
            TaskFactory taskFactory = new TaskFactory(new LimitedConcurrencyLevelTaskScheduler((int)thread));
            Action<object> action = o => {
                try
                {
                    spider.fetchUrl((PhpValue)o);
                }
                catch (Pchp.Library.Spl.Exception ex)
                {
                    spider.exceptionHandler(ex);
                }
                catch (System.Exception ex)
                {
                    // TODO: System.Exception to Pchp.Library.Spl.Exception properly
                    // NOTE: 引发CLR异常还继续跑下去可能会导致整个程序运行异常
                    // 将runningFlag置为false，强制不让程序跑不下去
                    spider.exceptionHandler(new Pchp.Library.Spl.Exception(ex.ToString()), false);
                }
                finally {
                    finishDownloadOne();
                }
            };
            while (true)
            {
                dynamic request = (dynamic)(spider.getRequest());
                if (request.IsObject && !request.IsNull)
                {
                    startDownloadOne();
                    // OPTIMIZE: ToClr比较低效，为了避免产生PhpValue，需在PHP代码中做类型限定
                    int timeLimit = (int)(spider.timeLimit("request", request.ToClr().getUrl()).ToClr());
                    if (timeLimit != 0)
                    {
                        Thread.Sleep(timeLimit);
                    }
                    if (!inited)
                    {
                        inited = true;
                    }
                    taskFactory.StartNew(action, request);
                    Thread.Sleep(10);
                } else {
                    Thread.Sleep(50);
                }
            }
        }
        private static void startDownloadOne()
        {
            lock (Application.downloadStatusObj)
            {
                Application.downloading++;
            }
        }
        private static void finishDownloadOne()
        {
            lock (Application.downloadStatusObj)
            {
                Application.downloading--;
                Application.downloaded++;
            }
        }
        private static void startProcessOne()
        {
            lock (Application.processStatusObj)
            {
                Application.processing++;
            }
        }
        private static void finishProcessOne()
        {
            lock (Application.processStatusObj)
            {
                Application.processing--;
                Application.processed++;
            }
        }

        private static void processTask(dynamic spider)
        {
            PhpValue thread = Application.configs["thread"];
            TaskFactory taskFactory = new TaskFactory(new LimitedConcurrencyLevelTaskScheduler((int)thread));
            Action<object> action = o => {
                try
                {
                    spider.processResponse((PhpValue)o);
                }
                catch (Pchp.Library.Spl.Exception ex)
                {
                    spider.exceptionHandler(ex);
                }
                catch (System.Exception ex)
                {
                    // TODO: System.Exception to Pchp.Library.Spl.Exception properly
                    // NOTE: 引发CLR异常还继续跑下去可能会导致整个程序运行异常
                    // 将runningFlag置为false，强制不让程序跑不下去
                    spider.exceptionHandler(new Pchp.Library.Spl.Exception(ex.ToString()), false);
                }
                finally {
                    finishProcessOne();
                }
            };
            while (true)
            {
                dynamic response = spider.getResponse();
                if (response.IsObject && !response.IsNull)
                {
                    startProcessOne();
                    // OPTIMIZE: ToClr比较低效，为了避免产生PhpValue，需在PHP代码中做类型限定
                    int timeLimit = (int)(spider.timeLimit("process", response.ToClr().getRequest().ToClr().getUrl()).ToClr());
                    if (timeLimit != 0)
                    {
                        Thread.Sleep(timeLimit);
                    }
                    taskFactory.StartNew(action, response);
                    Thread.Sleep(10);
                } else {
                    Thread.Sleep(50);
                }
            }
        }

        private static void monitorTask(dynamic spider)
        {
            bool clearSupported = true;
            bool shouldStop = false;
            while (true)
            {
                if (inited && downloading == 0 && processing == 0)
                {
                    shouldStop = true;
                }
                PhpArray monitor = (PhpArray)(spider.__get((PhpValue)"monitor"));
                string downloaded = monitor["downloaded"].ToString();
                string processed = monitor["processed"].ToString();
                string error = monitor["error"].ToString();
                string exception = monitor["exception"].ToString();
                if (clearSupported)
                {
                    try
                    {
                        Console.Clear();
                    }
                    catch {
                        clearSupported = false;
                    }
                }
                Console.WriteAscii("NULASpider");
                string table = ConsoleTableBuilder
                    .From(new List<List<object>> {
                        new List<object> { downloaded, processed, error, exception },
                    })
                    .WithFormat(ConsoleTableBuilderFormat.Alternative)
                    .WithColumn(
                        new List<string> { "downloaded", "processed", "error", "exception" }
                    )
                    .Export().ToString();
                Console.WriteLine(table, Color.Red);
                // Console.WriteWithGradient(table,Color.Red,Color.Blue);
                Thread.Sleep(300);
                if (shouldStop) {
                    finished = true;
                    break;
                }
            }
        }
    }
}
