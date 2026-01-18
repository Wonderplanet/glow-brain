using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Extensions;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases
{
    public class GetEncyclopediaArtworkDetailUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }

        public EncyclopediaArtworkDetailModel GetArtworkDetail(MasterDataId mstArtworkId)
        {
            var mstArtwork = MstArtworkDataRepository.GetArtwork(mstArtworkId);
            var gameFetch = GameRepository.GetGameFetch();
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var isRelease = gameFetchOther.UserArtworkModels
                .Any(artwork => artwork.MstArtworkId == mstArtworkId);
            var isAssigned = OutpostArtworkCacheRepository.GetSelectedArtwork() == mstArtworkId;

            var mstArtworkFragments = MstArtworkFragmentDataRepository.GetArtworkFragments(mstArtworkId);
            var mstStages = MstStageDataRepository.GetMstStages();
            List<IStageClearCountable> stageClearCountable = new();
            stageClearCountable.AddRange(gameFetch.StageModels);
            stageClearCountable.AddRange(gameFetch.UserStageEventModels);
            var fragmentCelList = mstArtworkFragments
                .Select(model =>
                {
                    var isReleasedFragment = gameFetchOther.UserArtworkFragmentModels
                        .Any(fragment => fragment.MstArtworkFragmentId == model.Id);
                    return ToTranslateFragmentListCellModel(
                        model,
                        mstStages,
                        gameFetch.UserStageEventModels,
                        stageClearCountable,
                        isReleasedFragment);
                })
                .OrderBy(model => model.StatusFlags.IsCleared)
                .ThenByDescending(model => model.StatusFlags.IsEnableChallenge)
                .ThenBy(model => model.Num.Value)
                .ToList();

            return new EncyclopediaArtworkDetailModel(
                mstArtworkId,
                mstArtwork.Name,
                ArtworkEffectDescription.FromHp(mstArtwork.OutpostAdditionalHp),
                fragmentCelList,
                new ArtworkUnlockFlag(isRelease),
                new EnableArtworkChangeFlag(!isAssigned && isRelease));
        }

        EncyclopediaArtworkFragmentListCellModel ToTranslateFragmentListCellModel(
            MstArtworkFragmentModel mstArtworkFragment,
            IReadOnlyList<MstStageModel> mstStages,
            IReadOnlyList<UserStageEventModel> eventStages,
            IReadOnlyList<IStageClearCountable> stageClearCountable,
            bool isAcquired)
        {
            var dropStage = mstStages
                .FirstOrDefault(
                    mstStage => mstStage.MstArtworkFragmentDropGroupId == mstArtworkFragment.MstDropGroupId,
                    MstStageModel.Empty);
            var dropStageReleaseRequiredStage = mstStages
                .FirstOrDefault(
                    mstStage => mstStage.Id == dropStage.ReleaseRequiredMstStageId,
                    MstStageModel.Empty);
            bool isUnReleaseQuest = dropStage.IsEmpty()
                                   || !StagePlayableEvaluator
                                        .EvaluateNormalStage(dropStageReleaseRequiredStage, stageClearCountable);

            // 開放条件が期間限定クエストだった場合に、現在が期間内かのフラグ。限定クエストでなければfalse;
            var isOutOfTermQuest = false;
            var conditionText = ArtworkFragmentConditionText.Empty;
            var questType = QuestType.Normal;
            if (!dropStage.IsEmpty())
            {
                var mstQuest = MstQuestDataRepository.GetMstQuestModel(dropStage.MstQuestId);
                questType = mstQuest.QuestType;
                conditionText = new ArtworkFragmentConditionText(ZString.Format("{0} {1} {2}",
                    mstQuest.Name.Value,
                    dropStage.StageNumber.ToSentenceString(),
                    DifficultyToStringConverter.DifficultyToString(mstQuest.Difficulty)));
                if (!mstQuest.MstEventId.IsEmpty())
                {
                    isUnReleaseQuest = !IsReleaseEventStage(mstQuest, dropStage, eventStages);
                    var mstEvent = MstEventDataRepository.GetEvent(mstQuest.MstEventId);
                    isOutOfTermQuest =
                        !CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mstEvent.StartAt, mstEvent.EndAt);
                }
            }

            return new EncyclopediaArtworkFragmentListCellModel(
                mstArtworkFragment.Id,
                questType,
                ArtworkFragmentAssetPath.FromAssetKey(ArtworkFragmentAssetKey
                    .ToArtworkFragmentAssetKey(mstArtworkFragment.AssetNum).ToPlayerResourceAssetKey()),
                mstArtworkFragment.Position,
                mstArtworkFragment.Name,
                mstArtworkFragment.Rarity,
                conditionText,
                new ArtworkFragmentStatusFlags(isAcquired, isUnReleaseQuest, isOutOfTermQuest)
            );
        }

        bool IsReleaseEventStage(
            MstQuestModel mstQuest,
            MstStageModel mstStage,
            IReadOnlyList<UserStageEventModel> eventStages)
        {
            var userStageEvent = eventStages
                .FirstOrDefault(stageEvent => stageEvent.MstStageId == mstStage.Id, UserStageEventModel.Empty);
            var mstStageEventSetting =
                MstStageEventSettingDataRepository.GetStageEventSettingFirstOrDefault(mstStage.Id);
            var releaseRequiredMstStageModel =
                MstStageDataRepository.GetMstStageFirstOrDefault(mstStage.ReleaseRequiredMstStageId);
            var releaseRequiredUserStageModel = eventStages
                .FirstOrDefault(stageEvent => stageEvent.MstStageId == mstStage.ReleaseRequiredMstStageId,
                    UserStageEventModel.Empty);
            var campaignModel = CampaignModelFactory.CreateCampaignModel(
                mstQuest.Id,
                CampaignTargetType.EventQuest,
                CampaignTargetIdType.Quest,
                mstQuest.Difficulty,
                CampaignType.ChallengeCount);

            var releaseStatus = StagePlayableEvaluator.EvaluateEventStage(
                TimeProvider.Now,
                mstStage,
                mstStageEventSetting,
                userStageEvent,
                releaseRequiredMstStageModel,
                releaseRequiredUserStageModel,
                campaignModel);
            return releaseStatus.IsReleased;
        }
    }
}
