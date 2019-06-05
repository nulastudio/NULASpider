using System;
using Pchp.Core;
using Pchp.Library.Spl;

namespace nulastudio.Collections
{
    public interface QueueInterface : Countable
    {
        PhpValue pop();
        void push(PhpValue value);
        bool exists(PhpValue value);
        PhpValue peek();
        new long count();
        void empty();
    }
}
