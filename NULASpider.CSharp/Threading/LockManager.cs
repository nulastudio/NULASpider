using System;
using System.Threading;
using System.Collections.Generic;
using Pchp.Core;

namespace nulastudio.Threading
{
    sealed public class LockManager
    {
        private static object _lockCheck = new object();
        private static object _lockSelf = new object();
        private static object _releaseSelf = new object();
        private static Dictionary<string, object> _locks = new Dictionary<string, object>();
        private static Dictionary<string, bool> _lockings = new Dictionary<string, bool>();

        public static void getLock(string LockName)
        {
            lock (_lockCheck)
            {
                while (true)
                {
                    if (!_lockings.ContainsKey(LockName) || !_lockings[LockName])
                    {
                        break;
                    }
                }
            }
            Monitor.Enter(_lockSelf);
            if (!_locks.ContainsKey(LockName))
            {
                _locks.Add(LockName, new object());
            }
            object _lock = _locks[LockName];
            Monitor.Enter(_lock);
            if (!_lockings.ContainsKey(LockName))
            {
                _lockings.Add(LockName, true);
            }
            Monitor.Exit(_lockSelf);
        }
        public static void releaseLock(string LockName)
        {
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