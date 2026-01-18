using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Domain.ModelFactories;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public sealed class HomeStageInfoUseCases
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageRewardDataRepository MstStageRewardDataRepository { get; }
        [Inject] IMstStageEventRewardDataRepository MstStageEventRewardDataRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IInGameSpecialRuleModelFactory InGameSpecialRuleModelFactory { get; }
        [Inject] IMstStageClearTimeRewardRepository MstStageClearTimeRewardRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IAutoPlayerSequenceModelFactory AutoPlayerSequenceModelFactory { get; }

        public HomeStageInfoUseCaseModel GetHomeStageInfoUseCasesModel(MasterDataId mstStageId)
        {
            List<HomeStageInfoEnemyUseCaseModel> enemyCharacterList = new List<HomeStageInfoEnemyUseCaseModel>();
            List<HomeStageInfoArtworkFragmentUseCaseModel> artworkFragmentResourceList =　
                new List<HomeStageInfoArtworkFragmentUseCaseModel>();

            var mstStage = MstStageDataRepository.GetMstStage(mstStageId);
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);
            var autoPlayerSequenceModel = AutoPlayerSequenceModelFactory.Create(mstStage.MstAutoPlayerSequenceSetId);

            foreach (var enemyModel in autoPlayerSequenceModel.SummonEnemies)
            {
                var enemyUseCaseModel = new HomeStageInfoEnemyUseCaseModel(
                    enemyModel.Id,
                    enemyModel.Name,
                    enemyModel.Color,
                    enemyModel.RoleType,
                    enemyModel.Kind,
                    enemyModel.AssetKey,
                    enemyModel.SortOrder);
                enemyCharacterList.Add(enemyUseCaseModel);
            }

            var stage = GetStageClearCountable(mstQuest, mstStageId);
            var artworkFragmentList = MstArtworkFragmentDataRepository.
                GetDropGroupArtworkFragments(mstStage.MstArtworkFragmentDropGroupId);
            var userArtworkFragmentList = GameRepository.GetGameFetchOther().UserArtworkFragmentModels;
            foreach (var fragment in artworkFragmentList)
            {
                var treasureResource = new HomeStageInfoArtworkFragmentResource(
                    ResourceType.ArtworkFragment,
                    fragment.Id,
                    PlayerResourceAmount.Empty);

                artworkFragmentResourceList.Add(new HomeStageInfoArtworkFragmentUseCaseModel(
                    treasureResource,
                    CreateAcquiredFlag(userArtworkFragmentList, fragment)
                    ));
            }

            return new HomeStageInfoUseCaseModel(
                enemyCharacterList,
                artworkFragmentResourceList,
                CreatePlayerResourceList(mstQuest.QuestType, stage, mstStageId, mstStage),
                InGameSpecialRuleModelFactory.Create(InGameContentType.Stage, mstStageId, mstQuest.QuestType),
                CreateSpeedAttackUseCaseModel(mstQuest, mstStageId),
                mstStage.InGameDescription
                );
        }

        AcquiredFlag CreateAcquiredFlag(IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentList, MstArtworkFragmentModel fragment)
        {
            return new AcquiredFlag(
                userArtworkFragmentList.Any(usr =>
                    usr.MstArtworkId == fragment.MstArtworkId && usr.MstArtworkFragmentId == fragment.Id));
        }

        List<HomeStageInfoRewardUseCaseModel> CreatePlayerResourceList(
            QuestType type,
            IStageClearCountable stage,
            MasterDataId mstStageId,
            MstStageModel mstStage)
        {
            List<HomeStageInfoRewardUseCaseModel> result = new List<HomeStageInfoRewardUseCaseModel>();
            if (type == QuestType.Event)
            {
                var rewards =
                    MstStageEventRewardDataRepository.GetMstStageEventRewardList(mstStageId);
                result = rewards
                    .Select(reward => new HomeStageInfoRewardUseCaseModel(
                        reward.RewardCategory,
                        CheckIsFirstClearAcquired(reward.RewardCategory, stage?.ClearCount ?? new StageClearCount(0)),
                        new HomeStageInfoRewardResource(
                            reward.ResourceType,
                            reward.ResourceId,
                            reward.ResourceAmount.ToPlayerResourceAmount())))
                    .ToList();
            }
            else
            {
                var rewards = MstStageRewardDataRepository.GetMstStageRewardList(mstStageId);
                result = rewards
                    .Select(reward => new HomeStageInfoRewardUseCaseModel(
                        reward.RewardCategory,
                        CheckIsFirstClearAcquired(reward.RewardCategory, stage?.ClearCount ?? new StageClearCount(0)),
                        new HomeStageInfoRewardResource(
                            reward.ResourceType,
                            reward.ResourceId,
                            reward.ResourceAmount.ToPlayerResourceAmount())))
                    .ToList();
            }

            // MstStage側にもコインがあるのでそれを追加する
            result.Add(new HomeStageInfoRewardUseCaseModel(
                RewardCategory.Always,
                AcquiredFlag.False,
                new HomeStageInfoRewardResource(ResourceType.Coin, MasterDataId.Empty, mstStage.Coin.ToPlayerResourceAmount())));

            return result;
        }

        HomeStageInfoSpeedAttackUseCaseModel CreateSpeedAttackUseCaseModel(MstQuestModel mstQuest, MasterDataId mstStageId)
        {
            var mstInGameSpecialRules =
                MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(mstStageId, InGameContentType.Stage);
            var isSpeedAttack = mstInGameSpecialRules.Any(mst => mst.RuleType == RuleType.SpeedAttack);
            if (!isSpeedAttack) return HomeStageInfoSpeedAttackUseCaseModel.Empty;

            var clearTime = EventClearTimeMs.Empty;
            if (mstQuest.QuestType == QuestType.Normal)
            {
                var userStage = GameRepository.GetGameFetch().StageModels.FirstOrDefault(x => x.MstStageId == mstStageId);
                clearTime = userStage?.ClearTimeMs ?? EventClearTimeMs.Empty;
            }
            else
            {
                var userStageEvent = GameRepository.GetGameFetch().
                    UserStageEventModels.FirstOrDefault(x => x.MstStageId == mstStageId);
                clearTime = userStageEvent?.ResetClearTimeMs ?? EventClearTimeMs.Empty;
            }
            var list = CreateSpeedAttackRewardList(mstStageId, clearTime, isSpeedAttack);

            return new HomeStageInfoSpeedAttackUseCaseModel(clearTime, list);
        }

        IReadOnlyList<HomeStageInfoSpeedAttackRewardUseCaseModel> CreateSpeedAttackRewardList(
            MasterDataId mstStageId,
            EventClearTimeMs clearTime,
            bool isSpeedAttack)
        {
            var list = new List<HomeStageInfoSpeedAttackRewardUseCaseModel>();
            if(!isSpeedAttack) return list;

            var mstStageClearTimeRewards =
                MstStageClearTimeRewardRepository.GetClearTimeRewards(mstStageId);

            foreach (var reward in mstStageClearTimeRewards)
            {
                var playerResourceResultModel = new HomeStageInfoRewardResource(
                    reward.ResourceType,
                    reward.ResourceId,
                    reward.ResourceAmount.ToPlayerResourceAmount());

                var acquireFlag = clearTime == null || clearTime.IsEmpty()
                    ? AcquiredFlag.False
                    : new AcquiredFlag(clearTime <= reward.UpperClearTimeMs);
                list.Add(new HomeStageInfoSpeedAttackRewardUseCaseModel(
                    playerResourceResultModel,
                    reward.UpperClearTimeMs,
                    acquireFlag
                    ));
            }

            return list;
        }

        IStageClearCountable GetStageClearCountable(MstQuestModel mstQuest ,MasterDataId mstStageId)
        {
            IStageClearCountable stageClearCountable = null;
            if (mstQuest.QuestType == QuestType.Normal)
            {
                stageClearCountable = GameRepository.GetGameFetch().StageModels
                    .FirstOrDefault(stage => stage.MstStageId == mstStageId);
            }
            else
            {
                stageClearCountable = GameRepository.GetGameFetch().UserStageEventModels
                    .FirstOrDefault(stage => stage.MstStageId == mstStageId);
            }

            return stageClearCountable;
        }

        AcquiredFlag CheckIsFirstClearAcquired(RewardCategory rewardCategory, StageClearCount stageClearCount)
        {
            if (rewardCategory == RewardCategory.FirstClear && stageClearCount.Value > 0)
                return AcquiredFlag.True;

            return AcquiredFlag.False;
        }
    }
}
