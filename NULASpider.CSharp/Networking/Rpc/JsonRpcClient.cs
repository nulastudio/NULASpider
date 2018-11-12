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
    public class JsonRpcClient
    {
        protected RpcClient _client;

        public JsonRpcClient(Context ctx, PhpString url)
        {
            _client = new RpcClient(new Uri(url.ToString(ctx)));
        }

        public virtual PhpValue __call(Context ctx, PhpString name, PhpArray args)
        {
            return this.call(ctx, name, args);
        }
        public virtual PhpValue call(Context ctx, PhpString name, PhpArray args)
        {
            string responseJson = null;
            try
            {
                // 创建ID
                RpcId rpcId = new RpcId(Guid.NewGuid().ToString());
                // 将List<object>或者Dictionary<string, object>隐式转换成RpcParameters
                RpcParameters rpcParameters = ArrayUtil.array2collection(ctx, args);
                // 构建请求
                RpcRequest rpcRequest = new RpcRequest(rpcId, name.ToString(ctx), rpcParameters);
                // 发送请求
                RpcResponse rpcResponse = _client.SendRequestAsync(rpcRequest, "").Result;
                // 获取响应
                PhpValue returnJson = JsonSerialization.json_decode(ctx, new PhpString(rpcResponse.Result.ToString()), true);
                return PhpValue.Create(new PhpArray() {
                    { new IntStringKey("id"), PhpValue.Create(rpcResponse.Id.StringValue) },
                    { new IntStringKey("jsonrpc"), PhpValue.Create("2.0") },
                    { new IntStringKey("result"), !returnJson.IsArray ? PhpValue.FromClr(rpcResponse.Result) : returnJson },
                });
            }
            catch (Exception ex)
            {
                if (ex.InnerException is RpcClientInvalidStatusCodeException)
                {
                    responseJson = (ex.InnerException as RpcClientInvalidStatusCodeException)?.Content;
                }
            }
            return String.IsNullOrEmpty(responseJson) ? PhpValue.False : JsonSerialization.json_decode(ctx, new PhpString(responseJson), true);
        }
    }
}
