using System;
using System.Collections.Generic;
using System.Globalization;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Series;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Helper
{
    public interface ISeriesPrefixWordSortHelper
    {
        IReadOnlyList<SeriesPrefixWordSortModel> PrefixWordSort(IReadOnlyList<MstSeriesModel> mstSeriesModels);
    }

    /// <summary> MstSeriesModelをPrefixWordを元に作品名順（五十音順）に並び替えてindexを足したmodelを返す </summary>
    public class SeriesPrefixWordSortHelper : ISeriesPrefixWordSortHelper
    {
        public IReadOnlyList<SeriesPrefixWordSortModel> PrefixWordSort(IReadOnlyList<MstSeriesModel> mstSeriesModels)
        {
            // 日本語でCulture設定してもA-Z,五十音順でorderByが効く（ローカライズでA-Zでソートする際でも効く認識）
            var comparer = StringComparer.Create(new CultureInfo("ja-JP"), false);

            return mstSeriesModels
                .OrderBy(model => model.PrefixWord != SeriesPrefixWord.Empty ? model.PrefixWord.Value : "んんん", comparer)
                .Select((model, index) => new SeriesPrefixWordSortModel(
                    model.Id,
                    model.Name,
                    model.SeriesAssetKey,
                    model.SeriesBannerAssetKey,
                    model.PrefixWord,
                    model.JumpPlusUrl,
                    new PrefixWordSortOrder(index)))
                .ToList();
        }
    }
}
