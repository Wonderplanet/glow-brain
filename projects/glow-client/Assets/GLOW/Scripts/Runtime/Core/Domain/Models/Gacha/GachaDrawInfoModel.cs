using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaDrawInfoModel(
        MasterDataId GachaId,
        GachaType GachaType,
        GachaDrawType GachaDrawType,
        CostType CostType
    )
    {
        public static GachaDrawInfoModel Empty { get; } = new GachaDrawInfoModel(
            GachaId: MasterDataId.Empty,
            GachaType: GachaType.Normal,
            GachaDrawType: GachaDrawType.Single,
            CostType: CostType.Coin);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
        
        public static GachaDrawInfoModel CreateTutorialDrawInfoModel(MasterDataId gachaId)
        {
            return new GachaDrawInfoModel(
                GachaId: gachaId,
                GachaType: GachaType.Tutorial,
                GachaDrawType: GachaDrawType.Multi,
                CostType: CostType.Diamond);
        }
    }
}
