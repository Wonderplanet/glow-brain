using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models
{
    /// <summary> フィルタの作品項目の一覧表示のためのモデル </summary>
    public record SeriesFilterTitleModel(
        MasterDataId Id,
        SeriesLogoImagePath SeriesLogoImagePath,
        SeriesPrefixWord PrefixWord)
    {
        public static SeriesFilterTitleModel Empty { get; } = new SeriesFilterTitleModel(
            MasterDataId.Empty,
            SeriesLogoImagePath.Empty,
            SeriesPrefixWord.Empty);
    }
}
