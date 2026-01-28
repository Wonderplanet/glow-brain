using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.BoxGacha.Domain.Model
{
    public record BoxGachaDrawResultCellModel(
        CommonReceiveResourceModel BoxGachaReward,
        IsNewUnitBadge IsNewUnitBadge)
    {
        public static BoxGachaDrawResultCellModel Empty { get; } = new(
            CommonReceiveResourceModel.Empty,
            IsNewUnitBadge.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}