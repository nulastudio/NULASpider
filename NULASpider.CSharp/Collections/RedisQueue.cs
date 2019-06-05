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
        private CSRedisClient CSRedis;
        private string key;

        public RedisQueue(string connString, PhpArray exConfig = null)
        {
            var segments = UriUtil.parseUrl(connString);
            if ("redis" != segments["scheme"])
            {
                throw new Pchp.Library.Spl.Exception("only redis connection accepted.");
            }
            if (string.IsNullOrEmpty(segments["host"].String))
            {
                throw new Pchp.Library.Spl.Exception("invalid redis connection.");
            }
            var port = 6379;
            if (segments["port"])
            {
                port = segments["port"].ToInt();
            }
            var db = 0;
            var key = "";
            if (segments["path"])
            {
                var dbkey = segments["path"].String?.Replace("/db","").Split('/');
                db = int.Parse(dbkey[0]);
                key = dbkey[1] ?? "";
                this.key = key;
            }
            Dictionary<string, string> @params = new Dictionary<string, string>();
            if (segments["query"])
            {
                foreach (var item in segments["query"].AsArray())
                {
                    @params[item.Key.String] = item.Value.String ?? "";
                }
            }
            @params["defaultDatabase"] = db.ToString();
            StringBuilder paramString = new StringBuilder("");
            foreach (var query in @params)
            {
                paramString.Append($",{query.Key}={query.Value}");
            }
            this.CSRedis = new CSRedisClient($"{segments["host"].String}:{port}{paramString}");
        }

        public PhpValue pop()
        {
            if (this.count() == 0)
            {
                throw new QueueException("The Queue is empty.");
            }
            return this.CSRedis.LPop(this.key);
        }
        public void push(PhpValue value)
        {
            this.CSRedis.RPush(this.key, value.String);
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
                var contains = this.CSRedis.LRange(this.key, start, start + 500 - 1).Contains(value.String);
                if (contains)
                {
                    return true;
                }
            }
            return false;
        }
        public PhpValue peek()
        {
            var count = this.count();
            if (count == 0)
            {
                throw new QueueException("The Queue is empty.");
            }
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
    }
}
