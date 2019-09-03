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
    public class RedisUniqueQueue : QueueInterface
    {
        private Context ctx;
        private CSRedisClient CSRedis;
        private string key;
        private ulong ticktock;

        public RedisUniqueQueue(Context ctx, string connString, PhpArray exConfig = null)
        {
            this.ctx = ctx;

            var components = nulastudio.Util.RedisHelper.parseConnectionString(connString);
            this.key = components["key"].String;
            this.CSRedis = new CSRedisClient(components["connectionString"].String);

            // 查询ticktock
            var ticktocks = this.CSRedis.ZRangeWithScores(this.key, 0, 0);
            if (ticktocks != null && ticktocks.Length > 0 && ticktocks[0].score >= 0)
            {
                this.ticktock = (ulong)ticktocks[0].score;
            }
            else
            {
                this.ticktock = (ulong)(DateTime.UtcNow - new DateTime(1970, 1, 1, 0, 0, 0, 0)).TotalMilliseconds;
            }
        }

        public PhpValue pop()
        {
            var val = this.peek();
            return this.CSRedis.ZRem<string>(this.key, val.ToString(this.ctx)) >= 1 ? val : PhpValue.Null;
        }
        public bool push(PhpValue value)
        {
            if (!this.exists(value))
            {
                this.ticktock++;
                return this.CSRedis.ZAdd(this.key, (this.ticktock, value.ToString(this.ctx))) == 1;
            }
            return false;
        }
        public bool exists(PhpValue value)
        {
            return this.CSRedis.ZRank(this.key, value.ToString(this.ctx)).HasValue;
        }
        public PhpValue peek()
        {
            var count = this.count();
            var val = this.CSRedis.ZRange(this.key, 0, 0);
            return val != null && val.Length > 0 ? val[0] : PhpValue.Null;
        }
        public long count()
        {
            return this.CSRedis.ZCard(this.key);
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
