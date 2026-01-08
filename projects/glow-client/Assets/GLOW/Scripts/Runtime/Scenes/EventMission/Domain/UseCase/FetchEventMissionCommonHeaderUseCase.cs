using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.QuestContent;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventMission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public class FetchEventMissionCommonHeaderUseCase
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public EventMissionCommonHeaderModel GetEventMissionCommonHeader(
            MasterDataId mstEventId,
            bool isDisplayedInHome)
        {
            if (isDisplayedInHome)
            {
                return GetRecentEventCommonHeader();
            }
            else
            {
                return GetCommonHeader(mstEventId);
            }
        }

        EventMissionCommonHeaderModel GetCommonHeader(
            MasterDataId mstEventId)
        {
            var mstEventModel = MstEventDataRepository.GetEvent(mstEventId);
            if (mstEventModel.IsEmpty()
                || !CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mstEventModel.StartAt, mstEventModel.EndAt))
            {
                return EventMissionCommonHeaderModel.Empty;
            }

            return new EventMissionCommonHeaderModel(
                mstEventModel.Id,
                EventMissionBannerAssetPath.FromAssetKey(mstEventModel.AssetKey),
                EventMissionDailyBonusBannerAssetPath.FromAssetKey(mstEventModel.AssetKey));
        }

        EventMissionCommonHeaderModel GetRecentEventCommonHeader()
        {
            var mstEventModels = MstEventDataRepository
                .GetEvents()
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt, m.EndAt))
                .ToList();
            if(mstEventModels.Count == 0)
            {
                return EventMissionCommonHeaderModel.Empty;
            }

            // 複数の開催中イベントからミッションを出すときは一番新しいMstEventModelをサンプルとして利用する
            var mstEventModel = mstEventModels.MaxBy(m => m.StartAt);
            return new EventMissionCommonHeaderModel(
                mstEventModel.Id,
                EventMissionBannerAssetPath.FromAssetKey(mstEventModel.AssetKey),
                EventMissionDailyBonusBannerAssetPath.FromAssetKey(mstEventModel.AssetKey));
        }
    }
}
