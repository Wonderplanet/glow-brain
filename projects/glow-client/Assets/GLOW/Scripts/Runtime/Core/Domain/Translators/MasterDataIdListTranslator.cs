using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Translators
{
    public static class MasterDataIdListTranslator
    {
        /// <summary>
        /// "[id1,id2,id3,...]"という文字列をMasterDataIdのリストにする
        /// 空文字列または"All"の場合は空のリストを返す（絞り込みなし）
        /// </summary>
        public static IReadOnlyList<MasterDataId> ToMasterDataIdList(string arrayString)
        {
            if (string.IsNullOrEmpty(arrayString)) return Array.Empty<MasterDataId>();

            // "All"の場合も空リストを返す（全対象=絞り込みなし）
            if (arrayString == "All") return Array.Empty<MasterDataId>();

            return arrayString
                .Trim('[', ']')
                .Split(',')
                .Select(element => new MasterDataId(element.Trim()))
                .Distinct()
                .ToArray();
        }
    }
}

