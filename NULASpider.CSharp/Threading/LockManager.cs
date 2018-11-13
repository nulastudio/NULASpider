using System;
using System.Threading;
using System.Collections.Generic;
using Pchp.Core;

namespace nulastudio.Threading
{
    sealed public class LockManager
    {
        private static object _lockSelf = new object();
        private static Dictionary<string, object> _locks = new Dictionary<string, object>();

        public static void getLock(string LockName)
        {
            Monitor.Enter(_lockSelf);
            if (!_locks.ContainsKey(LockName))
            {
                _locks.Add(LockName, new object());
            }
            object _lock = _locks[LockName];
            Monitor.Enter(_lock);
            Monitor.Exit(_lockSelf);
        }
        public static void releaseLock(string LockName)
        {
            Monitor.Enter(_lockSelf);
            try
            {
                object _lock = _locks[LockName];
                Monitor.Exit(_lock);
            }
            finally
            {
                Monitor.Exit(_lockSelf);
            }
        }
    }
}