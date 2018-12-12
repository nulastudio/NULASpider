using System;
using System.Threading;
using System.Collections.Concurrent;
using System.Collections.Generic;
using Pchp.Core;

namespace nulastudio.Threading
{
    sealed public class LockManager
    {
        private static object _lockCheck = new object();
        private static object _lockSelf = new object();
        private static object _releaseSelf = new object();
        private static ConcurrentDictionary<string, object> _locks = new ConcurrentDictionary<string, object>();

        public static void getLock(string LockName)
        {
            lock (_lockSelf)
            {
                if (!_locks.ContainsKey(LockName))
                {
                    _locks.TryAdd(LockName, new object());
                }
            }
            object _lock = _locks[LockName];
            Monitor.Enter(_lock);
        }
        public static void releaseLock(string LockName)
        {
            try
            {
                object _lock = _locks[LockName];
                Monitor.Exit(_lock);
            } catch {}
        }
    }
}
