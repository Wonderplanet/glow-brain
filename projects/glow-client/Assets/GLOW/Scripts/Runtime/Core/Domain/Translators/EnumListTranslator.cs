using System;
using System.Collections.Generic;
using System.Linq;

namespace GLOW.Core.Domain.Translators
{
    public static class EnumListTranslator
    {
        /// <summary>
        /// "[要素1,要素2,要素3,...]"という文字列をT型のenumのリストにする
        /// "All"の場合はenumの全要素を含むリストを返す
        /// </summary>
        public static IReadOnlyList<T> ToEnumList<T>(string arrayString) where T : struct
        {
            if (string.IsNullOrEmpty(arrayString)) return Array.Empty<T>();

            if (arrayString == "All")
            {
                return (T[])Enum.GetValues(typeof(T));
            }

            return arrayString
                .Trim('[', ']')
                .Split(',')
                .Select(element => (T)Enum.Parse(typeof(T), element))
                .Distinct()
                .ToArray();
        }
    }
}
