using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemDetail.Domain.Models;

namespace GLOW.Scenes.FragmentProvisionRatio.Domain
{
    public record FragmentProvisionRatioUseCaseModel(
        IReadOnlyList<FragmentProvisionRatioItemModel> Items,
        ItemDetailAvailableLocationModel FragmentBoxEarnLocationModel)
    {
        public OutputRatio RatioByRarity(Rarity targetRarity)
        {
            var a = Items.GroupBy(i => i.Rarity);
            var b = a.FirstOrDefault(i => i.Key == targetRarity);
            if (b == null) return OutputRatio.Zero;
            else return new OutputRatio(((decimal)b.Count() / (decimal)Items.Count)*100);
        }
    };

    public record FragmentProvisionRatioItemModel(
        MasterDataId MstUnitId,
        ItemModel ItemModel,
        Rarity Rarity,
        ItemName ItemName,
        OutputRatio OutputRatio);

}
