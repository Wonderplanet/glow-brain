using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Scenes.BoxGachaLineupDialog.Domain.Model
{
    public record BoxGachaLineupCellModel(
        PlayerResourceModel PrizeIconModel,
        BoxGachaPrizeStock PrizeStock)
    {
        public static BoxGachaLineupCellModel Empty { get; } = new BoxGachaLineupCellModel(
            PlayerResourceModel.Empty,
            BoxGachaPrizeStock.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}