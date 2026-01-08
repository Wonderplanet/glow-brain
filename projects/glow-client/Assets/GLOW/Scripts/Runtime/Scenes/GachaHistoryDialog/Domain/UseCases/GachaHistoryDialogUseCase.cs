using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using GLOW.Scenes.GachaHistoryDialog.Domain.Models;
using GLOW.Scenes.GachaList.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.GachaHistoryDialog.Domain.UseCases
{
    public class GachaHistoryDialogUseCase
    {
        [Inject] IGachaService GachaService { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
               
        public async UniTask<GachaHistoryUseCaseModel> GetGachaHistoryDialogUseCaseModel(
            CancellationToken cancellationToken)
        {
            var result = await GachaService.History(cancellationToken);

            if (result.GachaHistoryModels.IsEmpty())
            {
                return GachaHistoryUseCaseModel.Empty;
            }

            return CreateGachaHistoryUseCaseModel(result);
        }

        GachaHistoryUseCaseModel CreateGachaHistoryUseCaseModel(GachaHistoryResultModel resultModel)
        {
            var cellModels = new List<GachaHistoryCellModel>();
            var detailModels = new List<GachaHistoryDetailModel>();
            
            foreach (var gachaHistoryModel in resultModel.GachaHistoryModels)
            {
                var cellModel = CreateGachaHistoryCellModel(gachaHistoryModel);
                cellModels.Add(cellModel);

                var detailCellModels = CreateGachaHistoryDetailCellModels(gachaHistoryModel);
                detailModels.Add(detailCellModels);

            }
            
            return new GachaHistoryUseCaseModel(cellModels, detailModels);
        }
        
        GachaHistoryCellModel CreateGachaHistoryCellModel(GachaHistoryModel gachaHistoryModel)
        {
            var oprGachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaHistoryModel.OprGachaId);
                
            var costItemPlayerResourceIconAssetPath = CreateCostItemPlayerResourceIconAssetPath(
                gachaHistoryModel.MstCostId, 
                gachaHistoryModel.CostType);

            CostAmount costAmount;
            if (gachaHistoryModel.CostType == CostType.Ad)
            {
                // 広告ガシャの場合、コスト表示は1にする
                costAmount = new CostAmount(1);
            }
            else
            {
                costAmount = gachaHistoryModel.CostAmount;
            }
                
            return new GachaHistoryCellModel(
                gachaHistoryModel.PlayedAt,
                oprGachaModel.GachaName,
                new AdDrawFlag(gachaHistoryModel.CostType == CostType.Ad),
                costItemPlayerResourceIconAssetPath,
                costAmount);
        }
        
        GachaHistoryDetailModel CreateGachaHistoryDetailCellModels(GachaHistoryModel gachaHistoryModel)
        {
            var detailCellModels = gachaHistoryModel.GachaHistoryRewardModels
                .Select(CreateGachaHistoryDetailCellModel)
                .OrderBy(model => model.SortOrder)
                .ToList();
            
            return new GachaHistoryDetailModel(detailCellModels);
        }
        
        GachaHistoryDetailCellModel CreateGachaHistoryDetailCellModel(GachaHistoryRewardModel model)
        {
            var preconversionResource = model.RewardModel.PreConversionResource;
                
                var playerResourceModel = PlayerResourceModelFactory.Create(
                    model.RewardModel.ResourceType,
                    model.RewardModel.ResourceId,
                    model.RewardModel.Amount);


                var preconversionResourceModel = preconversionResource.IsEmpty()
                    ? PlayerResourceModel.Empty
                    : PlayerResourceModelFactory.Create(
                        preconversionResource.ResourceType,
                        preconversionResource.ResourceId,
                        preconversionResource.ResourceAmount.ToPlayerResourceAmount());

                GachaHistoryDetailCellModel detailCellModel;
                
                if (playerResourceModel.Type == ResourceType.Unit)
                {
                    // 初獲得キャラの場合
                    var mstCharacterModel = MstCharacterDataRepository.GetCharacter(playerResourceModel.Id);
                    
                    detailCellModel = new GachaHistoryDetailCellModel(
                        model.SortOrder,
                        playerResourceModel,
                        mstCharacterModel.Name,
                        PlayerResourceModel.Empty);
                }
                else if (preconversionResourceModel.Type == ResourceType.Unit) 
                {
                    // キャラ変換の場合
                    var mstCharacterModel = MstCharacterDataRepository.GetCharacter(preconversionResourceModel.Id);
                    
                    detailCellModel = new GachaHistoryDetailCellModel(
                        model.SortOrder,
                        preconversionResourceModel,
                        mstCharacterModel.Name,
                        playerResourceModel);
                }
                else if (preconversionResourceModel.IsEmpty())
                {
                    // アイテム獲得で変換がない場合、アイテムを個数表示する
                    detailCellModel = new GachaHistoryDetailCellModel(
                        model.SortOrder,
                        playerResourceModel,
                        CharacterName.Empty, 
                        playerResourceModel);
                }
                else
                {
                    // ユニット以外に変換はないのでここは通らない想定 変換前の排出物と変換後のアイテム個数表示
                    detailCellModel = new GachaHistoryDetailCellModel(
                        model.SortOrder,
                        preconversionResourceModel,
                        CharacterName.Empty, 
                        playerResourceModel);
                }

                return detailCellModel;
        }
        
        PlayerResourceIconAssetPath CreateCostItemPlayerResourceIconAssetPath(MasterDataId mstCostId, CostType costType)
        {
            switch (costType)
            {
                case CostType.Coin:
                    var coinAssetKey = new CoinAssetKey();
                    return CoinIconAssetPath.FromAssetKey(coinAssetKey).ToPlayerResourceIconAssetPath();
                
                case CostType.Diamond:
                case CostType.PaidDiamond:
                case CostType.Free: // 無料の場合はプリズム扱い
                    var diamondAssetKey = new DiamondAssetKey();
                    return DiamondIconAssetPath.FromAssetKey(diamondAssetKey).ToPlayerResourceIconAssetPath();
                    
                case CostType.Item:
                    var item = MstItemDataRepository.GetItem(mstCostId);
                    return ItemIconAssetPath.FromAssetKey(item.ItemAssetKey).ToPlayerResourceIconAssetPath();
                
                case CostType.Ad:
                    // 広告ガシャの場合はView側で広告アイコンを表示するため、空文字を返す
                    return PlayerResourceIconAssetPath.Empty;
              
                case CostType.Cash: // 現金でのガシャはない(有償プリズムでのみ)
                default:
                    return PlayerResourceIconAssetPath.Empty;
            }
        }
    }
}