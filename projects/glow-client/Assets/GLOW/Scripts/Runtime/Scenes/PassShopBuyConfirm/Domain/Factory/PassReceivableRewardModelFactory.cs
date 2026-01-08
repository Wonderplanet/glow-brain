using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.PassShopProductDetail.Domain.Factory;
using GLOW.Scenes.PassShopProductDetail.Domain.Model;
using Zenject;

namespace GLOW.Scenes.PassShopBuyConfirm.Domain.Factory
{
    public class PassReceivableRewardModelFactory : IPassReceivableRewardModelFactory
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IProductNameFactory ProductNameFactory { get; }

        IReadOnlyList<PassReceivableRewardModel> IPassReceivableRewardModelFactory.CreatePassReceivableRewardModels(
            MasterDataId mstShopPassId)
        {
            var mstShopPass = MstShopProductDataRepository.GetShopPass(mstShopPassId);
            var rewards = MstShopProductDataRepository.GetShopPassRewards(mstShopPassId);

            var passDailyRewards = rewards
                .Where(reward => reward.ShopPassRewardType == ShopPassRewardType.Daily)
                .ToList();
            
            // 1日に受け取れる報酬の種類と量の辞書(TypeとIdが同一のものは合算)
            var passDailyRewardAmountDictionary = passDailyRewards
                .GroupBy(reward => (reward.ResourceType, reward.ResourceId))
                .ToDictionary(
                    group => group.Key, 
                    group => new ObscuredPlayerResourceAmount(
                        group.Sum(reward => reward.ResourceAmount.Value)));
            
            var dailyMaxReceivableRewards = passDailyRewards
                .Select(reward => reward with
                {
                    ResourceAmount = reward.ResourceAmount * mstShopPass.PassDurationDays
                });
            
            var immediatelyRewards = rewards
                .Where(reward => reward.ShopPassRewardType == ShopPassRewardType.Immediately);
            var passRewards = dailyMaxReceivableRewards.Concat(immediatelyRewards).ToList();

            var passReceivableMaxRewards = passRewards
                .GroupBy(reward => (reward.ResourceType, reward.ResourceId))
                .Select(group => PlayerResourceModelFactory.Create(
                    group.Key.ResourceType,
                    group.Key.ResourceId,
                    new PlayerResourceAmount(group.Sum(reward => reward.ResourceAmount.Value))))
                .ToList();

            var passReceivableRewardModel = passReceivableMaxRewards
                .Select(reward => new PassReceivableRewardModel(
                    ProductNameFactory.Create(reward.Type, reward.Id),
                    reward,
                    passDailyRewardAmountDictionary.GetValueOrDefault(
                        (reward.Type, reward.Id), 
                        ObscuredPlayerResourceAmount.Empty)))
                .ToList();

            return passReceivableRewardModel;
        }
    }
}
