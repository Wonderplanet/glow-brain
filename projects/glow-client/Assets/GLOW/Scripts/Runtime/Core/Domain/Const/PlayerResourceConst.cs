using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Const
{
    public static class PlayerResourceConst
    {
        public static MasterDataId DailyBonusPointMasterDataId { get; } = new("DailyBonusPoint");
        public static MasterDataId WeeklyBonusPointMasterDataId { get; } = new("WeeklyBonusPoint");
        public static MasterDataId BeginnerBonusPointMasterDataId { get; } = new("BeginnerBonusPoint");
    }
}
