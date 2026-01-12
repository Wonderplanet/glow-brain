using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ContentMaintenanceTarget(ContentMaintenanceType Type, MasterDataId Id)
    {
        public static ContentMaintenanceTarget Empty { get; } = new(ContentMaintenanceType.Non, MasterDataId.Empty);

        public static ContentMaintenanceTarget[] AdventBattle { get; } = new[] { new ContentMaintenanceTarget(ContentMaintenanceType.AdventBattle, MasterDataId.Empty) };
        public static ContentMaintenanceTarget[] EnhanceQuest { get; } = new[] { new ContentMaintenanceTarget(ContentMaintenanceType.EnhanceQuest, MasterDataId.Empty) };
        public static ContentMaintenanceTarget[] Pvp { get; } = new[] { new ContentMaintenanceTarget(ContentMaintenanceType.Pvp, MasterDataId.Empty) };

        public static ContentMaintenanceTarget[] Gacha() => new[] { new ContentMaintenanceTarget(ContentMaintenanceType.Gacha, MasterDataId.Empty) };
        public static ContentMaintenanceTarget[] Gacha(MasterDataId gachaId) => new[] { new ContentMaintenanceTarget(ContentMaintenanceType.Gacha, gachaId) };

        public static ContentMaintenanceTarget[] ShopItem { get; } = new[] { new ContentMaintenanceTarget(ContentMaintenanceType.ShopItem, MasterDataId.Empty) };
        public static ContentMaintenanceTarget[] ShopPack { get; } = new[] { new ContentMaintenanceTarget(ContentMaintenanceType.ShopPack, MasterDataId.Empty) };
        public static ContentMaintenanceTarget[] ShopPass { get; } = new[] { new ContentMaintenanceTarget(ContentMaintenanceType.ShopPass, MasterDataId.Empty) };
        public static ContentMaintenanceTarget[] ShopTab(ContentMaintenanceType type) => new[] { new ContentMaintenanceTarget(type, MasterDataId.Empty) };
        public static ContentMaintenanceTarget[] Shop { get; } = ShopItem
            .Concat(ShopPack)
            .Concat(ShopPass)
            .ToArray();

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool HasId()
        {
            return !Id.IsEmpty();
        }
    }
}
