using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Extensions;
using GLOW.Scenes.BoxGacha.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Domain.Factory
{
    public class BoxGachaInfoModelFactory : IBoxGachaInfoModelFactory
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstBoxGachaDataRepository MstBoxGachaDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        
        BoxGachaInfoModel IBoxGachaInfoModelFactory.Create(
            MasterDataId mstEventId,
            MasterDataId mstBoxGachaId,
            MasterDataId costItemId,
            CostAmount costAmount,
            UserBoxGachaModel userBoxGachaModel)
        {
            var mstBoxGachaGroupModel = MstBoxGachaDataRepository.GetMstBoxGachaGroupModelFirstOrDefault(
                mstBoxGachaId,
                userBoxGachaModel.CurrentBoxLevel);
            if (mstBoxGachaGroupModel.IsEmpty()) return BoxGachaInfoModel.Empty;

            var prizes = CreateSortedPrizeModels(
                mstBoxGachaGroupModel.Prizes,
                userBoxGachaModel.UserBoxGachaPrizeModels);

            var userItemModels = GameRepository.GetGameFetchOther().UserItemModels;
            var userCostItemModel = userItemModels.FirstOrDefault(
                item => item.MstItemId == costItemId, 
                UserItemModel.Empty);
            var costResource = PlayerResourceModelFactory.Create(
                ResourceType.Item,
                costItemId,
                userCostItemModel.Amount.ToPlayerResourceAmount());
            
            var currentDrawnCount = new BoxDrawCount(prizes.Sum(prize => prize.DrawCount.Value));
            var totalStockCount = new BoxGachaPrizeStock(prizes.Sum(prize => prize.Stock.Value));
            
            var mstEventModel = MstEventDataRepository.GetEventFirstOrDefault(mstEventId);
            var remainingTimeSpan = CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, mstEventModel.EndAt);
            var model = new BoxGachaInfoModel(
                totalStockCount,
                userBoxGachaModel.ResetCount,
                currentDrawnCount,
                userBoxGachaModel.CurrentBoxLevel,
                costResource,
                costAmount,
                prizes,
                remainingTimeSpan);

            return model;
        }

        IReadOnlyList<BoxGachaPrizeModel> CreateSortedPrizeModels(
            IReadOnlyList<MstBoxGachaPrizeModel> mstPrizes,
            IReadOnlyList<UserBoxGachaPrizeModel> userPrizes)
        {
            // ソート順
            // ①ピックアップ
            // ②レアリティ降順
            // ③グループソート順昇順
            // ④ソート順昇順
            return mstPrizes.Select(mstPrize =>
                {
                    var userPrize = userPrizes.FirstOrDefault(
                        prize => prize.MstBoxGachaPrizeId == mstPrize.Id, 
                        UserBoxGachaPrizeModel.Empty);
                    return ToBoxGachaPrizeModel(mstPrize, userPrize);
                })
                .OrderByDescending(prize => prize.IsPickUp)
                .ThenByDescending(prize => prize.PrizeResource.Rarity)
                .ThenBy(prize => prize.PrizeResource.GroupSortOrder)
                .ThenBy(prize => prize.PrizeResource.SortOrder)
                .ToList();
        }
        
        BoxGachaPrizeModel ToBoxGachaPrizeModel(
            MstBoxGachaPrizeModel mstBoxGachaPrizeModel,
            UserBoxGachaPrizeModel userBoxGachaPrizeModel)
        {
            return new BoxGachaPrizeModel(
                mstBoxGachaPrizeModel.IsPickUp,
                PlayerResourceModelFactory.Create(
                    mstBoxGachaPrizeModel.ResourceType,
                    mstBoxGachaPrizeModel.ResourceId,
                    mstBoxGachaPrizeModel.ResourceAmount.ToPlayerResourceAmount()),
                userBoxGachaPrizeModel.DrawCount,
                mstBoxGachaPrizeModel.Stock);
        }
    }
}