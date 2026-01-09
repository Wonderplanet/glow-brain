using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaDetailDialog.Domain.UseCases;
using GLOW.Scenes.GachaList.Domain.Definition.Service;
using GLOW.Scenes.GachaRatio.Domain.Model;
using Zenject;

namespace GLOW.Scenes.GachaRatio.Domain.UseCases
{
    public class GachaRatioDialogUseCase
    {
        [Inject] IGachaService GachaService { get; }
        [Inject] IGachaRatioPageModelFactory GachaRatioPageModelFactory { get; }
        [Inject] IOprGachaRepository OprGachaRepository { get; }

        public async UniTask<GachaRatioDialogUseCaseModel> GetGachaRatioUseCaseModel(CancellationToken cancellationToken, MasterDataId masterDataId)
        {
            GachaPrizeResultModel resultModel = await GachaService.Prize(cancellationToken, masterDataId);
            
            var gachaFixedPrizeDescription = GetGachaFixedPrizeDescription(masterDataId);

            return new GachaRatioDialogUseCaseModel(
                GachaRatioPageModelFactory.Create(resultModel.NormalPrizePageModel),
                GachaRatioPageModelFactory.Create(resultModel.NormalPrizeInFixedPageModel),
                GachaRatioPageModelFactory.Create(resultModel.UpperPrizeInMaxRarityPageModel),
                GachaRatioPageModelFactory.Create(resultModel.UpperPrizeInPickupPageModel),
                gachaFixedPrizeDescription);
        }

        GachaFixedPrizeDescription GetGachaFixedPrizeDescription(MasterDataId gachaId)
        {
            var gachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaId);
            return gachaModel.GachaFixedPrizeDescription;
        }
    }
}