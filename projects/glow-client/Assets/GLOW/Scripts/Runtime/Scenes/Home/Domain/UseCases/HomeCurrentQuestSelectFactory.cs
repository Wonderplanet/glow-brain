using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Providers;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeCurrentQuestSelectFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] IDefaultStageProvider DefaultStageProvider { get; }

        public MasterDataId GetHomeBackgroundMstQuestId(QuestType questType, MasterDataId mstEventId)
        {
            switch(questType)
            {
                case QuestType.Normal:
                    return GetNormalMstQuestId();
                case QuestType.Event:
                    return GetEventMstQuestId(mstEventId);
                default:
                    return GetNormalMstQuestId();
            };
        }

        MasterDataId GetNormalMstQuestId()
        {
            if(GameRepository.GetGameFetch().StageModels.Count == 0) return DefaultStageProvider.GetDefaultStage().MstQuestId;

            var mstStages = MstStageDataRepository.GetMstStages();
            var stageModels = GameRepository.GetGameFetch().StageModels;
            var mstQuests = MstQuestDataRepository.GetMstQuestModels();

            var hasModels = stageModels
                .Select(s => mstStages.First(m => m.Id == s.MstStageId))
                .ToList();

            var releasedMaxSortOrderMstStages = stageModels
                .Where(s => 1 <= s.ClearCount.Value)
                .Select(s => mstStages.First(m => m.Id == s.MstStageId))
                .Where(target => !target.ReleaseRequiredMstStageId.IsEmpty())
                .OrderByDescending(t => t.SortOrder)
                .Select(target => mstStages.LastOrDefault(m => m.ReleaseRequiredMstStageId == target.Id))
                .Where(target => target != null)
                .Where(target => IsReleasedStage(target, target.ReleaseRequiredMstStageId, mstQuests));

            var result = hasModels.Concat(releasedMaxSortOrderMstStages)
                .Distinct(h => h.Id)
                .OrderByDescending(m => m.SortOrder)
                .First()
                .Id;

            return mstStages.First(m => m.Id == result).MstQuestId;
        }

        bool IsReleasedStage(
            MstStageModel mstStageModel,
            MasterDataId releaseRequiredMstStageId, 
            IReadOnlyList<MstQuestModel> mstQuestModels)
        {
            var mstQuestModel = mstQuestModels.FirstOrDefault(m => m.Id == mstStageModel.MstQuestId);
            mstQuestModel ??= MstQuestModel.Empty;
            var isOpened = CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mstQuestModel.StartDate, mstQuestModel.EndDate);
            if (!isOpened) return false;

            return GameRepository.GetGameFetch().StageModels.Exists(s => s.MstStageId == releaseRequiredMstStageId);
        }

        MasterDataId GetEventMstQuestId(MasterDataId mstEventId)
        {
            var models = GetFactoryModel(mstEventId);

            // 表示優先度
            // 1. 1日の挑戦可能回数に限りがあるが、まだ一度も挑戦していない場合のクエスト
            if (TryGetHasLimitQuest(models, out MasterDataId havingLimitMstQuestId))
            {
                return havingLimitMstQuestId;
            }
            // 2. NEW表示されまだ一度も挑戦していないクエスト
            if(TryGetHasNewQuest(models, out MasterDataId havingNewMstQuestId))
            {
                return havingNewMstQuestId;
            }
            // 3. 最後に遊んだクエスト
            if(TryGetLastPlayedQuestId(out MasterDataId havingLastPlayedMstQuestId))
            {
                return havingLastPlayedMstQuestId;
            }
            // 4. それ以外(デフォルト)
            return GetDefaultMstQuestId(mstEventId);
        }

        IReadOnlyList<HomeCurrentQuestSelectFactoryModel> GetFactoryModel(MasterDataId mstEventId)
        {
            var mstQuests = MstQuestDataRepository.GetMstQuestModelsFromEvent(mstEventId);
            return mstQuests
                .Select(m =>
                {
                    var itemModels =
                        MstStageDataRepository
                            .GetMstStagesFromMstQuestId(m.Id)
                            .Select(GetHomeCurrentQuestSelectFactoryItemModel)
                            .ToList();

                    return new HomeCurrentQuestSelectFactoryModel(itemModels);
                })
                .ToList();
        }

        HomeCurrentQuestSelectFactoryItemModel GetHomeCurrentQuestSelectFactoryItemModel(MstStageModel mstStage)
        {
            var eventSetting = MstStageEventSettingDataRepository.GetStageEventSettingFirstOrDefault(mstStage.Id);
            var userModel = GameRepository.GetGameFetch().UserStageEventModels
                .FirstOrDefault(u => u.MstStageId == mstStage.Id);

            userModel ??= UserStageEventModel.Empty;

            StagePlayableFlag stagePlayable;
            if(mstStage.ReleaseRequiredMstStageId.IsEmpty()) stagePlayable = StagePlayableFlag.True;
            else
            {
                stagePlayable = GameRepository.GetGameFetch().UserStageEventModels
                    .Exists(u => u.MstStageId == mstStage.ReleaseRequiredMstStageId && u.TotalClearCount.IsCleared)
                    ? StagePlayableFlag.True
                    : StagePlayableFlag.False;
            }
            return new HomeCurrentQuestSelectFactoryItemModel(mstStage,eventSetting, userModel, stagePlayable);

        }

        bool TryGetHasLimitQuest(IReadOnlyList<HomeCurrentQuestSelectFactoryModel> models, out MasterDataId havingLimitMstQuestId)
        {
            havingLimitMstQuestId = MasterDataId.Empty;
            var hasLimitStageModel =  models
                .SelectMany(m => m.Items)
                .FirstOrDefault(m =>
                {
                    if (m.MstStageEventSettingModel.ClearableCount.IsEmpty()) return false;

                    if (m.UserStageEventModel.IsEmpty()) return true;
                    if (m.UserStageEventModel.LatestResetAt == null) return false;
                    
                    //挑戦回数残っていたら
                    if (m.UserStageEventModel.ResetClearCount < m.MstStageEventSettingModel.ClearableCount) return true;
                    
                    //リセットされていたら
                    if(TimeProvider.Now <= m.UserStageEventModel.LatestResetAt.Value) return true; 

                    return true; //考慮漏れなければ通ることはない
                });

            if(hasLimitStageModel != null)
            {
                havingLimitMstQuestId = hasLimitStageModel.MstStageModel.MstQuestId;
                return true;
            }
            return false;
        }

        bool TryGetHasNewQuest(IReadOnlyList<HomeCurrentQuestSelectFactoryModel> models, out MasterDataId havingNewMstQuestId)
        {
            havingNewMstQuestId = MasterDataId.Empty;
            var hasNewStageModel =  models
                .SelectMany(m => m.Items)
                .FirstOrDefault(m => m.UserStageEventModel.IsEmpty() && m.StagePlayable);
            if(hasNewStageModel != null)
            {
                havingNewMstQuestId = hasNewStageModel.MstStageModel.MstQuestId;
                return true;
            }
            return false;
        }

        bool TryGetLastPlayedQuestId(out MasterDataId lastPlayedMstQuestId)
        {
            lastPlayedMstQuestId = MasterDataId.Empty;
            var stageEventModels = GameRepository.GetGameFetch().UserStageEventModels;

            var lastPlayerMstStage = stageEventModels
                .OrderByDescending(u => u.LastChallengedAt) // OrderByDescでnullは最後にくるのでこれだけで対応
                .DefaultIfEmpty(UserStageEventModel.Empty)
                .First();

            lastPlayedMstQuestId = MstStageDataRepository.GetMstStageFirstOrDefault(lastPlayerMstStage.MstStageId).MstQuestId;
            return !lastPlayerMstStage.IsEmpty();
        }

        MasterDataId GetDefaultMstQuestId(MasterDataId mstEventId)
        {
            var mstQuests = MstQuestDataRepository.GetMstQuestModelsFromEvent(mstEventId);
            return mstQuests.First().Id;
        }
    }
}
