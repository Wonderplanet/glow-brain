using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Domain.Calculator
{
    public static class GachaContentCalculator
    {
        // ガチャ用の指定リソースを必要個数所持しているか
        public static bool HasGachaResource(
            OprGachaUseResourceModel model,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel,
            string platformId
        )
        {
            switch (model.CostType)
            {
                case CostType.Coin:
                    var coin = gameFetchModel.UserParameterModel.Coin.Value;
                    return coin >= model.CostAmount.Value;

                case CostType.Diamond:
                    var diamond = gameFetchModel.UserParameterModel.GetTotalDiamond(platformId).Value;
                    return diamond >= model.CostAmount.Value;

                case CostType.PaidDiamond:
                    var paidDiamond = gameFetchModel.UserParameterModel
                        .GetPaidDiamondFromPlatform(platformId).Value;
                    return paidDiamond >= model.CostAmount.Value;

                case CostType.Item:
                    var item = gameFetchOtherModel.UserItemModels.FirstOrDefault(x => x.MstItemId == model.MstCostId);

                    return item != null && item.Amount.Value >= model.CostAmount.Value;

                case CostType.Free:
                case CostType.Ad:
                    // 無料・広告ガチャは別途判定
                case CostType.Cash:
                    // 有償ガチャは有償ダイヤで引くため、判定不要
                default:
                    return false;
            }
        }

        public static OprGachaUseResourceModel GetHighestPriorityUseResourceModel(
            IReadOnlyList<OprGachaUseResourceModel> models,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel,
            string platformId
            )
        {
            // 昇順にソート
            var sortedModels = models
                .Where(x=>x.CostType != CostType.Ad)
                .OrderBy(model => model.GachaCostPriority)
                .ToList();

            if(!sortedModels.Any())
            {
                return null;
            }

            foreach (var model in sortedModels)
            {
                if(HasGachaResource(model, gameFetchModel, gameFetchOtherModel, platformId))
                {
                    return model;
                }
            }

            // どのリソースも所持していない場合は最も優先度の低いリソースを返す
            return sortedModels.Last();
        }
        public static GachaThresholdText GetGachaContentThresholdText(
            OprGachaModel oprGachaModel, 
            IOprGachaUpperRepository oprGachaUpperRepository, 
            List<UserDrawCountThresholdModel> userDrawCountThresholdModels, 
            bool isContentView)
        {
            var counts = GetGachaThresholds(
                oprGachaModel, 
                oprGachaUpperRepository, 
                userDrawCountThresholdModels);
            
            return GachaThresholdText.CreateGachaContentThresholdText(
                oprGachaModel.RarityThresholdText, 
                counts.rarityThresholdCount, 
                oprGachaModel.PickupThresholdText, 
                counts.pickupThresholdDiffCount);
        }

        public static GachaThresholdText GetGachaListThresholdText(
            OprGachaModel oprGachaModel, 
            IOprGachaUpperRepository oprGachaUpperRepository, 
            List<UserDrawCountThresholdModel> userDrawCountThresholdModels, 
            bool isContentView)
        {
            var counts = GetGachaThresholds(
                oprGachaModel, 
                oprGachaUpperRepository, 
                userDrawCountThresholdModels);
            
            return GachaThresholdText.CreateGachaListThresholdText(
                oprGachaModel.RarityThresholdText, 
                counts.rarityThresholdCount, 
                oprGachaModel.PickupThresholdText, 
                counts.pickupThresholdDiffCount);
        }

        public static PlayerResourceIconAssetPath GetItemIconAssetPath(
            CostType costType, 
            MasterDataId consId, 
            IMstItemDataRepository mstItemDataRepository)
        {
            switch (costType)
            {
                case CostType.Item:
                    var useResourceItem = mstItemDataRepository.GetItem(consId);
                    return ItemIconAssetPath.FromAssetKey(useResourceItem.ItemAssetKey).ToPlayerResourceIconAssetPath();
                case CostType.PaidDiamond:
                case CostType.Diamond:
                    var assetKey = new DiamondAssetKey();
                    return DiamondIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath();
                case CostType.Ad:
                case CostType.Free:
                default:
                    return PlayerResourceIconAssetPath.Empty;
            }
        }

        static (int? rarityThresholdCount, int? pickupThresholdDiffCount) GetGachaThresholds(
                OprGachaModel oprGachaModel,
                IOprGachaUpperRepository oprGachaUpperRepository,
                List<UserDrawCountThresholdModel> userDrawCountThresholdModels
                )
        {
            // 天井Idから天井情報を取得
            var oprThresholdModels = oprGachaUpperRepository
                .FindByDrawCountThresholdGroupId(oprGachaModel.DrawCountThresholdGroupId).ToList();
            var userThresholdModels = userDrawCountThresholdModels
                .Where(model => model.DrawCountThresholdGroupId ==  oprGachaModel.DrawCountThresholdGroupId)
                .ToList();

            // レアリティ天井
            var oprRarityThresholdModel = oprThresholdModels
                .FirstOrDefault(model => model.UpperType == UpperType.MaxRarity);
            var rarityThreshold = oprThresholdModels
                .FirstOrDefault(model => model.UpperType == UpperType.MaxRarity)?.GachaThresholdCount.Value;
            var userRarityPlayedCount = userThresholdModels
                .FirstOrDefault(model => model.UpperType == oprRarityThresholdModel?.UpperType)?.GachaPlayedCount.Value;

            if (userRarityPlayedCount == null)
            {
                userRarityPlayedCount = 0;
            }

            // ピックアップ天井
            var oprPickupThresholdModel =
                oprThresholdModels.FirstOrDefault(model => model.UpperType == UpperType.Pickup);
            var pickupThreshold = oprThresholdModels
                .FirstOrDefault(model => model.UpperType == UpperType.Pickup)?.GachaThresholdCount.Value;
            var userPickupPlayedCount = userThresholdModels
                .FirstOrDefault(model => model.UpperType == oprPickupThresholdModel?.UpperType)?.GachaPlayedCount.Value;

            if(userPickupPlayedCount == null)
            {
                userPickupPlayedCount = 0;
            }

            var rarityThresholdCount = rarityThreshold - userRarityPlayedCount;
            var pickupThresholdDiffCount = pickupThreshold - userPickupPlayedCount;
            return (rarityThresholdCount, pickupThresholdDiffCount);
        }
    }
}
