using System.Collections.Generic;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.UnitReceive.Domain.Model;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCaseModel
{
    public record ExchangeResultUseCaseModel(
        IReadOnlyList<CommonReceiveResourceModel> RewardModels,
        ArtworkFragmentAcquisitionModel ArtworkFragmentAcquisitionModel,
        UnitReceiveModel UnitReceiveViewModel)
    {
        public static ExchangeResultUseCaseModel Empty { get; } = new(
            new List<CommonReceiveResourceModel>(),
            ArtworkFragmentAcquisitionModel.Empty,
            UnitReceiveModel.Empty);
    }
}
