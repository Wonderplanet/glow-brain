using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Sorter;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.IdleIncentiveTop.Domain.Calculator;
using GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator;
using GLOW.Scenes.IdleIncentiveTop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.UseCase
{
    public class GetIdleIncentiveRewardUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] IIdleIncentiveRewardEvaluator IdleIncentiveRewardEvaluator { get; }
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IIdleIncentiveRewardAmountCalculator IdleIncentiveRewardAmountCalculator { get; }
        [Inject] IPlayerResourceSorter PlayerResourceSorter { get; }

        public IdleIncentiveRewardListModel GetRewardList(TimeSpan currentElapsedTime)
        {
            var idleIncentive = MstIdleIncentiveRepository.GetMstIdleIncentive();
            var mstIdleIncentiveRewardModel = IdleIncentiveRewardEvaluator.EvaluateHighestClearedStageReward();
            
            var resourceModels = CreatePlayerResourceModels(
                currentElapsedTime, 
                mstIdleIncentiveRewardModel, 
                idleIncentive);

            var sortedRewards = PlayerResourceSorter.Sort(resourceModels);
            var list = sortedRewards
                .Select(model => new IdleIncentiveRewardListCellModel(model))
                .ToList();

            return new IdleIncentiveRewardListModel(list);
        }

        List<PlayerResourceModel> CreatePlayerResourceModels(
            TimeSpan currentElapsedTime,
            MstIdleIncentiveRewardModel mstIdleIncentiveRewardModel, 
            MstIdleIncentiveModel idleIncentive)
        {
            if (mstIdleIncentiveRewardModel.IsEmpty() || idleIncentive.InitialRewardReceiveMinutes > currentElapsedTime)
            {
                return new List<PlayerResourceModel>();
            }
            var resourceModels = new List<PlayerResourceModel>();
            
            // パス効果を一度だけ取得
            var passEffectValue = GetPassEffectValue();
            
            var idleIncentiveItems = MstIdleIncentiveRepository
                .GetMstIncentiveItems(mstIdleIncentiveRewardModel.MstIdleIncentiveItemGroupId);

            resourceModels.Add(CreateCoin(mstIdleIncentiveRewardModel, currentElapsedTime, idleIncentive, passEffectValue));
            resourceModels.Add(CreateExp(mstIdleIncentiveRewardModel, currentElapsedTime, idleIncentive, passEffectValue));

            var items = idleIncentiveItems
                .Select(model => TranslatePlayerResourceModel(
                    model,
                    currentElapsedTime,
                    idleIncentive.RewardIncreaseIntervalMinutes,
                    passEffectValue));
            
            resourceModels.AddRange(items);

            return resourceModels.Where(model => model.Amount.Value > 0).ToList();
        }

        PlayerResourceModel CreateCoin(
            MstIdleIncentiveRewardModel mstIdleIncentiveRewardModel,
            TimeSpan currentElapsedTime,
            MstIdleIncentiveModel idleIncentive,
            PassEffectValue passEffectValue)
        {
            var rewardAmount = IdleIncentiveRewardAmountCalculator.CalculatePlayerResourceAmount(
                mstIdleIncentiveRewardModel.BaseCoinAmount,
                currentElapsedTime,
                idleIncentive.RewardIncreaseIntervalMinutes,
                passEffectValue);
            
            return PlayerResourceModelFactory.Create(
                ResourceType.Coin, 
                MasterDataId.Empty, 
                rewardAmount);
        }

        PlayerResourceModel CreateExp(
            MstIdleIncentiveRewardModel mstIdleIncentiveRewardModel,
            TimeSpan currentElapsedTime,
            MstIdleIncentiveModel idleIncentive,
            PassEffectValue passEffectValue)
        {
            var rewardAmount = IdleIncentiveRewardAmountCalculator.CalculatePlayerResourceAmount(
                mstIdleIncentiveRewardModel.BaseExpAmount,
                currentElapsedTime,
                idleIncentive.RewardIncreaseIntervalMinutes,
                passEffectValue);
            
            return PlayerResourceModelFactory.Create(
                ResourceType.Exp, 
                MasterDataId.Empty, 
                rewardAmount);
        }

        PlayerResourceModel TranslatePlayerResourceModel(
            MstIdleIncentiveItemModel model,
            TimeSpan currentElapsedTime,
            TimeSpan intervalMinute,
            PassEffectValue passEffectValue)
        {
            var rewardAmount = IdleIncentiveRewardAmountCalculator.CalculatePlayerResourceAmount(
                model.BaseAmount,
                currentElapsedTime,
                intervalMinute,
                passEffectValue);
            
            return PlayerResourceModelFactory.Create(
                ResourceType.Item, 
                model.MstItemId, 
                rewardAmount);
        }
        
        PassEffectValue GetPassEffectValue()
        {
            var heldPassEffects = HeldPassEffectRepository.GetHeldPassEffectListModel();
            
            return heldPassEffects.GetPassEffectValue(
                ShopPassEffectType.IdleIncentiveAddReward, 
                TimeProvider.Now);
        }
    }
}
