using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Scenes.BoxGacha.Domain.Model
{
    public record BoxGachaInfoModel(
        BoxGachaPrizeStock TotalStockCount,
        BoxResetCount BoxResetCount,
        BoxDrawCount CurrentBoxTotalDrawnCount,
        BoxLevel CurrentBoxLevel,
        PlayerResourceModel CostResource,
        CostAmount CostAmount,
        IReadOnlyList<BoxGachaPrizeModel> BoxGachaPrizes,
        RemainingTimeSpan RemainingTimeSpan)
    {
        public static BoxGachaInfoModel Empty { get; } = new(
            BoxGachaPrizeStock.Empty,
            BoxResetCount.Empty,
            BoxDrawCount.Empty,
            BoxLevel.Empty,
            PlayerResourceModel.Empty,
            CostAmount.Empty,
            new List<BoxGachaPrizeModel>(),
            RemainingTimeSpan.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}