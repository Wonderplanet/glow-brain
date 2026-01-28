using System;
using System.Collections.Generic;
using System.Linq;

namespace GLOW.Core.Extensions
{
    // ReSharper disable once InconsistentNaming
    public static class IEnumerableExtension
    {
        public static TSource FirstOrDefault<TSource>(
            this IEnumerable<TSource> source, 
            Func<TSource, bool> predicate, 
            TSource defaultValue)
        {
            foreach (TSource value in source)
            {
                if (predicate(value))
                {
                    return value;
                }
            }

            return defaultValue;
        }
        
        public static TSource FirstOrDefault<TSource>(this IEnumerable<TSource> source, TSource defaultValue)
        {
            using var enumerator = source.GetEnumerator();
            return enumerator.MoveNext() ? enumerator.Current : defaultValue;
        }
        
        public static TSource LastOrDefault<TSource>(this IEnumerable<TSource> source, TSource defaultValue)
        {
            TSource result = defaultValue;
            foreach (var item in source)
            {
                result = item;
            }
            return result;
        }
        
        public static TSource ElementAtOrDefault<TSource>(this IEnumerable<TSource> source, int index, TSource defaultValue)
        {
            if (index < 0) return defaultValue;
            
            int currentIndex = 0;
            foreach (var item in source)
            {
                if (currentIndex == index) return item;
                currentIndex++;
            }
            return defaultValue;
        }

        /// <summary>
        /// 最大値を持つ要素を返す
        /// https://github.com/dotnet/runtime/blob/release/6.0/src/libraries/System.Linq/src/System/Linq/Max.cs
        /// </summary>
        /// <param name="source"></param>
        /// <param name="keySelector"></param>
        /// <typeparam name="TSource"></typeparam>
        /// <typeparam name="TKey"></typeparam>
        /// <returns></returns>
        public static TSource MaxBy<TSource, TKey>(this IEnumerable<TSource> source, Func<TSource, TKey> keySelector)
        {
            return MaxBy(source, keySelector, null);
        }

        public static TSource MaxBy<TSource, TKey>(
            this IEnumerable<TSource> source, 
            Func<TSource, TKey> keySelector, 
            IComparer<TKey> comparer)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            if (keySelector == null)
            {
                throw new ArgumentNullException();
            }

            comparer ??= Comparer<TKey>.Default;

            using IEnumerator<TSource> e = source.GetEnumerator();

            if (!e.MoveNext())
            {
                if (default(TSource) is null)
                {
                    return default;
                }
                else
                {
                    throw new InvalidOperationException();
                }
            }

            TSource value = e.Current;
            TKey key = keySelector(value);

            if (default(TKey) is null)
            {
                while (key == null)
                {
                    if (!e.MoveNext())
                    {
                        return value;
                    }

                    value = e.Current;
                    key = keySelector(value);
                }

                while (e.MoveNext())
                {
                    TSource nextValue = e.Current;
                    TKey nextKey = keySelector(nextValue);
                    if (nextKey != null && comparer.Compare(nextKey, key) > 0)
                    {
                        key = nextKey;
                        value = nextValue;
                    }
                }
            }
            else
            {
                // ReSharper disable once PossibleUnintendedReferenceComparison
                if (comparer == Comparer<TKey>.Default)
                {
                    while (e.MoveNext())
                    {
                        TSource nextValue = e.Current;
                        TKey nextKey = keySelector(nextValue);
                        if (Comparer<TKey>.Default.Compare(nextKey, key) > 0)
                        {
                            key = nextKey;
                            value = nextValue;
                        }
                    }
                }
                else
                {
                    while (e.MoveNext())
                    {
                        TSource nextValue = e.Current;
                        TKey nextKey = keySelector(nextValue);
                        if (comparer.Compare(nextKey, key) > 0)
                        {
                            key = nextKey;
                            value = nextValue;
                        }
                    }
                }
            }

            return value;
        }

        public static TSource MaxByBelowUpperLimit<TSource, TKey>(
            this IEnumerable<TSource> source,
            Func<TSource, TKey> keySelector,
            TKey upperLimit)
        {
            return MaxBy(source,
                keySelector,
                upperLimit,
                isAboveValue: false,
                false);
        }

        public static TSource MaxByBelowOrEqualUpperLimit<TSource, TKey>(
            this IEnumerable<TSource> source,
            Func<TSource, TKey> keySelector,
            TKey upperLimit)
        {
            return MaxBy(source,
                keySelector,
                upperLimit,
                isAboveValue: false,
                true);
        }

        public static TSource MaxBy<TSource, TKey>(
            this IEnumerable<TSource> source,
            Func<TSource, TKey> keySelector,
            TKey targetValue,
            bool isAboveValue,
            bool isIncludeZero)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            if (keySelector == null)
            {
                throw new ArgumentNullException();
            }

            var comparer = Comparer<TKey>.Default;

            using IEnumerator<TSource> e = source.GetEnumerator();

            if (!e.MoveNext())
            {
                if (default(TSource) is null)
                {
                    return default;
                }
                else
                {
                    throw new InvalidOperationException();
                }
            }

            TSource value = e.Current;
            TKey key = keySelector(value);

            if (default(TKey) is null)
            {
                while (key == null || !CheckLimit(key, targetValue, isAboveValue, isIncludeZero, comparer))
                {
                    if (!e.MoveNext())
                    {
                        if (key == null) return value;
                        if (default(TSource) is null) return default;
                        throw new InvalidOperationException();
                    }

                    value = e.Current;
                    key = keySelector(value);
                }

                while (e.MoveNext())
                {
                    TSource nextValue = e.Current;
                    TKey nextKey = keySelector(nextValue);

                    if (!CheckLimit(nextKey, targetValue, isAboveValue, isIncludeZero, comparer)) continue;

                    if (nextKey != null && comparer.Compare(nextKey, key) > 0)
                    {
                        key = nextKey;
                        value = nextValue;
                    }
                }
            }
            else
            {
                // ReSharper disable once PossibleUnintendedReferenceComparison
                if (comparer == Comparer<TKey>.Default)
                {
                    while (!CheckLimit(key, targetValue, isAboveValue, isIncludeZero, Comparer<TKey>.Default))
                    {
                        if (!e.MoveNext())
                        {
                            if (default(TSource) is null) return default;
                            throw new InvalidOperationException();
                        }

                        value = e.Current;
                        key = keySelector(value);
                    }

                    while (e.MoveNext())
                    {
                        TSource nextValue = e.Current;
                        TKey nextKey = keySelector(nextValue);

                        if (!CheckLimit(nextKey, targetValue, isAboveValue, isIncludeZero, Comparer<TKey>.Default)) continue;

                        if (Comparer<TKey>.Default.Compare(nextKey, key) > 0)
                        {
                            key = nextKey;
                            value = nextValue;
                        }
                    }
                }
                else
                {
                    while (!CheckLimit(key, targetValue, isAboveValue, isIncludeZero, comparer))
                    {
                        if (!e.MoveNext())
                        {
                            if (default(TSource) is null) return default;
                            throw new InvalidOperationException();
                        }

                        value = e.Current;
                        key = keySelector(value);
                    }

                    while (e.MoveNext())
                    {
                        TSource nextValue = e.Current;
                        TKey nextKey = keySelector(nextValue);

                        if (!CheckLimit(nextKey, targetValue, isAboveValue, isIncludeZero, comparer)) continue;

                        if (comparer.Compare(nextKey, key) > 0)
                        {
                            key = nextKey;
                            value = nextValue;
                        }
                    }
                }
            }

            return value;
        }

        static bool CheckLimit<TKey>(
            TKey sourceValue, 
            TKey target, 
            bool isAboveValue, 
            bool isIncludeZero, 
            IComparer<TKey> comparer)
        {
            comparer ??= Comparer<TKey>.Default;
            if (isAboveValue)
            {
                if (isIncludeZero)
                {
                    return comparer.Compare(sourceValue, target) >= 0;
                }
                return comparer.Compare(sourceValue, target) > 0;
            }
            if (isIncludeZero)
            {
                return comparer.Compare(sourceValue, target) <= 0;
            }
            return comparer.Compare(sourceValue, target) < 0;
        }

        public static TSource MinBy<TSource, TKey>(this IEnumerable<TSource> source, Func<TSource, TKey> keySelector)
        {
            return MinBy(source, keySelector, null);
        }

        public static TSource MinBy<TSource, TKey>(
            this IEnumerable<TSource> source, 
            Func<TSource, TKey> keySelector, 
            IComparer<TKey> comparer)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            if (keySelector == null)
            {
                throw new ArgumentNullException();
            }

            comparer ??= Comparer<TKey>.Default;

            using IEnumerator<TSource> e = source.GetEnumerator();

            if (!e.MoveNext())
            {
                if (default(TSource) is null)
                {
                    return default;
                }
                else
                {
                    throw new InvalidOperationException();
                }
            }

            TSource value = e.Current;
            TKey key = keySelector(value);

            if (default(TKey) is null)
            {
                while (key == null)
                {
                    if (!e.MoveNext())
                    {
                        return value;
                    }

                    value = e.Current;
                    key = keySelector(value);
                }

                while (e.MoveNext())
                {
                    TSource nextValue = e.Current;
                    TKey nextKey = keySelector(nextValue);
                    if (nextKey != null && comparer.Compare(nextKey, key) < 0)
                    {
                        key = nextKey;
                        value = nextValue;
                    }
                }
            }
            else
            {
                // ReSharper disable once PossibleUnintendedReferenceComparison
                if (comparer == Comparer<TKey>.Default)
                {
                    while (e.MoveNext())
                    {
                        TSource nextValue = e.Current;
                        TKey nextKey = keySelector(nextValue);
                        if (Comparer<TKey>.Default.Compare(nextKey, key) < 0)
                        {
                            key = nextKey;
                            value = nextValue;
                        }
                    }
                }
                else
                {
                    while (e.MoveNext())
                    {
                        TSource nextValue = e.Current;
                        TKey nextKey = keySelector(nextValue);
                        if (comparer.Compare(nextKey, key) < 0)
                        {
                            key = nextKey;
                            value = nextValue;
                        }
                    }
                }
            }

            return value;
        }

        public static TSource MinByAboveLowerLimit<TSource, TKey>(
            this IEnumerable<TSource> source,
            Func<TSource, TKey> keySelector,
            TKey upperLimit)
        {
            return MinBy(source,
                keySelector,
                upperLimit,
                isAboveValue: true,
                false);
        }

        public static TSource MinByAboveOrEqualLowerLimit<TSource, TKey>(
            this IEnumerable<TSource> source,
            Func<TSource, TKey> keySelector,
            TKey upperLimit)
        {
            return MinBy(source,
                keySelector,
                upperLimit,
                isAboveValue: true,
                true);
        }

        public static TSource MinBy<TSource, TKey>(
            this IEnumerable<TSource> source,
            Func<TSource, TKey> keySelector,
            TKey upperLimit,
            bool isAboveValue,
            bool isIncludeZero)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            if (keySelector == null)
            {
                throw new ArgumentNullException();
            }

            var comparer = Comparer<TKey>.Default;

            using IEnumerator<TSource> e = source.GetEnumerator();

            if (!e.MoveNext())
            {
                if (default(TSource) is null)
                {
                    return default;
                }
                else
                {
                    throw new InvalidOperationException();
                }
            }

            TSource value = e.Current;
            TKey key = keySelector(value);

            if (default(TKey) is null)
            {
                while (key == null || !CheckLimit(key, upperLimit, isAboveValue, isIncludeZero, comparer))
                {
                    if (!e.MoveNext())
                    {
                        if (key == null) return value;
                        if (default(TSource) is null) return default;
                        throw new InvalidOperationException();
                    }

                    value = e.Current;
                    key = keySelector(value);
                }

                while (e.MoveNext())
                {
                    TSource nextValue = e.Current;
                    TKey nextKey = keySelector(nextValue);

                    if (!CheckLimit(nextKey, upperLimit, isAboveValue, isIncludeZero, comparer)) continue;

                    if (nextKey != null && comparer.Compare(nextKey, key) < 0)
                    {
                        key = nextKey;
                        value = nextValue;
                    }
                }
            }
            else
            {
                // ReSharper disable once PossibleUnintendedReferenceComparison
                if (comparer == Comparer<TKey>.Default)
                {
                    while (!CheckLimit(key, upperLimit, isAboveValue, isIncludeZero, Comparer<TKey>.Default))
                    {
                        if (!e.MoveNext())
                        {
                            if (default(TSource) is null) return default;
                            throw new InvalidOperationException();
                        }

                        value = e.Current;
                        key = keySelector(value);
                    }

                    while (e.MoveNext())
                    {
                        TSource nextValue = e.Current;
                        TKey nextKey = keySelector(nextValue);

                        if (!CheckLimit(nextKey, upperLimit, isAboveValue, isIncludeZero, Comparer<TKey>.Default)) continue;

                        if (Comparer<TKey>.Default.Compare(nextKey, key) < 0)
                        {
                            key = nextKey;
                            value = nextValue;
                        }
                    }
                }
                else
                {
                    while (!CheckLimit(key, upperLimit, isAboveValue, isIncludeZero, comparer))
                    {
                        if (!e.MoveNext())
                        {
                            if (default(TSource) is null) return default;
                            throw new InvalidOperationException();
                        }

                        value = e.Current;
                        key = keySelector(value);
                    }

                    while (e.MoveNext())
                    {
                        TSource nextValue = e.Current;
                        TKey nextKey = keySelector(nextValue);

                        if (!CheckLimit(nextKey, upperLimit, isAboveValue, isIncludeZero, comparer)) continue;

                        if (comparer.Compare(nextKey, key) < 0)
                        {
                            key = nextKey;
                            value = nextValue;
                        }
                    }
                }
            }

            return value;
        }

        public static IEnumerable<(TSource current, TSource next)> PairWithNext<TSource>(this IEnumerable<TSource> source)
        {
            using var enumerator = source.GetEnumerator();
            if (!enumerator.MoveNext()) yield break;
            
            TSource current = enumerator.Current;
            while (enumerator.MoveNext())
            {
                TSource next = enumerator.Current;
                yield return (current, next);
                current = next;
            }
        }
    }
}
