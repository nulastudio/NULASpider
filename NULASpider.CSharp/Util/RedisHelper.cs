using CSRedis;
using Pchp.Core;
using System.Collections.Generic;
using System.Text;

namespace nulastudio.Util
{
    public class RedisHelper
    {
        public static PhpArray parseConnectionString(string connString)
        {
            var components = new PhpArray();
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
            // if (segments["path"])
            // {
            //     var dbkey = segments["path"].String?.Replace("/db", "").Split('/');
            //     db = int.Parse(dbkey[0]);
            //     key = dbkey[1] ?? "";
            // }
            if (segments["query"]["key"])
            {
                key = segments["query"]["key"].String;
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
            components["host"] = segments["host"];
            components["port"] = port;
            components["db"] = db;
            components["key"] = key;
            components["params"] = segments["query"];
            components["connectionString"] = $"{segments["host"]}:{port}{paramString}";

            return components;
        }
    }
}
