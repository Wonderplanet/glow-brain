using System.Collections.Generic;

namespace GLOW.Core.Extensions
{
    public static class ListExtension
    {
        /// <summary>
        /// ListにAddして自身のListを返す
        /// </summary>
        /// <param name="list"></param>
        /// <param name="item"></param>
        /// <typeparam name="T"></typeparam>
        /// <returns></returns>
        public static List<T> ChainAdd<T>(this List<T> list, T item)
        {
            list.Add(item);
            return list;
        }

        public static List<T> Replace<T>(this List<T> list, T targetItem, T newItem)
        {
            int index = list.IndexOf(targetItem);
            if (index == -1) return list;
            
            list[index] = newItem;
            return list;
        }
    }
}