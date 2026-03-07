using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCase
{
    public class ArtworkAcquisitionRouteUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] IMstExchangeShopDataRepository MstExchangeShopDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstArtworkAcquisitionRouteRepository MstArtworkAcquisitionRouteRepository { get; }

        public ArtworkAcquisitionRouteUseCaseModel CreateArtworkSourceModel(MasterDataId mstArtworkId)
        {
            // 原画の入手経路を取得
            var acquisitionRouteCells = CreateAcquisitionRouteCellModels(mstArtworkId);

            // 原画の入手経路がない場合、原画のかけらリスト情報を取得
            var fragmentCellList = new List<EncyclopediaArtworkFragmentListCellModel>();
            if (!acquisitionRouteCells.Any())
            {
                fragmentCellList = CreateFragmentListCellModels(mstArtworkId).ToList();
            }

            return new ArtworkAcquisitionRouteUseCaseModel(
                fragmentCellList,
                acquisitionRouteCells
                );
        }

        IReadOnlyList<ArtworkAcquisitionRouteCellModel> CreateAcquisitionRouteCellModels(MasterDataId mstArtworkId)
        {
            var mstArtworkAcquisitionRoutes =
                MstArtworkAcquisitionRouteRepository.GetArtworkAcquisitionRouteFirstOrDefault(mstArtworkId);

            return mstArtworkAcquisitionRoutes.AcquisitionRoutes
                .Select(CreateCellModel)
                .ToList();
        }

        ArtworkAcquisitionRouteCellModel CreateCellModel(ArtworkAcquisitionRoute route)
        {
            if (route.Type == ArtworkAcquisitionRouteType.Exchange)
            {
                var exchangeShopName =
                    MstExchangeShopDataRepository.GetTradeContents()
                        .FirstOrDefault(shop => shop.Id == route.AcquisitionId, MstExchangeModel.Empty)
                        .Name;

                return new ArtworkAcquisitionRouteCellModel(
                    new ArtworkAcquisitionRouteName(exchangeShopName.Value),
                    route.Type);
            }
            else if (route.Type == ArtworkAcquisitionRouteType.UnitGrade)
            {
                var unitName =
                    MstCharacterDataRepository.GetCharacter(route.AcquisitionId).Name;

                return new ArtworkAcquisitionRouteCellModel(
                    new ArtworkAcquisitionRouteName(unitName.Value),
                    route.Type);
            }

            return new ArtworkAcquisitionRouteCellModel(
                ArtworkAcquisitionRouteName.Empty,
                route.Type);
        }

        IReadOnlyList<EncyclopediaArtworkFragmentListCellModel> CreateFragmentListCellModels(MasterDataId mstArtworkId)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var gameFetch = GameRepository.GetGameFetch();
            var mstStages = MstStageDataRepository.GetMstStages();
            var mstArtworkFragments =
                MstArtworkFragmentDataRepository.GetArtworkFragments(mstArtworkId);

            var stageClearCountable = new List<IStageClearCountable>();
            stageClearCountable.AddRange(gameFetch.StageModels);
            stageClearCountable.AddRange(gameFetch.UserStageEventModels);

            return mstArtworkFragments
                .Select(model =>
                {
                    var isReleasedFragment = gameFetchOther.UserArtworkFragmentModels
                        .Any(fragment => fragment.MstArtworkFragmentId == model.Id);
                    return CreateFragmentListCellModel(
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
        }

        EncyclopediaArtworkFragmentListCellModel CreateFragmentListCellModel(
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
                conditionText = GetConditionText(mstQuest, dropStage);
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

        ArtworkFragmentConditionText GetConditionText(MstQuestModel mstQuest, MstStageModel mstStage)
        {
            if (mstQuest.MstEventId.IsEmpty())
            {
                return new ArtworkFragmentConditionText(ZString.Format("{0} {1} {2}",
                    mstQuest.Name.Value,
                    mstStage.StageNumber.ToSentenceString(),
                    DifficultyToStringConverter.DifficultyToString(mstQuest.Difficulty)));
            }
            else
            {
                return new ArtworkFragmentConditionText(ZString.Format("{0} {1} {2}",
                    "イベントクエスト",
                    mstQuest.Name.Value,
                    mstStage.StageNumber.ToSentenceString()));
            }
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
