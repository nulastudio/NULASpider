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
        private static ConcurrentDictionary<string, bool> _lockings = new ConcurrentDictionary<string, bool>();

        public static void getLock(string LockName)
        {
            return;
            Monitor.Enter(_lockSelf);
            if (!_locks.ContainsKey(LockName))
            {
                _locks.TryAdd(LockName, new object());
            }
            object _lock = _locks[LockName];
            Monitor.Enter(_lock);
            if (!_lockings.ContainsKey(LockName))
            {
                _lockings.TryAdd(LockName, true);
            }
            Monitor.Exit(_lockSelf);
        }
        public static void releaseLock(string LockName)
        {
            return;
            Monitor.Enter(_releaseSelf);
            try
            {
                if (_lockings.ContainsKey(LockName))
                {
                    _lockings[LockName] = false;
                }
                object _lock = _locks[LockName];
                Monitor.Exit(_lock);
            }
            finally
            {
                Monitor.Exit(_releaseSelf);
            }
        }
    }
}