using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Scenes.IdleIncentiveTop.Domain.Calculator;
using GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator;
using GLOW.Scenes.IdleIncentiveTop.Domain.Models;
using GLOW.Scenes.IdleIncentiveTop.Domain.ValueObjects;
using GLOW.Scenes.PassShop.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.UseCase
{
    public class GetIdleIncentiveTopModelUseCase
    {
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IHeldPassEffectDisplayModelFactory HeldPassEffectDisplayModelFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        [Inject] IIdleIncentiveRewardEvaluator IdleIncentiveRewardEvaluator { get; }
        [Inject] IIdleIncentiveRewardAmountCalculator IdleIncentiveRewardAmountCalculator { get; }

        public IdleIncentiveTopModel GetIdleIncentiveTopModel()
        {
            var mstIdleIncentive = MstIdleIncentiveRepository.GetMstIdleIncentive();
            var mstIdleIncentiveRewardModel = IdleIncentiveRewardEvaluator.EvaluateHighestClearedStageReward();

            var coin = IdleIncentiveRewardAmountCalculator.CalculateRewardAmountPerHour(
                mstIdleIncentiveRewardModel.BaseCoinAmount, 
                mstIdleIncentive.RewardIncreaseIntervalMinutes);
            
            var exp = IdleIncentiveRewardAmountCalculator.CalculateRewardAmountPerHour(
                mstIdleIncentiveRewardModel.BaseExpAmount, 
                mstIdleIncentive.RewardIncreaseIntervalMinutes);

            var userIdleIncentiveModel = GameRepository.GetGameFetchOther().UserIdleIncentiveModel;
            
            var remainAdReceiveCount =
                mstIdleIncentive.MaxDailyAdQuickReceiveAmount - userIdleIncentiveModel.AdQuickReceiveCount;
            
            var remainDiamondReceiveCount =
                mstIdleIncentive.MaxDailyDiamondQuickReceiveAmount - userIdleIncentiveModel.DiamondQuickReceiveCount;
            
            var enableQuickReceive = !remainAdReceiveCount.IsZero() || !remainDiamondReceiveCount.IsZero();

            var displayModels = HeldPassEffectDisplayModelFactory.GetHeldPassEffectDisplayModels(
                new HashSet<ShopPassEffectType>()
                {
                    ShopPassEffectType.AdSkip,
                    ShopPassEffectType.IdleIncentiveAddReward,
                    ShopPassEffectType.IdleIncentiveMaxQuickReceiveByAd,
                    ShopPassEffectType.IdleIncentiveMaxQuickReceiveByDiamond
                });

            var passEffectValue = HeldPassEffectRepository.GetHeldPassEffectListModel().GetPassEffectValue(
                ShopPassEffectType.IdleIncentiveAddReward,
                TimeProvider.Now);

            var passAddCoin = IdleIncentiveRewardAmount.Empty;
            var passAddExp = IdleIncentiveRewardAmount.Empty;

            if (!passEffectValue.IsZero())
            {
                var coinWithPass = IdleIncentiveRewardAmountCalculator.CalculateRewardAmount(coin, passEffectValue);
                passAddCoin = coinWithPass - coin;
                
                var expWithPass = IdleIncentiveRewardAmountCalculator.CalculateRewardAmount(exp, passEffectValue);
                passAddExp = expWithPass - exp;
            }

            return new IdleIncentiveTopModel(
                coin,
                passAddCoin,
                exp,
                passAddExp,
                new EnableQuickReceiveFlag(enableQuickReceive),
                mstIdleIncentive.MaxIdleHours,
                mstIdleIncentive.InitialRewardReceiveMinutes,
                displayModels);
        }
    }
}
