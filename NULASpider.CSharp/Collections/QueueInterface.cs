using Pchp.Core;
using Pchp.Library.Spl;

namespace nulastudio.Collections
{
    public interface QueueInterface : Countable
    {
        /// <summary>
        /// 弹出队列元素
        /// </summary>
        /// <returns>队列元素，空队列返回null</returns>
        PhpValue pop();
        /// <summary>
        /// 压入队列元素
        /// </summary>
        /// <param name="value">压入元素</param>
        void push(PhpValue value);
        /// <summary>
        /// 判断元素是否存在
        /// </summary>
        /// <param name="value">判断元素</param>
        /// <returns></returns>
        bool exists(PhpValue value);
        /// <summary>
        /// 获取队列元素
        /// </summary>
        /// <returns>队列元素，空队列返回null</returns>
        PhpValue peek();
        /// <summary>
        /// 获取队列元素个数
        /// </summary>
        /// <returns></returns>
        new long count();
        /// <summary>s
        /// 清空队列
        /// </summary>
        void empty();
    }
}
