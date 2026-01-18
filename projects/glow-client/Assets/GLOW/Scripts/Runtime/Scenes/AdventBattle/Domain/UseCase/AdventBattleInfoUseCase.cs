using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGameSpecialRule.Domain.ModelFactories;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.UseCase
{
    public class AdventBattleInfoUseCase
    {
        [Inject] IInGameSpecialRuleModelFactory InGameSpecialRuleModelFactory { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IAutoPlayerSequenceModelFactory AutoPlayerSequenceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }

        public AdventBattleInfoModel GetAdventBattleInfoModel(MasterDataId mstAdventBattleId)
        {
            var mstAdventBattleModel = MstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(mstAdventBattleId);
            var enemyList = GetAdventBattleEnemyList(mstAdventBattleModel);
            var inGameSpecialRuleModel = InGameSpecialRuleModelFactory.Create(
                InGameContentType.AdventBattle,
                mstAdventBattleId,
                QuestType.Event);
            var stageDescription = GetInGameDescription(mstAdventBattleModel);
            return new AdventBattleInfoModel(
                enemyList,
                inGameSpecialRuleModel,
                GetAdventBattleClearRewardList(mstAdventBattleModel),
                stageDescription);
        }

        IReadOnlyList<HomeStageInfoEnemyUseCaseModel> GetAdventBattleEnemyList(MstAdventBattleModel mstAdventBattleModel)
        {
            if(mstAdventBattleModel.IsEmpty())
            {
                return new List<HomeStageInfoEnemyUseCaseModel>();
            }

            var autoPlayerSequenceSetId = mstAdventBattleModel.MstAutoPlayerSequenceSetId;
            var autoPlayerSequenceModel = AutoPlayerSequenceModelFactory.Create(autoPlayerSequenceSetId);

            return autoPlayerSequenceModel.SummonEnemies.Select(Translate).ToList();
        }

        HomeStageInfoEnemyUseCaseModel Translate(MstEnemyStageParameterModel model)
        {
            return new HomeStageInfoEnemyUseCaseModel(
                model.Id,
                model.Name,
                model.Color,
                model.RoleType,
                model.Kind,
                model.AssetKey,
                model.SortOrder);
        }

        IReadOnlyList<PlayerResourceModel> GetAdventBattleClearRewardList(MstAdventBattleModel mstAdventBattleModel)
        {
            if (mstAdventBattleModel.IsEmpty())
            {
                return new List<PlayerResourceModel>();
            }

            var rewards = new List<PlayerResourceModel>();

            var rewardCoin = PlayerResourceModelFactory.Create(
                ResourceType.Coin,
                MasterDataId.Empty,
                mstAdventBattleModel.Coin.ToPlayerResourceAmount(),
                RewardCategory.Always);
            rewards.Add(rewardCoin);

            var rewardExp = PlayerResourceModelFactory.Create(
                ResourceType.Exp,
                MasterDataId.Empty,
                mstAdventBattleModel.UserExp.ToPlayerResourceAmount(),
                RewardCategory.Always);
            rewards.Add(rewardExp);

            // 一度でもクリアしていれば初回報酬受け取り済み
            var userAdventBattleModel = GameRepository.GetGameFetch().UserAdventBattleModels
                .FirstOrDefault(model => model.MstAdventBattleId == mstAdventBattleModel.Id, UserAdventBattleModel.Empty);
            rewards.AddRange(MstAdventBattleDataRepository.GetMstAdventBattleClearRewardModels(mstAdventBattleModel.Id)
                .Select(model => PlayerResourceModelFactory.Create(
                    model.ResourceType,
                    model.ResourceId,
                    model.ResourceAmount.ToPlayerResourceAmount(),
                    model.RewardCategory.ToRewardCategory(),
                    new AcquiredFlag(model.RewardCategory == AdventBattleClearRewardCategory.FirstClear &&
                                     userAdventBattleModel.ClearCount.IsCleared)
                ))
                .ToList());

            return rewards;
        }

        InGameDescription GetInGameDescription(MstAdventBattleModel mstAdventBattleModel)
        {
            if (mstAdventBattleModel.IsEmpty())
            {
                return InGameDescription.Empty;
            }

            return mstAdventBattleModel.InGameDescription;
        }
    }
}
