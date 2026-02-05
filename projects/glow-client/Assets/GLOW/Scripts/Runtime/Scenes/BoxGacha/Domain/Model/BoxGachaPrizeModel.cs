using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.BoxGacha.Domain.Model
{
    public record BoxGachaPrizeModel(
        PickUpFlag IsPickUp,
        PlayerResourceModel PrizeResource,
        GachaDrawCount DrawCount,
        BoxGachaPrizeStock Stock)
    {
        public static BoxGachaPrizeModel Empty { get; } = new(
            PickUpFlag.False,
            PlayerResourceModel.Empty,
            GachaDrawCount.Empty,
            BoxGachaPrizeStock.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}