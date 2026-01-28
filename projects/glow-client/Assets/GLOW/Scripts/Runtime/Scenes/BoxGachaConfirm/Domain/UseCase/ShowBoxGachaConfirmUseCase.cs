using System;
using Cysharp.Text;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.BoxGacha.Domain.Factory;
using GLOW.Scenes.BoxGacha.Domain.Provider;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using GLOW.Scenes.BoxGachaConfirm.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BoxGachaConfirm.Domain.UseCase
{
    public class ShowBoxGachaConfirmUseCase
    {
        [Inject] IBoxGachaInfoModelFactory BoxGachaInfoModelFactory { get; }
        [Inject] IMstBoxGachaProvider MstBoxGachaProvider { get; }
        [Inject] IUserBoxGachaCacheRepository UserBoxGachaCacheRepository { get; }
        
        public BoxGachaConfirmDialogModel ShowDrawConfirm(MasterDataId mstEventId)
        {
            var mstBoxGachaModel = MstBoxGachaProvider.GetMstBoxGachaModelByEventId(mstEventId);
            
            if (mstBoxGachaModel.CostAmount.IsZero())
            {
                throw new Exception(
                    ZString.Format(
                        "いいジャンくじを引くための必要なアイテム数が0になってます。 MstBoxGachaId:{0}", 
                        mstBoxGachaModel.Id.Value));
            }
            
            var userBoxGachaModel = UserBoxGachaCacheRepository.GetFirstOrDefault(mstBoxGachaModel.Id);
            
            var infoModel = BoxGachaInfoModelFactory.Create(
                mstEventId,
                mstBoxGachaModel.Id,
                mstBoxGachaModel.CostId,
                mstBoxGachaModel.CostAmount,
                userBoxGachaModel);
            
            // 引ける回数を計算
            var canSelectDrawCount = new GachaDrawCount(
                infoModel.CostResource.Amount.Value / (int)mstBoxGachaModel.CostAmount.Value);
            
            // 残り引ける回数を計算
            var remainingDrawCount = new GachaDrawCount(
                infoModel.TotalStockCount.Value - infoModel.CurrentBoxTotalDrawnCount.Value);

            return new BoxGachaConfirmDialogModel(
                infoModel.CostResource.Name.ToItemName(),
                ItemIconAssetPath.FromAssetKey(infoModel.CostResource.AssetKey),
                infoModel.CostResource.Amount.ToItemAmount(),
                mstBoxGachaModel.CostAmount,
                mstBoxGachaModel.Name,
                GachaDrawCount.Min(canSelectDrawCount, remainingDrawCount), 
                new BoxGachaDrawableFlag(!canSelectDrawCount.IsZero()));
        }
    }
}