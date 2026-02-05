using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.Calculator
{
    public class LimitAmountModelCalculator : ILimitAmountModelCalculator
    {
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        IReadOnlyList<LimitCheckModel> ILimitAmountModelCalculator.FilteringLimitAmount(IReadOnlyList<LimitCheckModel> models)
        {
            var result = new List<LimitCheckModel>();
            var calculateGroups = models
                .GroupBy(m => m.ResourceType)
                .ToList();

            // Coinのチェック
            var coinGroups = calculateGroups.FirstOrDefault(g => g.Key == ResourceType.Coin);
            var idleCoinGroups = calculateGroups.FirstOrDefault(g => g.Key == ResourceType.IdleCoin);
            if (IsLimitCoin(coinGroups, idleCoinGroups))
            {
                if(coinGroups != null) result.AddRange(coinGroups);
                if(idleCoinGroups != null) result.AddRange(idleCoinGroups);
            }

            // アイテムのチェック
            var itemGroups = calculateGroups.FirstOrDefault(g => g.Key == ResourceType.Item);
            if (itemGroups != null)
            {
                var limitItems = IsLimitItems(itemGroups);
                if (1 <= limitItems.Count) result.AddRange(limitItems);
            }

            // FreeDiamondのチェック
            var freeDiamondGroups = calculateGroups.FirstOrDefault(g => g.Key == ResourceType.FreeDiamond);
            if (freeDiamondGroups != null && IsLimitFreeDiamond(freeDiamondGroups)) result.AddRange(freeDiamondGroups);

            // PaidDiamondのチェック
            var paidDiamondGroups = calculateGroups.FirstOrDefault(g => g.Key == ResourceType.PaidDiamond);
            if (paidDiamondGroups != null && IsLimitPaidDiamond(paidDiamondGroups)) result.AddRange(paidDiamondGroups);

            // Expのチェック
            var expGroups = calculateGroups.FirstOrDefault(g => g.Key == ResourceType.Exp);
            if (expGroups != null && IsLimitExp(expGroups)) result.AddRange(expGroups);

            // Staminaのチェック
            var staminaGroups = calculateGroups.FirstOrDefault(g => g.Key == ResourceType.Stamina);
            if (staminaGroups != null && IsLimitStamina(staminaGroups)) result.AddRange(staminaGroups);

            return result;
        }

        bool IsLimitCoin(IGrouping<ResourceType, LimitCheckModel> coinGroups, IGrouping<ResourceType, LimitCheckModel> idleCoinGroups)
        {
            var totalCoin = coinGroups?.Sum(c => c.Amount) ?? 0;
            var totalIdleCoin = idleCoinGroups?.Sum(c => c.Amount) ?? 0;
            var totalAmount = GameRepository.GetGameFetch().UserParameterModel.Coin.Value + totalCoin + totalIdleCoin;

            return MstConfigRepository.GetConfig(MstConfigKey.UserCoinMaxAmount).Value.ToInt() < totalAmount;
        }

        IReadOnlyList<LimitCheckModel> IsLimitItems(IGrouping<ResourceType, LimitCheckModel> groupingModels)
        {
            var result = new List<LimitCheckModel>();
            var filterByCharacterFragment = groupingModels.Where(g => MstItemDataRepository.GetItem(g.MstId).Type != ItemType.CharacterFragment);

            foreach (var item in filterByCharacterFragment)
            {
                var userModel = GameRepository.GetGameFetchOther().UserItemModels.FirstOrDefault(i => i.MstItemId == item.MstId);
                // 所持チェック
                if (userModel == null)
                {
                    // 取得数上限チェック
                    if(MstConfigRepository.GetConfig(MstConfigKey.UserItemMaxAmount).Value.ToInt() < item.Amount)
                        result.Add(item);
                    continue;
                }

                // 所持数 + 取得数上限チェック
                var totalAmount = userModel.Amount + item.Amount;
                if (MstConfigRepository.GetConfig(MstConfigKey.UserItemMaxAmount).Value.ToInt() < totalAmount.Value)
                {
                    result.Add(item);
                }
            }

            return result;
        }

        bool IsLimitFreeDiamond(IGrouping<ResourceType, LimitCheckModel> groupingModels)
        {
            var totalAmount = GameRepository.GetGameFetch().UserParameterModel.FreeDiamond.Value + groupingModels.Sum(g => g.Amount);
            return MstConfigRepository.GetConfig(MstConfigKey.UserFreeDiamondMaxAmount).Value.ToInt() < totalAmount;
        }

        bool IsLimitPaidDiamond(IGrouping<ResourceType, LimitCheckModel> groupingModels)
        {
            var totalAmount = GameRepository.GetGameFetch().UserParameterModel
                .GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId)
                .Value + groupingModels.Sum(g => g.Amount);
            return MstConfigRepository.GetConfig(MstConfigKey.UserPaidDiamondMaxAmount).Value.ToInt() < totalAmount;
        }

        bool IsLimitExp(IGrouping<ResourceType, LimitCheckModel> groupingModels)
        {
            var totalAmount = GameRepository.GetGameFetch().UserParameterModel.Exp.Value +
                              groupingModels.Sum(g => g.Amount);
            return MstConfigRepository
                .GetConfig(MstConfigKey.UserExpMaxAmount).Value.ToInt() < totalAmount;
        }

        bool IsLimitStamina(IGrouping<ResourceType, LimitCheckModel> groupingModels)
        {
            var totalAmount = GameRepository.GetGameFetch().UserParameterModel.CurrentStamina.Value +
                              groupingModels.Sum(g => g.Amount);
            return MstConfigRepository
                .GetConfig(MstConfigKey.UserMaxStaminaAmount).Value.ToInt() < totalAmount;
        }
    }
}
