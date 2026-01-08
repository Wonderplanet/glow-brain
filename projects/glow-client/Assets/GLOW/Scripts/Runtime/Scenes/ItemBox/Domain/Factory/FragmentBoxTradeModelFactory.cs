using System;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ItemBox.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.Factory
{
    public class FragmentBoxTradeModelFactory : IFragmentBoxTradeModelFactory
    {
        [Inject] IMstItemRarityTradeRepository MstItemRarityTradeRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        FragmentBoxTradeModel IFragmentBoxTradeModelFactory.CreateFragmentBoxTradeModel(
            MasterDataId offerItemId)
        {
            var items = MstItemDataRepository.GetItems();
            var mstOfferItem = items.FirstOrDefault(item => item.Id == offerItemId, MstItemModel.Empty);
            var usrOfferItem = GameRepository.GetGameFetchOther().UserItemModels
                .FirstOrDefault(item => item.MstItemId == offerItemId, UserItemModel.Empty);
            var mstReceivedItem = items
                .Where(data => data.Type == ItemType.SelectionFragmentBox)
                .FirstOrDefault(data => data.Rarity == mstOfferItem.Rarity, MstItemModel.Empty);
            var offerItemModel = ItemModelTranslator.ToItemModel(mstOfferItem, usrOfferItem.Amount);
            var receivedItem = ItemModelTranslator.ToItemModel(mstReceivedItem);

            var itemRarityTradeModel = CreateItemRarityTradeModel(mstOfferItem.Rarity);

            // 一つ交換に必要なかけらの数
            var offerFragmentAmountForOneTrade = itemRarityTradeModel.TradeCostAmount;

            // 交換可能な最大数
            var userItemTradeModel = GetUserItemTradeModel(mstReceivedItem.Id, itemRarityTradeModel.TradeResetType);

            var amountForTradable = TradableAmount.Infinity;
            if (!itemRarityTradeModel.MaxTradableAmount.IsInfinity())
            {
                amountForTradable = TradableAmount.Max(
                    itemRarityTradeModel.MaxTradableAmount - userItemTradeModel.TradeAmount,
                    TradableAmount.Zero);
            }

            var remainingReceivableAmount = usrOfferItem.Amount.ToTradableAmount();

            // 所持数と一つ交換に必要な数から計算する
            // 一つ交換に必要な数が0の場合は割り算を行わない
            if(!offerFragmentAmountForOneTrade.IsZero())
            {
                remainingReceivableAmount /= offerFragmentAmountForOneTrade;
            }

            // 交換可能な最大数が設定されている場合は、交換可能な最大数と残りの受け取り可能数の小さい方を交換可能な数とする
            if (!amountForTradable.IsInfinity())
            {
                remainingReceivableAmount = TradableAmount.Min(remainingReceivableAmount, amountForTradable);
            }

            // 交換終了時間までの残り時間の計算
            var remainingTime =  GetRemainingTimeSpan(itemRarityTradeModel.TradeResetType);

            return new FragmentBoxTradeModel(
                offerItemModel,
                receivedItem,
                amountForTradable,
                remainingReceivableAmount,
                offerFragmentAmountForOneTrade,
                itemRarityTradeModel.TradeResetType,
                remainingTime
            );
        }

        UserItemTradeModel GetUserItemTradeModel(MasterDataId receivedItemId, ItemTradeResetType resetType)
        {
            var userItemTradeModels = GameRepository.GetGameFetchOther().UserItemTradeModels;
            var userItemTradeModel = userItemTradeModels
                .FirstOrDefault(data => data.MstItemId == receivedItemId, UserItemTradeModel.Empty);

            if (userItemTradeModel.IsEmpty()) return userItemTradeModel;

            var isReset = resetType switch
            {
                ItemTradeResetType.Daily => DailyResetTimeCalculator.IsPastDailyRefreshTime(userItemTradeModel.TradeAmountResetAt.Value),
                ItemTradeResetType.Weekly => DailyResetTimeCalculator.IsPastWeeklyRefreshTime(userItemTradeModel.TradeAmountResetAt.Value),
                ItemTradeResetType.Monthly => DailyResetTimeCalculator.IsPastMonthlyRefreshTime(userItemTradeModel.TradeAmountResetAt.Value),
                _ => false
            };

            if (isReset) return UserItemTradeModel.Empty;

            return userItemTradeModel;
        }

        MstItemRarityTradeModel CreateItemRarityTradeModel(Rarity rarity)
        {
            var itemRarityTradeModels = MstItemRarityTradeRepository.GetMstItemRarityTradeList();
            var itemRarityTradeModel = itemRarityTradeModels
                .FirstOrDefault(data => data.Rarity == rarity, MstItemRarityTradeModel.Empty);

            return itemRarityTradeModel;
        }

        RemainingTimeSpan GetRemainingTimeSpan(ItemTradeResetType resetType)
        {
            if(resetType == ItemTradeResetType.None)
            {
                return RemainingTimeSpan.Empty;
            }

            var nextResetDateTime =  resetType switch
            {
                ItemTradeResetType.Daily => DailyResetTimeCalculator.GetRemainingTimeToDailyReset(),
                ItemTradeResetType.Weekly => DailyResetTimeCalculator.GetRemainingTimeToWeeklyReset(),
                ItemTradeResetType.Monthly => DailyResetTimeCalculator.GetRemainingTimeToMonthlyReset(),
                _ =>  TimeSpan.Zero
            };

            return new RemainingTimeSpan(nextResetDateTime);
        }
    }
}
