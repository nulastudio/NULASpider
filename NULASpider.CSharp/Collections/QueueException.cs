using System;
using Pchp.Library.Spl;

namespace nulastudio.Collections
{
    public class QueueException : Pchp.Library.Spl.Exception
    {
        public QueueException(string message = "", long code = 0, Throwable previous = null): base(message, code, previous)
        {
        }
    }
}
