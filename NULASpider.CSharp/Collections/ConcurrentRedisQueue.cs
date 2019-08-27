using System.Linq;
using System;
using System.Collections.Generic;
using System.Text;
using CSRedis;
using Pchp.Core;
using Pchp.Library.Spl;
using nulastudio.Util;

namespace nulastudio.Collections
{
    public class ConcurrentRedisQueue : RedisQueue
    {
        private static object @lock = new object();

        public ConcurrentRedisQueue(Context ctx, string connString, PhpArray exConfig = null) : base(ctx, connString, exConfig)
        {
        }

        public new PhpValue pop()
        {
            lock (@lock)
            {
                return base.pop();
            }
        }
        public new bool push(PhpValue value)
        {
            lock (@lock)
            {
                return base.push(value);
            }
        }
        public new bool exists(PhpValue value)
        {
            lock (@lock)
            {
                return base.exists(value);
            }
        }
        public new PhpValue peek()
        {
            lock (@lock)
            {
                return base.peek();
            }
        }
        public new long count()
        {
            lock (@lock)
            {
                return base.count();
            }
        }
        public new void empty()
        {
            lock (@lock)
            {
                base.empty();
            }
        }
        public new PhpString serialize(PhpValue value)
        {
            lock (@lock)
            {
                return base.serialize(value);
            }
        }
        public new PhpValue unserialize(PhpString str)
        {
            lock (@lock)
            {
                return base.unserialize(str);
            }
        }
    }
}
