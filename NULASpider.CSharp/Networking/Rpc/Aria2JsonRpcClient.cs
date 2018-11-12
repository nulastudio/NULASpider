using System;
using Pchp.Core;
using Pchp.Library;
using EdjCase.JsonRpc.Client;
using System.Collections.Generic;
using System.Linq;
using EdjCase.JsonRpc.Core;

[assembly: PhpExtension]
namespace nulastudio.Networking.Rpc
{
    [PhpType]
    public class Aria2JsonRpcClient : JsonRpcClient
    {
        protected PhpString _token = PhpString.Empty;

        public Aria2JsonRpcClient(Context ctx, PhpString url) : this(ctx, url, PhpString.Empty)
        {}

        public Aria2JsonRpcClient(Context ctx, PhpString url, PhpString token) : base(ctx, url)
        {
            this._token = token;
        }

        public override PhpValue __call(Context ctx, PhpString name, PhpArray args)
        {
            PhpString fName = new PhpString(string.Format("aria2.{0}", name.ToString(ctx)));
            if (!this._token.IsEmpty)
            {
                PhpString token =  new PhpString(string.Format("token:{0}", this._token.ToString(ctx)));
                Arrays.array_unshift(args, PhpValue.Create(token));
                return this.call(ctx, fName, args);
            }
            return this.call(ctx, fName, args);
        }
    }
}
