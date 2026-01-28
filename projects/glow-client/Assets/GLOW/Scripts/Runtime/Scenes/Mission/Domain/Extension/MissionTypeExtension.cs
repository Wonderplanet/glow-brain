using GLOW.Core.Domain.Const;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Mission.Domain.Extension
{
    public static class MissionTypeExtension
    {
        public static bool ExistBonusPointMission(this MissionType missionType)
        {
            return missionType switch
            {
                MissionType.Beginner => true,
                MissionType.Daily => true,
                MissionType.Weekly => true,
                MissionType.DailyBonus => true,
                _ => false
            };
        }


        public static MasterDataId ToBonusPointMasterDataId(this MissionType missionType)
        {
            return missionType switch
            {
                MissionType.Daily => PlayerResourceConst.DailyBonusPointMasterDataId,
                MissionType.Weekly => PlayerResourceConst.WeeklyBonusPointMasterDataId,
                MissionType.Beginner => PlayerResourceConst.BeginnerBonusPointMasterDataId,
                _ => MasterDataId.Empty
            };
        }
    }
}
