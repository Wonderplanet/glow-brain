using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaDetailDialog.Domain.UseCases;
using GLOW.Scenes.GachaLineupDialog.Domain.Factory;
using GLOW.Scenes.GachaLineupDialog.Domain.Models;
using GLOW.Scenes.GachaList.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.GachaLineupDialog.Domain.UseCases
{
    public class GachaLineupDialogUseCase
    {
        [Inject] IGachaService GachaService { get; }
        [Inject] IGachaLineupPageModelFactory GachaLineupPageModelFactory { get; }
        [Inject] IOprGachaRepository OprGachaRepository { get; }

        public async UniTask<GachaLineupDialogUseCaseModel> GetGachaLineupUseCaseModel(CancellationToken cancellationToken, MasterDataId masterDataId)
        {
            GachaPrizeResultModel resultModel = await GachaService.Prize(cancellationToken, masterDataId);
            var gachaFixedPrizeDescription = GetGachaFixedPrizeDescription(masterDataId);

            return new GachaLineupDialogUseCaseModel(
                GachaLineupPageModelFactory.Create(resultModel.NormalPrizePageModel),
                GachaLineupPageModelFactory.Create(resultModel.NormalPrizeInFixedPageModel),
                GachaLineupPageModelFactory.Create(resultModel.UpperPrizeInMaxRarityPageModel),
                GachaLineupPageModelFactory.Create(resultModel.UpperPrizeInPickupPageModel),
                gachaFixedPrizeDescription);
        }
        
        GachaFixedPrizeDescription GetGachaFixedPrizeDescription(MasterDataId gachaId)
        {
            var gachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaId);
            return gachaModel.GachaFixedPrizeDescription;
        }
    }
}
