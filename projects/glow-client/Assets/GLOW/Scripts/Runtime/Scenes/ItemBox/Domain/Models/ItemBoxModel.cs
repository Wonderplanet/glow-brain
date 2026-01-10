using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemBox.Domain.Models
{
    public record ItemBoxModel(
        ItemModel ItemModel,
        MstSeriesModel MstSeriesModel)
    {
        public static ItemBoxModel Empty { get; } = new (
            ItemModel.Empty,
            MstSeriesModel.Empty);
    }
}
