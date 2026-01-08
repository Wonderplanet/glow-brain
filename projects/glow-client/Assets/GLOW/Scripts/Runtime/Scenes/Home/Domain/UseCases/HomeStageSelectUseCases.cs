using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Providers;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Modules.InAppReview.Domain.ValueObject;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Domain.Evaluator;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeStageSelectUseCases
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IQuestStageReleaseAnimationRepository QuestStageReleaseAnimationRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] IMstStageClearTimeRewardRepository MstStageClearTimeRewardRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IInGameSpecialRuleEvaluator InGameSpecialRuleEvaluator { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] ISelectedStageRepository SelectedStageRepository { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] ISpeedAttackUseCaseModelFactory SpeedAttackUseCaseModelFactory { get; }
        [Inject] IArtworkFragmentCompleteEvaluator ArtworkFragmentCompleteEvaluator { get; }
        [Inject] IShowStageReleaseAnimationFactory ShowStageReleaseAnimationFactory { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IDefaultStageProvider DefaultStageProvider { get; }

        public HomePlayableQuestUseCaseModel UpdateAndGetQuestUseCaseModel()
        {
            var shouldShowAnimationStatus = QuestStageReleaseAnimationRepository.GetForHomeTop();
            var selectedMstStage = SelectedStageEvaluator.GetSelectedStage();

            // 導入・メインチュートリアル中のステージ上書き対応
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            if (!tutorialStatus.IsCompleted())
            {
                var tutorialModel = CreateTutorialModel(tutorialStatus);
                if (!tutorialModel.IsEmpty())
                {
                    return tutorialModel;
                }

                // sortOrderが最も若いクエストを持ってくる(チュートリアルは999)
                var newStage = DefaultStageProvider.GetDefaultStage();

                // チュートリアル完了直前はメインクエストを表示する
                selectedMstStage = SelectedStageModel.Empty;
                shouldShowAnimationStatus = new ShowReleaseAnimationStatus(
                    newStage.MstQuestId,
                    newStage.Id);
            }

            // 新しく解放されるステージがある場合、Getする前に新しいステージに更新する
            if (!shouldShowAnimationStatus.NewReleaseMstStageId.IsEmpty())
            {
                SelectedStageRepository.Save(new SelectedStageModel(
                    shouldShowAnimationStatus.NewReleaseMstStageId, MasterDataId.Empty, ContentSeasonSystemId.Empty));
            }

            // 一度通過するとClearが行われ、連続でメソッドコールするとreturn内容が変わることに注意
            QuestStageReleaseAnimationRepository.DeleteAtNormal();

            var questInformation = GetSelectedStageMstQuestIdAndDifficulty(shouldShowAnimationStatus);
            var stages = CreateStageLists(questInformation.mstQuestId, selectedMstStage.SelectedStageId);

            if (stages.Count == 0)
            {
                // ステージが存在しない場合は、デフォルトのステージを取得
                questInformation = GetDefaultStageMstQuestIdAndDifficulty();
                stages = CreateStageLists(questInformation.mstQuestId, selectedMstStage.SelectedStageId);
            }

            var showQuestReleaseAnimation =
                CreateShowQuestReleaseAnimation(shouldShowAnimationStatus.NewReleaseMstQuestId);
            var showStageReleaseAnimation =
                ShowStageReleaseAnimationFactory.Create(shouldShowAnimationStatus.NewReleaseMstStageId);

            var isInAppReviewDisplay = ShouldDisplayInAppReview(shouldShowAnimationStatus.NewReleaseMstQuestId);

            if (!shouldShowAnimationStatus.NewReleaseMstQuestId.IsEmpty() &&
                MstQuestDataRepository.GetMstQuestModel(shouldShowAnimationStatus.NewReleaseMstQuestId).EndDate <
                TimeProvider.Now)
            {
                //クエスト開放演出したとき、期間内だったら最後に選んだクエストIdを更新する
                UpdateSelectedStageMstQuestId(shouldShowAnimationStatus.NewReleaseMstQuestId);
            }

            var mstQuest = MstQuestDataRepository.GetMstQuestModel(questInformation.mstQuestId);

            var assetPath = QuestImageAssetPath.GetQuestImagePath(mstQuest.AssetKey.Value);
            var shouldShowAnimation = !shouldShowAnimationStatus.NewReleaseMstQuestId.IsEmpty() ||
                                      !shouldShowAnimationStatus.NewReleaseMstStageId.IsEmpty();

            var questLimitTime = mstQuest.EndDate.IsUnlimitedEndAt ||
                                 mstQuest.QuestType == QuestType.Normal ||
                                 mstQuest.QuestType == QuestType.Tutorial
                ? QuestLimitTime.Empty
                : new QuestLimitTime(mstQuest.EndDate - TimeProvider.Now);

            var isDisplayTryStageText = ShouldDisplayTryStageText();

            return new HomePlayableQuestUseCaseModel(
                mstQuest.Id,
                mstQuest.Name,
                assetPath,
                questLimitTime,
                GetInitialSelectMstStageId(shouldShowAnimation, stages, selectedMstStage),
                stages,
                showStageReleaseAnimation,
                showQuestReleaseAnimation,
                questInformation.difficulty,
                isInAppReviewDisplay,
                isDisplayTryStageText);
        }

        HomePlayableQuestUseCaseModel CreateTutorialModel(TutorialStatusModel tutorialStatus)
        {
            if (tutorialStatus.IsMainPart1())
            {
                return CreateModelTutorialMainPart1();
            }
            else if (tutorialStatus.IsMainPart2())
            {
                return CreateModelTutorialMainPart2();
            }

            // メインパートのインゲーム2以降はチュートリアルステージがないのでEmptyを返す
            return HomePlayableQuestUseCaseModel.Empty;
        }

        ShowQuestReleaseAnimation CreateShowQuestReleaseAnimation(MasterDataId newReleaseMstQuestId)
        {
            if (newReleaseMstQuestId.IsEmpty()) return ShowQuestReleaseAnimation.Empty;
            var mstQuestModel = MstQuestDataRepository.GetMstQuestModel(newReleaseMstQuestId);
            // 期間外だったらアニメーション出さない
            if (mstQuestModel.EndDate < TimeProvider.Now)
            {
                return ShowQuestReleaseAnimation.Empty;
            }

            return new ShowQuestReleaseAnimation(
                true,
                mstQuestModel.Name,
                QuestImageAssetPath.GetQuestImagePath(mstQuestModel.AssetKey.Value),
                mstQuestModel.QuestFlavorText
            );
        }

        (MasterDataId mstQuestId, Difficulty difficulty) GetSelectedStageMstQuestIdAndDifficulty(
            ShowReleaseAnimationStatus showAnimationStatusAtHome)
        {
            if (TryGetCurrentSelectMstQuestId(
                    showAnimationStatusAtHome,
                    PreferenceRepository.CurrentHomeTopSelectMstQuestId,
                    out MasterDataId currentSelectMstQuestId))
            {
                var currentMstQuest = MstQuestDataRepository.GetMstQuestModel(currentSelectMstQuestId);
                return (currentMstQuest.Id, currentMstQuest.Difficulty);
            }
            else
            {
                // チュートリアルステージは除外する
                var stageModels = GameRepository.GetGameFetch().StageModels
                    .Where(stage => !TutorialDefinitionIds.StageIds.Contains(stage.MstStageId))
                    .ToList();

                var defaultMstStage = stageModels.Count <= 0
                    ? DefaultStageProvider.GetDefaultStage()
                    : MstStageDataRepository.GetMstStage(stageModels[0].MstStageId);
                var defaultMstQuest = MstQuestDataRepository.GetMstQuestModel(defaultMstStage.MstQuestId);
                return (defaultMstQuest.Id, defaultMstQuest.Difficulty);
            }
        }

        (MasterDataId mstQuestId, Difficulty difficulty) GetDefaultStageMstQuestIdAndDifficulty()
        {
            var defaultStage = DefaultStageProvider.GetDefaultStage();
            var defaultMstQuest = MstQuestDataRepository.GetMstQuestModel(defaultStage.MstQuestId);
            return (defaultMstQuest.Id, defaultMstQuest.Difficulty);
        }

        bool TryGetCurrentSelectMstQuestId(
            ShowReleaseAnimationStatus showAnimationStatusAtHome,
            MasterDataId currentHomeTopSelectMstQuestId,
            out MasterDataId currentSelectMstQuestId)
        {
            currentSelectMstQuestId = showAnimationStatusAtHome.NewReleaseMstQuestId.IsEmpty()
                ? currentHomeTopSelectMstQuestId
                : showAnimationStatusAtHome.NewReleaseMstQuestId;

            return !currentSelectMstQuestId.IsEmpty() && ExistsQuest(currentSelectMstQuestId);
        }

        bool ExistsQuest(MasterDataId mstQuestId)
        {
            var mstQuestModel = MstQuestDataRepository.GetMstQuestModelFirstOrDefault(mstQuestId);
            return !mstQuestModel.IsEmpty();
        }

        void UpdateSelectedStageMstQuestId(MasterDataId mstQuestId)
        {
            PreferenceRepository.SetCurrentHomeTopSelectMstQuestId(mstQuestId);
        }

        IReadOnlyList<HomePlayableStageUseCaseModel> CreateStageLists(MasterDataId mstQuestId,
            MasterDataId selectedMstStageId)
        {
            var targetMstStages =
                MstStageDataRepository.GetMstStages()
                    .Where(s => s.MstQuestId == mstQuestId);

            var gameFetchModel = GameRepository.GetGameFetch();
            var userEventModels = gameFetchModel.UserStageEventModels;
            var userStageModels = gameFetchModel.StageModels;
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstQuestId);

            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;
            var mstCharacterModels = PartyCacheRepository.GetCurrentPartyModel().GetUnitList()
                .Join(userUnitModels, id => id, userUnit => userUnit.UsrUnitId, (_, userUnit) => userUnit)
                .Select(userUnit => MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId))
                .ToList();
            var settings = MstStageEventSettingDataRepository.GetStageEventSettings();

            if (mstQuest.QuestType == QuestType.Event)
            {
                return targetMstStages
                    .Join(settings, m => m.Id, s => s.MstStageId, (m, s) => new { m, s })
                    .Where(stageAndSettings => stageAndSettings.s.StartAt <= TimeProvider.Now &&
                                               TimeProvider.Now < stageAndSettings.s.EndAt)
                    .OrderBy(stageAndSetting => stageAndSetting.m.SortOrder)
                    .Select(stageAndSettings =>
                    {
                        var mstQuestEndDate = new UnlimitedCalculableDateTimeOffset(stageAndSettings.s.EndAt);
                        var stage = userEventModels.FirstOrDefault(s => s.MstStageId == stageAndSettings.m.Id);
                        if (stage == null)
                        {
                            return CreateEmptyUseCaseModel(
                                mstQuest,
                                stageAndSettings.m,
                                userEventModels,
                                mstQuestEndDate,
                                stageAndSettings.s,
                                mstCharacterModels);
                        }
                        else
                        {
                            return CreateUseCaseModelAtEvent(
                                mstQuest,
                                userEventModels,
                                stageAndSettings.s,
                                stage,
                                selectedMstStageId,
                                mstQuestEndDate,
                                mstCharacterModels);
                        }
                    })
                    .ToList();
            }
            else
            {
                var mstQuestEndDate = mstQuest.EndDate;
                return targetMstStages
                    .GroupJoin(
                        userStageModels,
                        mst => mst.Id,
                        user => user.MstStageId,
                        (mst, users) => new { mst, user = users.FirstOrDefault() ?? StageModel.Empty })
                    .OrderBy(mstAndUser => mstAndUser.mst.SortOrder)
                    .Select(mstAndUser =>
                    {
                        var stageEventSettingModel = settings.FirstOrDefault(
                            s => s.MstStageId == mstAndUser.mst.Id,
                            MstStageEventSettingModel.Empty);
                        var stage = userStageModels.FirstOrDefault(s => s.MstStageId == mstAndUser.mst.Id);
                        if (stage == null)
                        {
                            return CreateEmptyUseCaseModel(
                                mstQuest,
                                mstAndUser.mst,
                                userStageModels,
                                mstQuestEndDate,
                                stageEventSettingModel,
                                mstCharacterModels);
                        }
                        else
                        {
                            return CreateUseCaseModelAtNormal(
                                mstQuest,
                                userStageModels,
                                stage,
                                selectedMstStageId,
                                mstQuestEndDate,
                                mstCharacterModels);
                        }
                    })
                    .ToList();
            }
        }

        MasterDataId GetInitialSelectMstStageId(
            bool shouldShowReleaseAnimation,
            IReadOnlyList<HomePlayableStageUseCaseModel> stages,
            SelectedStageModel selectedStageModel)
        {
            // 開放演出あれば最後のステージ
            if (shouldShowReleaseAnimation)
            {
                return stages.LastOrDefault(s => s.Playable)?.MstStageId ?? stages.First().MstStageId;
            }

            // 最新の新規ステージがあればそれをターゲット
            if (TryGetLatestNewStageMstStageId(stages, out MasterDataId latestNewStageMstStageId))
            {
                return latestNewStageMstStageId;
            }

            // アプリ起動中における最終選択ステージがStagesに入っていればそれをターゲット
            if (!selectedStageModel.SelectedStageId.IsEmpty())
            {
                // 選択中ステージが存在しない場合は初期選択ステージを返す
                if (stages.Any(s => s.MstStageId == selectedStageModel.SelectedStageId))
                {
                    return selectedStageModel.SelectedStageId;
                }
            }


            // 開放演出無いかつ選択中ステージがなければ、端末保存から前回選択したステージがターゲット
            if (TryGetLastPlayedMstQuestId(stages, out MasterDataId lastPlayedMstStageId))
            {
                return lastPlayedMstStageId;
            }
            else
            {
                return stages.LastOrDefault(s => s.Playable)?.MstStageId ?? stages.First().MstStageId;
            }
        }

        bool TryGetLastPlayedMstQuestId(IReadOnlyList<HomePlayableStageUseCaseModel> stages, out MasterDataId result)
        {
            result = MasterDataId.Empty;
            var playedMstStageId = PreferenceRepository.LastPlayedMstStageId;
            if (playedMstStageId.IsEmpty())
            {
                return false;
            }

            // 選択中クエストのStagesの中に対象Id無ければfalse返す
            if (!stages.Exists(s => s.MstStageId == playedMstStageId)) return false;

            var mstStageModel = MstStageDataRepository.GetMstStageFirstOrDefault(playedMstStageId);
            result = mstStageModel.Id;
            if (mstStageModel.IsEmpty()) return false;

            return CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mstStageModel.StartAt, mstStageModel.EndAt);
        }

        bool TryGetLatestNewStageMstStageId(IReadOnlyList<HomePlayableStageUseCaseModel> stages, out MasterDataId result)
        {
            result = MasterDataId.Empty;
            var targetModel = stages
                .LastOrDefault(s => s.StageClearStatus == StageClearStatus.New && s.Playable);

            if (targetModel != null)
            {
                result = targetModel.MstStageId;
            }

            return targetModel != null;
        }

        HomePlayableStageUseCaseModel CreateEmptyUseCaseModel(
            MstQuestModel mstQuest,
            MstStageModel mstStage,
            IReadOnlyList<IStageClearCountable> countables,
            UnlimitedCalculableDateTimeOffset mstQuestEndDate,
            MstStageEventSettingModel mstStageEventSettingModel,
            IReadOnlyList<MstCharacterModel> mstCharacterModels,
            bool isTutorial = false)
        {
            var requiredMstStage = MstStageDataRepository.GetMstStageFirstOrDefault(mstStage.ReleaseRequiredMstStageId);
            var prevStageCountable = countables.FirstOrDefault(c => c.MstStageId == mstStage.ReleaseRequiredMstStageId);

            var mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                mstStage.Id,
                InGameContentType.Stage);

            var isSpeedAttack = mstQuest.QuestType != QuestType.Normal
                                && mstInGameSpecialRuleModels.Any(rule => rule.RuleType == RuleType.SpeedAttack);

            var speedAttackUseCaseModel = CreateSpeedAttackUseCaseModel(
                mstStage.Id,
                EventClearTimeMs.Empty,
                isSpeedAttack);

            var existsSpecialRule = InGameSpecialRuleEvaluator.ExistsSpecialRule(
                InGameContentType.Stage,
                mstStage.Id,
                mstQuest.QuestType);

            var campaignTargetType = mstQuest.QuestType == QuestType.Event ? CampaignTargetType.EventQuest : CampaignTargetType.NormalQuest;
            var staminaCampaignModel = CampaignModelFactory.CreateCampaignModel(
                mstQuest.Id,
                campaignTargetType,
                CampaignTargetIdType.Quest,
                mstQuest.Difficulty,
                CampaignType.Stamina);
            var campaignModels = CampaignModelFactory.CreateCampaignModels(
                mstQuest.Id,
                campaignTargetType,
                CampaignTargetIdType.Quest,
                mstQuest.Difficulty);

            var consumeStamina = mstStage.StageConsumeStamina;
            if (!staminaCampaignModel.IsEmpty())
            {
                consumeStamina = StageStaminaCalculator.CalcConsumeStaminaInCampaign(
                    mstStage.StageConsumeStamina,
                    staminaCampaignModel.EffectValue);
            }

            var staminaBoostBalloonType = mstStage.AutoLapType switch
            {
                null => StaminaBoostBalloonType.None,
                AutoLapType.Initial => StaminaBoostBalloonType.DefaultBalloon,
                AutoLapType.AfterClear => StaminaBoostBalloonType.FirstClearBalloon,
                _ => StaminaBoostBalloonType.None
            };

            if (requiredMstStage.IsEmpty())
            {
                return HomePlayableStageUseCaseModel.EmptyOpened(
                    mstStage,
                    mstQuestEndDate,
                    mstStageEventSettingModel,
                    speedAttackUseCaseModel,
                    InGameSpecialRuleAchievingEvaluator.CreateAchievedSpecialRuleFlag(
                        mstCharacterModels,
                        mstInGameSpecialRuleModels),
                    existsSpecialRule,
                    isTutorial ? new List<CampaignModel>() : campaignModels,
                    isTutorial ? mstStage.StageConsumeStamina : consumeStamina,
                    staminaBoostBalloonType);
            }
            else if (prevStageCountable != null && 1 <= prevStageCountable.ClearCount)
            {
                return HomePlayableStageUseCaseModel.EmptyOpened(
                    mstStage,
                    mstQuestEndDate,
                    mstStageEventSettingModel,
                    speedAttackUseCaseModel,
                    InGameSpecialRuleAchievingEvaluator.CreateAchievedSpecialRuleFlag(
                        mstCharacterModels,
                        mstInGameSpecialRuleModels),
                    existsSpecialRule,
                    isTutorial ? new List<CampaignModel>() : campaignModels,
                    isTutorial ? mstStage.StageConsumeStamina : consumeStamina,
                    staminaBoostBalloonType);
            }
            else
            {
                return HomePlayableStageUseCaseModel.EmptyNonOpen(
                    mstStage,
                    requiredMstStage.StageNumber,
                    mstQuestEndDate,
                    isTutorial ? new List<CampaignModel>() : campaignModels,
                    isTutorial ? mstStage.StageConsumeStamina : consumeStamina);
            }
        }

        HomePlayableStageUseCaseModel CreateUseCaseModelAtNormal(
            MstQuestModel mstQuest,
            IReadOnlyList<StageModel> stageModels,
            StageModel targetStageModel,
            MasterDataId selectedMstStageId,
            UnlimitedCalculableDateTimeOffset mstQuestEndDate,
            IReadOnlyList<MstCharacterModel> mstCharacterModels,
            bool isTutorial = false)
        {
            var mstStages = MstStageDataRepository.GetMstStages();
            var mstStage = mstStages.First(s => s.Id == targetStageModel.MstStageId);
            var releaseRequiredMstStage = mstStages.FirstOrDefault(
                s => s.Id == mstStage.ReleaseRequiredMstStageId,
                MstStageModel.Empty);
            var isReleased = StagePlayableEvaluator.EvaluateNormalStage(releaseRequiredMstStage, stageModels);
            var isSelected = new StageIsSelected(selectedMstStageId == targetStageModel.MstStageId);
            var isShowArtworkFragmentIcon = CheckIsShowArtworkFragmentIcon(
                MstArtworkFragmentDataRepository.GetDropGroupArtworkFragments(mstStage.MstArtworkFragmentDropGroupId),
                GameRepository.GetGameFetchOther().UserArtworkFragmentModels
            );

            var mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                targetStageModel.MstStageId,
                InGameContentType.Stage);

            var existsSpecialRule = InGameSpecialRuleEvaluator.ExistsSpecialRule(
                InGameContentType.Stage,
                targetStageModel.MstStageId,
                QuestType.Normal);

            // 通常ステージはスピードアタック表示をしない
            var speedAttackModel = SpeedAttackUseCaseModel.Empty;
            var isRewardComplete = StageRewardCompleteFlag.False;

            var staminaCampaignModel = CampaignModelFactory.CreateCampaignModel(
                mstQuest.Id,
                CampaignTargetType.NormalQuest,
                CampaignTargetIdType.Quest,
                mstQuest.Difficulty,
                CampaignType.Stamina);
            var campaignModels = CampaignModelFactory.CreateCampaignModels(
                mstQuest.Id,
                CampaignTargetType.NormalQuest,
                CampaignTargetIdType.Quest,
                mstQuest.Difficulty);

            var consumeStamina = mstStage.StageConsumeStamina;
            if (!staminaCampaignModel.IsEmpty())
            {
                consumeStamina = StageStaminaCalculator.CalcConsumeStaminaInCampaign(
                    mstStage.StageConsumeStamina,
                    staminaCampaignModel.EffectValue);
            }

            var clearStatus = CheckStageClearStatus(targetStageModel.ClearCount, isReleased);
            var staminaBoostBalloonType = mstStage.AutoLapType switch
            {
                null => StaminaBoostBalloonType.None,
                AutoLapType.Initial => StaminaBoostBalloonType.DefaultBalloon,
                AutoLapType.AfterClear => clearStatus == StageClearStatus.Clear
                    ? StaminaBoostBalloonType.DefaultBalloon
                    : StaminaBoostBalloonType.FirstClearBalloon,
                _ => StaminaBoostBalloonType.None
            };

            return new HomePlayableStageUseCaseModel(
                targetStageModel.MstStageId,
                mstStage.StageNumber,
                mstStage.Name,
                mstStage.RecommendedLevel,
                mstStage.StageAssetKey,
                consumeStamina,
                releaseRequiredMstStage?.StageNumber ?? StageNumber.Create(-1),
                isReleased,
                isSelected,
                clearStatus,
                mstQuestEndDate,
                StageClearCount.Empty,
                ClearableCount.Empty,
                speedAttackModel,
                isShowArtworkFragmentIcon,
                isRewardComplete,
                InGameSpecialRuleAchievingEvaluator.CreateAchievedSpecialRuleFlag(mstCharacterModels, mstInGameSpecialRuleModels),
                existsSpecialRule,
                isTutorial ? new List<CampaignModel>() : campaignModels,
                staminaBoostBalloonType);
        }

        HomePlayableStageUseCaseModel CreateUseCaseModelAtEvent(
            MstQuestModel mstQuest,
            IReadOnlyList<UserStageEventModel> userEventModels,
            MstStageEventSettingModel mstStageEventSettingModel,
            UserStageEventModel targetUserStageEventModel,
            MasterDataId selectedMstStageId,
            UnlimitedCalculableDateTimeOffset mstQuestEndDate,
            IReadOnlyList<MstCharacterModel> mstCharacterModels)
        {
            var mstStages = MstStageDataRepository.GetMstStages();
            var mstStage = mstStages.First(s => s.Id == targetUserStageEventModel.MstStageId);
            var releaseRequiredMstStage = mstStages.FirstOrDefault(
                s => s.Id == mstStage.ReleaseRequiredMstStageId,
                MstStageModel.Empty);
            //そのうちEvent処理自体消すので一旦NormalStageでEvaluateする
            var isReleased =
                StagePlayableEvaluator.EvaluateNormalStage(
                    releaseRequiredMstStage,
                    userEventModels);
            var isSelected =
                new StageIsSelected(selectedMstStageId == targetUserStageEventModel.MstStageId);
            var isShowArtworkFragmentIcon =
                ArtworkFragmentCompleteEvaluator.Evaluate(mstStage.MstArtworkFragmentDropGroupId);
            var stageClearCount = targetUserStageEventModel.IsEmpty()
                ? StageClearCount.Empty
                : targetUserStageEventModel.ResetClearCount;

            var speedAttackModel = SpeedAttackUseCaseModelFactory.Create(targetUserStageEventModel);
            var isSpeedAttack = SpeedAttackRewardCompleteEvaluator.Evaluate(speedAttackModel);

            var speedAttackRewardComplete = isSpeedAttack && speedAttackModel.NextGoalTime.IsEmpty();
            var isRewardComplete = new StageRewardCompleteFlag(speedAttackRewardComplete);

            var existsSpecialRule = InGameSpecialRuleEvaluator.ExistsSpecialRule(
                InGameContentType.Stage,
                targetUserStageEventModel.MstStageId,
                QuestType.Event);
            var mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                targetUserStageEventModel.MstStageId,
                InGameContentType.Stage);

            var staminaCampaignModel = CampaignModelFactory.CreateCampaignModel(
                mstQuest.Id,
                CampaignTargetType.EventQuest,
                CampaignTargetIdType.Quest,
                mstQuest.Difficulty,
                CampaignType.Stamina);
            var campaignModels = CampaignModelFactory.CreateCampaignModels(
                mstQuest.Id,
                CampaignTargetType.EventQuest,
                CampaignTargetIdType.Quest,
                mstQuest.Difficulty);

            var consumeStamina = mstStage.StageConsumeStamina;
            if (!staminaCampaignModel.IsEmpty())
            {
                consumeStamina = StageStaminaCalculator.CalcConsumeStaminaInCampaign(
                    mstStage.StageConsumeStamina,
                    staminaCampaignModel.EffectValue);
            }

            var clearStatus = CheckStageClearStatus(stageClearCount, isReleased);
            var staminaBoostBalloonType = mstStage.AutoLapType switch
            {
                null => StaminaBoostBalloonType.None,
                AutoLapType.Initial => StaminaBoostBalloonType.DefaultBalloon,
                AutoLapType.AfterClear => clearStatus == StageClearStatus.Clear
                    ? StaminaBoostBalloonType.DefaultBalloon
                    : StaminaBoostBalloonType.FirstClearBalloon,
                _ => StaminaBoostBalloonType.None
            };

            return new HomePlayableStageUseCaseModel(
                targetUserStageEventModel.MstStageId,
                mstStage.StageNumber,
                mstStage.Name,
                mstStage.RecommendedLevel,
                mstStage.StageAssetKey,
                consumeStamina,
                releaseRequiredMstStage?.StageNumber ?? StageNumber.Create(-1),
                isReleased,
                isSelected,
                clearStatus,
                mstQuestEndDate,
                stageClearCount,
                mstStageEventSettingModel.ClearableCount,
                speedAttackModel,
                isShowArtworkFragmentIcon,
                isRewardComplete,
                InGameSpecialRuleAchievingEvaluator.CreateAchievedSpecialRuleFlag(mstCharacterModels, mstInGameSpecialRuleModels),
                existsSpecialRule,
                campaignModels,
                staminaBoostBalloonType);
        }

        SpeedAttackUseCaseModel CreateSpeedAttackUseCaseModel(
            MasterDataId mstStageId,
            EventClearTimeMs clearTime,
            bool isSpeedAttack)
        {
            if (!isSpeedAttack) return SpeedAttackUseCaseModel.Empty;

            var rewards = MstStageClearTimeRewardRepository.GetClearTimeRewards(mstStageId)
                .OrderByDescending(mst => mst.UpperClearTimeMs)
                .Select(mst => mst.UpperClearTimeMs)
                .ToList();

            var nextGoalTime = StageClearTime.Empty;
            if (clearTime.IsEmpty())
            {
                nextGoalTime = rewards.First();
            }
            else
            {
                nextGoalTime = rewards.FirstOrDefault(upperClearTime => upperClearTime < clearTime) ?? StageClearTime.Empty;
            }

            return new SpeedAttackUseCaseModel(clearTime, nextGoalTime);
        }


        StageClearStatus CheckStageClearStatus(StageClearCount clearCount, StagePlayableFlag playableFlag)
        {
            if (!playableFlag) return StageClearStatus.None;
            if (clearCount.IsEmpty()) return StageClearStatus.New;

            return 1 <= clearCount.Value ? StageClearStatus.Clear : StageClearStatus.None;
        }

        StageRewardCompleteFlag CheckIsShowArtworkFragmentIcon(
            IReadOnlyList<MstArtworkFragmentModel> mstArtworkFragmentModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            if (!mstArtworkFragmentModels.Any()) return StageRewardCompleteFlag.False;

            foreach (var mstFragment in mstArtworkFragmentModels)
            {
                if (!userArtworkFragmentModels.Any(user =>
                        user.MstArtworkId == mstFragment.MstArtworkId &&
                        user.MstArtworkFragmentId == mstFragment.Id))
                {
                    return StageRewardCompleteFlag.False;
                }
            }

            return StageRewardCompleteFlag.True;
        }

        HomePlayableQuestUseCaseModel CreateModelTutorialMainPart1()
        {
            var shouldShowAnimationStatus = new ShowReleaseAnimationStatus(
                MasterDataId.Empty,
                TutorialDefinitionIds.Stage2Id);

            var selectedMstStage = new SelectedStageModel(
                TutorialDefinitionIds.Stage2Id,
                MasterDataId.Empty,
                ContentSeasonSystemId.Empty);

            var clearedTutorialStages = new List<StageModel>()
            {
                new StageModel(
                    UserDataId.Empty,
                    TutorialDefinitionIds.Stage1Id,
                    new StageReleaseStatus(StageStatus.Released),
                    EventClearTimeMs.Empty,
                    new StageClearCount(1)),
            };

            return CreateTutorialModel(shouldShowAnimationStatus, selectedMstStage, clearedTutorialStages);
        }

        HomePlayableQuestUseCaseModel CreateModelTutorialMainPart2()
        {
            var shouldShowAnimationStatus = new ShowReleaseAnimationStatus(
                MasterDataId.Empty,
                TutorialDefinitionIds.Stage3Id);

            var selectedMstStage = new SelectedStageModel(
                TutorialDefinitionIds.Stage3Id,
                MasterDataId.Empty,
                ContentSeasonSystemId.Empty);

            var clearedTutorialStages = new List<StageModel>()
            {
                new StageModel(
                    UserDataId.Empty,
                    TutorialDefinitionIds.Stage1Id,
                    new StageReleaseStatus(StageStatus.Released),
                    EventClearTimeMs.Empty,
                    new StageClearCount(1)),
                new StageModel(
                    UserDataId.Empty,
                    TutorialDefinitionIds.Stage2Id,
                    new StageReleaseStatus(StageStatus.Released),
                    EventClearTimeMs.Empty,
                    new StageClearCount(1)),
            };

            return CreateTutorialModel(shouldShowAnimationStatus, selectedMstStage, clearedTutorialStages);
        }

        HomePlayableQuestUseCaseModel CreateTutorialModel(
            ShowReleaseAnimationStatus shouldShowAnimationStatus,
            SelectedStageModel selectedMstStage,
            IReadOnlyList<StageModel> clearedTutorialStages)
        {
            var showStageReleaseAnimation =
                ShowStageReleaseAnimationFactory.Create(shouldShowAnimationStatus.NewReleaseMstStageId);

            var questId = TutorialDefinitionIds.QuestId;
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(questId);
            var assetPath = QuestImageAssetPath.GetQuestImagePath(mstQuest.AssetKey.Value);
            var stages = CreateTutorialStageLists(questId, selectedMstStage.SelectedStageId, clearedTutorialStages);
            return new HomePlayableQuestUseCaseModel(
                mstQuest.Id,
                mstQuest.Name,
                assetPath,
                QuestLimitTime.Empty,
                shouldShowAnimationStatus.NewReleaseMstStageId,
                stages,
                showStageReleaseAnimation,
                ShowQuestReleaseAnimation.Empty,
                Difficulty.Normal,
                InAppReviewFlag.False,
                DisplayTryStageTextFlag.False);
        }

        IReadOnlyList<HomePlayableStageUseCaseModel> CreateTutorialStageLists(MasterDataId mstQuestId,
            MasterDataId selectedMstStageId, IReadOnlyList<StageModel> clearedUserTutorialStageModel)
        {
            var targetMstStages =
                MstStageDataRepository.GetMstStages()
                    .Where(s => s.MstQuestId == mstQuestId);

            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstQuestId);

            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;
            var mstCharacterModels = PartyCacheRepository.GetCurrentPartyModel().GetUnitList()
                .Join(userUnitModels, id => id, userUnit => userUnit.UsrUnitId, (_, userUnit) => userUnit)
                .Select(userUnit => MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId))
                .ToList();
            var settings = MstStageEventSettingDataRepository.GetStageEventSettings();
            var mstQuestEndDate = mstQuest.EndDate;

            return targetMstStages
                .GroupJoin(
                    clearedUserTutorialStageModel,
                    mst => mst.Id,
                    user => user.MstStageId,
                    (mst, users) => new { mst, user = users.FirstOrDefault() ?? StageModel.Empty })
                .OrderBy(mstAndUser => mstAndUser.mst.SortOrder)
                .Select(mstAndUser =>
                {
                    var stageEventSettingModel = settings.FirstOrDefault(
                        s => s.MstStageId == mstAndUser.mst.Id,
                        MstStageEventSettingModel.Empty);
                    var stage = clearedUserTutorialStageModel.FirstOrDefault(s => s.MstStageId == mstAndUser.mst.Id);
                    if (stage == null)
                    {
                        return CreateEmptyUseCaseModel(
                            mstQuest,
                            mstAndUser.mst,
                            clearedUserTutorialStageModel,
                            mstQuestEndDate,
                            stageEventSettingModel,
                            mstCharacterModels,
                            true);
                    }
                    else
                    {
                        return CreateUseCaseModelAtNormal(
                            mstQuest,
                            clearedUserTutorialStageModel,
                            stage,
                            selectedMstStageId,
                            mstQuestEndDate,
                            mstCharacterModels,
                            true);
                    }
                })
                .ToList();
        }

        InAppReviewFlag ShouldDisplayInAppReview(
            MasterDataId releaseQuestId)
        {
            if (releaseQuestId.IsEmpty()) return InAppReviewFlag.False;

            var inAppReviewDisplayTriggerQuestId1 =
                MstConfigRepository.GetConfig(MstConfigKey.InAppReviewTriggerQuestId1);

            var inAppReviewDisplayTriggerQuestId2 =
                MstConfigRepository.GetConfig(MstConfigKey.InAppReviewTriggerQuestId2);

            if (inAppReviewDisplayTriggerQuestId1.IsEmpty() && inAppReviewDisplayTriggerQuestId2.IsEmpty())
            {
                return InAppReviewFlag.False;
            }

            var isInAppReviewDisplay = inAppReviewDisplayTriggerQuestId1.Value.ToMasterDataId() == releaseQuestId ||
                                       inAppReviewDisplayTriggerQuestId2.Value.ToMasterDataId() == releaseQuestId;

            return new InAppReviewFlag(isInAppReviewDisplay);
        }

        DisplayTryStageTextFlag ShouldDisplayTryStageText()
        {
            // チュートリアルステージ完了以降の通常ステージが表示されている状態か
            var isNormalStageDisplayed = GameRepository.GetGameFetchOther().TutorialStatus.IsNormalStageDisplayed();

            // ステージ挑戦がチュートリアルステージのみか
            var isOnlyTutorialStage = GameRepository.GetGameFetch().StageModels
                .All(stage => TutorialDefinitionIds.StageIds.Contains(stage.MstStageId));

            // 通常ステージ表示でメインステージ挑戦歴がない(チュートリアルステージのみの)場合
            var isDisplayTryStageText = isNormalStageDisplayed && isOnlyTutorialStage;

            return new DisplayTryStageTextFlag(isDisplayTryStageText);
        }
    }
}
