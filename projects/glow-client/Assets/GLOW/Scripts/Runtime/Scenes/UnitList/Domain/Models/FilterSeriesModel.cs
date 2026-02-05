using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterSeriesModel(IReadOnlyList<MasterDataId> SeriesIds)
    {
        public static FilterSeriesModel Default { get; } = new FilterSeriesModel(new List<MasterDataId>());

        public bool IsAnyFilter => SeriesIds.Count > 0;

        public bool IsOn(MasterDataId seriesId)
        {
            return SeriesIds.Contains(seriesId);
        }
    }
}
