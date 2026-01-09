using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestTop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class EventInitialSelectStageFactory : IEventInitialSelectStageFactory
    {
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public MasterDataId Create(
            MasterDataId mstQuestGroupId,
            IReadOnlyList<EventQuestTopElementModel> models,
            ShowStageReleaseAnimation showStageReleaseAnimation)
        {
            // 開放演出ある > 開放演出のステージがターゲット
            if (showStageReleaseAnimation.ShouldShow)
            {
                var model = models
                    .FirstOrDefault(s =>
                            s.MstStageId == showStageReleaseAnimation.TargetMstStageId,
                        EventQuestTopElementModel.Empty);
                if (!model.IsEmpty()) return model.MstStageId;
            }

            // 最新の新規ステージがあればそれをターゲット
            if (TryGetLatestNewStageMstStageId(models, out MasterDataId latestNewStageMstStageId))
            {
                return latestNewStageMstStageId;
            }

            // 前回選択したステージがある > 前回選択ステージがターゲット
            if (TryGetLastPlayedMstQuestId(mstQuestGroupId, out MasterDataId lastPlayedMstStageId))
            {
                return lastPlayedMstStageId;
            }

            // それ以外 > 最後のステージがターゲット
            return models.LastOrDefault(m => m.StageReleaseStatus.Value == StageStatus.Released)?.MstStageId
                   ?? MasterDataId.Empty;
        }

        bool TryGetLatestNewStageMstStageId(IReadOnlyList<EventQuestTopElementModel> models, out MasterDataId result)
        {
            result = MasterDataId.Empty;
            var targetModel = models
                .LastOrDefault(s =>
                    s.StageClearStatus == StageClearStatus.New &&
                    s.StageReleaseStatus.Value == StageStatus.Released);

            if (targetModel != null)
            {
                result = targetModel.MstStageId;
            }

            return targetModel != null;
        }

        bool TryGetLastPlayedMstQuestId(
            MasterDataId mstQuestGroupId,
            out MasterDataId result)
        {
            result = MasterDataId.Empty;
            var lastPlayedMstStageId = PreferenceRepository.LastPlayedEventAtMstQuestGroupIds
                .FirstOrDefault(m => m.Key == mstQuestGroupId).Value;

            if (lastPlayedMstStageId == null)
            {
                return false;
            }

            var mstStageModel = MstStageDataRepository.GetMstStageFirstOrDefault(lastPlayedMstStageId);
            result = mstStageModel.Id;
            if (mstStageModel.IsEmpty()) return false;

            return CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mstStageModel.StartAt, mstStageModel.EndAt);
        }
    }
}
