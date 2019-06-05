using System;
using System.Collections.Generic;
using System.Text;
using System.Web;
using Pchp.Core;

namespace nulastudio.Util
{
    public class UriUtil
    {
        public static PhpArray parseUrl(string url)
        {
            PhpArray segments = new PhpArray();

            try
            {
                Uri uri = new Uri(url);

                segments["scheme"] = uri.Scheme;
                segments["username"] = "";
                segments["password"] = "";
                string[] auth = uri.UserInfo?.Split(':');
                if (auth != null && auth.Length != 0)
                {
                    segments["username"] = auth[0];
                    segments["password"] = auth.Length > 1 ? auth[1] : "";
                }
                segments["host"] = uri.Host;
                segments["path"] = uri.AbsolutePath;
                PhpArray queries = new PhpArray();
                if (!string.IsNullOrEmpty(uri.Query))
                {
                    foreach (var queryPair in uri.Query.TrimStart('?').Split('&'))
                    {
                        var kv = queryPair.Split('=');
                        queries[HttpUtility.UrlDecode(kv[0])] = kv.Length > 1 ?  HttpUtility.UrlDecode(kv[1]) : "";
                    }
                }
                segments["query"] = queries;
                segments["fragment"] = uri.Fragment.TrimStart('#');
            }
            finally {}
            
            return segments;
        }
    }
}
