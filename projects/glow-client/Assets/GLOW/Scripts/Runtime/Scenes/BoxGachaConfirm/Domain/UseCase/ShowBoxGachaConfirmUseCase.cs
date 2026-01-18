using System;
using Cysharp.Text;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using GLOW.Scenes.BoxGachaConfirm.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BoxGachaConfirm.Domain.UseCase
{
    public class ShowBoxGachaConfirmUseCase
    {
        [Inject] IMstBoxGachaDataRepository MstBoxGachaDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        
        public BoxGachaConfirmDialogModel ShowDrawConfirm(MasterDataId mstBoxGachaId)
        {
            var mstBoxGachaModel = MstBoxGachaDataRepository.GetMstBoxGachaModelFirstOrDefault(mstBoxGachaId);
            
            var userItemModels = GameRepository.GetGameFetchOther().UserItemModels;
            var userCostItemModel = userItemModels.FirstOrDefault(
                item => item.MstItemId == mstBoxGachaModel.CostId, 
                UserItemModel.Empty);
            var costItem = MstItemDataRepository.GetItem(mstBoxGachaModel.CostId);

            if (mstBoxGachaModel.CostAmount.IsZero())
            {
                throw new Exception(
                    ZString.Format(
                        "いいジャンくじを引くための必要なアイテム数が0になってます。 MstBoxGachaId:{0}", 
                        mstBoxGachaId.Value));
            }
            
            // 引ける回数を計算
            var canSelectDrawCount = new GachaDrawCount(
                userCostItemModel.Amount.Value / (int)mstBoxGachaModel.CostAmount.Value);

            return new BoxGachaConfirmDialogModel(
                costItem.Name,
                ItemIconAssetPath.FromAssetKey(costItem.ItemAssetKey),
                userCostItemModel.Amount,
                mstBoxGachaModel.CostAmount,
                mstBoxGachaModel.Name,
                canSelectDrawCount,
                new BoxGachaDrawableFlag(!canSelectDrawCount.IsZero()));
        }
    }
}