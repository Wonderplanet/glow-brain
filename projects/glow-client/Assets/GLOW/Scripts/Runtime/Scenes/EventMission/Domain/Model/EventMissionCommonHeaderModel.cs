using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.QuestContent;

namespace GLOW.Scenes.EventMission.Domain.Model
{
    public record EventMissionCommonHeaderModel(
        MasterDataId MstEventId,
        EventMissionBannerAssetPath MissionBannerAssetPath,
        EventMissionDailyBonusBannerAssetPath DailyBonusBannerAssetPath)
    {
        public static EventMissionCommonHeaderModel Empty { get; } = new (
            MasterDataId.Empty,
            EventMissionBannerAssetPath.Empty,
            EventMissionDailyBonusBannerAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}