using System;
using System.Collections.Generic;
using System.Linq;

namespace GLOW.Core.Extensions
{
    // ReSharper disable once InconsistentNaming
    public static class IReadOnlyListExtension
    {
        public static T Find<T>(this IReadOnlyList<T> list, Predicate<T> predicate)
        {
            for (int i = 0; i < list.Count; i++)
            {
                if (predicate(list[i])) return list[i];
            }
            return default(T);
        }

        public static IReadOnlyList<T> FindAll<T>(this IReadOnlyList<T> list, Predicate<T> predicate)
        {
            var result = new List<T>();
            for (int i = 0; i < list.Count; i++)
            {
                if (predicate(list[i])) result.Add(list[i]);
            }
            return result;
        }

        public static int FindIndex<T>(this IReadOnlyList<T> list, Predicate<T> predicate)
        {
            for (int i = 0; i < list.Count; i++)
            {
                if (predicate(list[i])) return i;
            }
            return -1;
        }

        public static int IndexOf<T>(this IReadOnlyList<T> list, T item)
        {
            return list.ToList().IndexOf(item);
        }

        public static bool Containts<T>(this IReadOnlyList<T> list, T item)
        {
            return list.ToList().Contains(item);
        }

        public static bool Exists<T>(this IReadOnlyList<T> list, Predicate<T> predicate)
        {
            for (int i = 0; i < list.Count; i++)
            {
                if (predicate(list[i])) return true;
            }
            return false;
        }

        public static bool IsEmpty<T>(this IReadOnlyList<T> list)
        {
            return list.Count == 0;
        }
        
        public static IReadOnlyList<T> Replace<T>(this IReadOnlyList<T> list, T targetItem, T newItem)
        {
            var index = list.IndexOf(targetItem);
            if (index == -1) return list;

            var result = list.ToList();
            result[index] = newItem;
            return result;
        }
        
        public static IReadOnlyList<T> ReplaceAt<T>(this IReadOnlyList<T> list, int listIndex, T newItem)
        {
            if (listIndex == -1) return list;
            if (listIndex >= list.Count) return list;
            
            var result = list.ToList();
            result[listIndex] = newItem;
            return result;
        }
        
        public static IReadOnlyList<T> ReplaceOrAdd<T>(this IReadOnlyList<T> list, Predicate<T> predicate, T item)
        {
            int index = -1;
            for (int i = 0; i < list.Count; i++)
            {
                if (predicate(list[i]))
                {
                    index = i;
                    break;
                }
            }
            
            // 見つからない場合は追加
            if (index == -1) return list.ToList().ChainAdd(item);
            
            // 見つかった場合は置き換え
            var result = list.ToList();
            result[index] = item;
            return result;
        }
        
    }
}
