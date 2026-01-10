using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Scenes.ItemDetail.Domain.Models;
using GLOW.Scenes.PackShop.Domain.Calculator;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Domain.Factory
{
    public class ItemDetailAvailableLocationModelFactory : IItemDetailAvailableLocationModelFactory
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IMstItemTransitionDataRepository MstItemTransitionDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }

        public ItemDetailAvailableLocationModel Create(ResourceType resourceType, MasterDataId masterDataId)
        {
            if (resourceType == ResourceType.Coin || resourceType == ResourceType.IdleCoin)
            {
                // 強化クエストのIDを取得(なければコンテンツTOP
                var enhanceQuestId = MstQuestDataRepository.GetMstQuestModels()
                    .Where(q => q.QuestType == QuestType.Enhance)
                    .Where(q => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, q.StartDate, q.EndDate))
                    .Select(q => q.Id).FirstOrDefault() ?? MasterDataId.Empty;

                var earnLocation1 = new ItemDetailEarnLocationModel(ItemTransitionType.EventQuest, enhanceQuestId, TransitionPossibleFlag.True);
                var earnLocation2 = new ItemDetailEarnLocationModel(ItemTransitionType.Patrol, MasterDataId.Empty, TransitionPossibleFlag.True);

                return new ItemDetailAvailableLocationModel(earnLocation1, earnLocation2);
            }
            else
            {
                var modelData = MstItemTransitionDataRepository.GetEarnLocationFirstOrDefault(masterDataId);

                var earnLocation1 = new ItemDetailEarnLocationModel(modelData.TransitionType1, modelData.TransitionMasterDataId1, CheckTransitionPossible(modelData.TransitionType1, modelData.TransitionMasterDataId1));
                var earnLocation2 = new ItemDetailEarnLocationModel(modelData.TransitionType2, modelData.TransitionMasterDataId2, CheckTransitionPossible(modelData.TransitionType2, modelData.TransitionMasterDataId2));

                return new ItemDetailAvailableLocationModel(earnLocation1, earnLocation2);
            }
        }

        TransitionPossibleFlag CheckTransitionPossible(ItemTransitionType transitionType, MasterDataId id)
        {
            switch (transitionType)
            {
                case ItemTransitionType.MainQuest:
                    return new TransitionPossibleFlag(IsMainQuestMasterEnabled(id));
                case ItemTransitionType.EventQuest:
                    return new TransitionPossibleFlag(IsEventQuestMasterEnabled(id));
                case ItemTransitionType.ShopItem:
                case ItemTransitionType.Pack:
                    return new TransitionPossibleFlag(IsShopMasterEnabled(id));
                case ItemTransitionType.None:
                    return TransitionPossibleFlag.False;
                default:
                    return TransitionPossibleFlag.True;
            }
        }

        bool IsMainQuestMasterEnabled(MasterDataId mstQuestId)
        {
            // メインクエストのstage_numberからPrevStageのクリア数が1以上であればtrue
            var mstStages = MstStageDataRepository.GetMstStages();
            var userStages = GameRepository.GetGameFetch().StageModels;

            var mstStage = mstStages.FirstOrDefault(model => model.MstQuestId == mstQuestId && model.StageNumber.Value == 1);
            if (mstStage == null) return false;

            // 開放するのにクリアする必要があるステージが無ければtrue
            if (mstStage.ReleaseRequiredMstStageId == null) return true;
            if (mstStage.ReleaseRequiredMstStageId.IsEmpty()) return true;

            return userStages.Any(model => model.MstStageId == mstStage.ReleaseRequiredMstStageId && !model.ClearCount.IsEmpty());
        }

        bool IsEventQuestMasterEnabled(MasterDataId mstQuestId)
        {
            // イベントクエストのIDがなければコンテンツTOPに遷移させるのでtrue
            if (mstQuestId == MasterDataId.Empty)
                return true;

            var nowTime = TimeProvider.Now;
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstQuestId);

            if(mstQuest == null) return false;
            return CalculateTimeCalculator.IsValidTime(nowTime, mstQuest.StartDate, mstQuest.EndDate);
        }

        bool IsShopMasterEnabled(MasterDataId mstShopItemId)
        {
            var shopItemModels = MstShopProductDataRepository.GetShopProducts();
            var packModels = MstShopProductDataRepository.GetPacks();
            var storeProductModels = ValidatedStoreProductRepository.GetValidatedStoreProducts();

            // アイテムの解放日程チェック
            if (shopItemModels.Any(model => model.Id == mstShopItemId))
            {
                var nowTime = TimeProvider.Now;
                var itemModel = shopItemModels.First(model => model.Id == mstShopItemId);
                return CalculateTimeCalculator.IsValidTime(nowTime, itemModel.StartDate, itemModel.EndDate);
            }
            // パックの解放条件チェック
            else if (packModels.Any(model => model.Id == mstShopItemId))
            {
                var packModel = packModels.First(model => model.Id == mstShopItemId);

                // 解放条件の指定が無ければ通す
                if (packModel.SaleConditionValue.IsEmpty()) return true;

                // 条件があれば条件をチェックする
                switch (packModel.SaleConditionValue.Condition)
                {
                    case SaleCondition.StageClear:
                        return PackShopProductCalculator.IsValidStageClear(packModel, GameRepository);
                    case SaleCondition.UserLevel:
                        return PackShopProductCalculator.IsValidUserLevel(packModel, GameRepository);
                    case SaleCondition.ElaspeDays:
                        return PackShopProductCalculator.IsValidElapseDays(packModel);
                }
            }
            // ジュエルは条件なさそうなのでIDだけチェック
            else if (storeProductModels.Any(model => model.MstStoreProduct.Id == mstShopItemId))
            {
                return true;
            }
            // MasterIdに該当が無ければfalse
            return false;
        }
    }
}
