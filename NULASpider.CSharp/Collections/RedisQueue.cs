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
    public class RedisQueue : QueueInterface
    {
        private Context ctx;
        private CSRedisClient CSRedis;
        private string key;

        public RedisQueue(Context ctx, string connString, PhpArray exConfig = null)
        {
            this.ctx = ctx;
            var components = nulastudio.Util.RedisHelper.parseConnectionString(connString);
            this.key = components["key"].String;
            this.CSRedis = new CSRedisClient(components["connectionString"].String);
        }

        public PhpValue pop()
        {
            return this.CSRedis.LPop(this.key);
        }
        public bool push(PhpValue value)
        {
            return this.count() == this.CSRedis.RPush(this.key, value.ToString(this.ctx)) - 1;
        }
        public bool exists(PhpValue value)
        {
            // TODO: 性能问题
            var count = this.count();
            if (count == 0)
            {
                return false;
            }
            for (int i = 0; i < Math.Ceiling(count / 500d); i++)
            {
                var start = i * 500;
                var contains = this.CSRedis.LRange(this.key, start, start + 500 - 1).Contains(value.ToString(this.ctx));
                if (contains)
                {
                    return true;
                }
            }
            return false;
        }
        public PhpValue peek()
        {
            return this.CSRedis.LIndex(this.key, 0);
        }
        public long count()
        {
            return this.CSRedis.LLen(this.key);
        }
        public void empty()
        {
            this.CSRedis.Del(this.key);
        }
        // NOTE: 存储的时候无法存储对象，只能先进行序列化再存储
        public PhpString serialize(PhpValue value)
        {
            // NOTE: base64后体积会变大很多
            var serialize = Pchp.Library.PhpSerialization.serialize(this.ctx, default(RuntimeTypeHandle), value);
            var data = Pchp.Library.Web.base64_encode(this.ctx, serialize);
            return new PhpString(data);
        }
        public PhpValue unserialize(PhpString str)
        {
            // NOTE: base64后体积会变大很多
            var data = Pchp.Library.Web.base64_decode(str.ToString(this.ctx));
            var unserialize = Pchp.Library.PhpSerialization.unserialize(this.ctx, default(RuntimeTypeHandle), data);
            return unserialize;
        }
    }
}
