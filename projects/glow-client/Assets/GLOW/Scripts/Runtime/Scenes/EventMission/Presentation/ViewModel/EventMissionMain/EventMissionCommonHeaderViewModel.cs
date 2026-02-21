using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.QuestContent;

namespace GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionMain
{
    public record EventMissionCommonHeaderViewModel(
        MasterDataId MstEventId,
        EventMissionBannerAssetPath MissionBannerAssetPath,
        EventMissionDailyBonusBannerAssetPath DailyBonusBannerAssetPath)
    {
        public static EventMissionCommonHeaderViewModel Empty { get; } = new (
            MasterDataId.Empty,
            EventMissionBannerAssetPath.Empty,
            EventMissionDailyBonusBannerAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}