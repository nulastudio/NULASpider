using System;
using System.Collections.Concurrent;
using Pchp.Core;

namespace nulastudio.Threading
{
    public class ConcurrentQueue
    {
        private ConcurrentQueue<object> Queue;
        private string className;

        public ConcurrentQueue(string className)
        {
            Queue = new ConcurrentQueue<object>();
            this.className = className.Replace(@"\",".");
        }

        public void Enqueue(object obj)
        {
            if (obj.GetType().ToString().Equals(this.className))
            {
                Queue.Enqueue(obj);
            }
        }

        public object DeQueue()
        {
            object obj;
            if (!Queue.TryDequeue(out obj))
            {
                return null;
            }
            return obj;
        }

        public int Count()
        {
            return Queue.Count;
        }
    }
}
