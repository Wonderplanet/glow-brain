using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.GachaDetailDialog.Domain.UseCases;
using GLOW.Scenes.GachaList.Domain.Definition.Service;
using GLOW.Scenes.StepupGachaRatioDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.StepupGachaRatioDialog.Domain.UseCases
{
    public class StepupGachaRatioDialogUseCase
    {
        [Inject] IGachaService GachaService { get; }
        [Inject] IGachaRatioPageModelFactory GachaRatioPageModelFactory { get; }
        [Inject] IOprStepUpGachaStepRepository OprStepUpGachaStepRepository { get; }

        public async UniTask<StepupGachaRatioDialogUseCaseModel> GetStepupGachaRatioUseCaseModel(
            CancellationToken cancellationToken,
            MasterDataId oprGachaId)
        {
            var resultModel = await GachaService.Prize(cancellationToken, oprGachaId);

            if (resultModel.IsEmpty())
            {
                return StepupGachaRatioDialogUseCaseModel.Empty;
            }
            var stepupModel = resultModel.StepupGachaPrizeResultModel;
            var oprStepupGachaStepModels = OprStepUpGachaStepRepository.GetOprStepUpGachaModels(oprGachaId);
            
            var stepUseCaseModels = CreateStepUseCaseModels(stepupModel, oprStepupGachaStepModels);

            return new StepupGachaRatioDialogUseCaseModel(stepUseCaseModels);
        }

        IReadOnlyList<StepupGachaRatioStepUseCaseModel> CreateStepUseCaseModels(
            StepupGachaPrizeResultModel resultModel,
            IReadOnlyList<OprStepUpGachaStepModel> oprStepupGachaStepModels)
        {
            if (resultModel.IsEmpty())
            {
                return new List<StepupGachaRatioStepUseCaseModel>();
            }
            
            return resultModel.StepupGachaPrizeStepModels
                .Select(stepModel => new StepupGachaRatioStepUseCaseModel(
                    stepModel.StepNumber,
                    oprStepupGachaStepModels.FirstOrDefault(
                            model => model.StepNumber == stepModel.StepNumber,
                            OprStepUpGachaStepModel.Empty)
                        .FixedPrizeDescription,
                    GachaRatioPageModelFactory.Create(stepModel.NormalPrizePageModel),
                    GachaRatioPageModelFactory.Create(stepModel.FixedPrizePageModel)))
                .ToList();
        }
    }
}
