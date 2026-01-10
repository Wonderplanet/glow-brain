using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.PackShopGacha.Domain.Models;
using Zenject;

namespace GLOW.Scenes.PackShopGacha.Domain.UseCases
{
    public class PackShopGachaUseCase
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }
        
        public PackShopGachaUseCaseModel GetPackShopGachaUseCaseModel(MasterDataId ticketId)
        {
            // チケットで引けるガシャのIdを取得
            var useResourceModels = OprGachaUseResourceRepository.GetOprGachaUseResourceModelsByItemId(ticketId);
            var gachaIds = useResourceModels.Select(x => x.OprGachaId).Distinct().ToList();

            var oprGachaModels = OprGachaRepository.GetOprGachaModelsByDataTime(TimeProvider.Now);
            
            var filteredOprGachaModels = oprGachaModels
                .Where(x => gachaIds.Contains(x.GachaId))
                .ToList();
            
            if (filteredOprGachaModels.Count == 0)
            {
                return PackShopGachaUseCaseModel.Empty;
            }
            
            var sortedOprGachaModels = GachaEvaluator.SortOprGachaModelByPriority(filteredOprGachaModels);
            
            var bannerModels = sortedOprGachaModels
                .Select(x => new PackShopGachaBannerModel(
                    x.GachaId, 
                    GachaBannerAssetPath.FromAssetKey(x.GachaBannerAssetKey)))
                .ToList();

            return new PackShopGachaUseCaseModel(bannerModels);
        }
    }
}