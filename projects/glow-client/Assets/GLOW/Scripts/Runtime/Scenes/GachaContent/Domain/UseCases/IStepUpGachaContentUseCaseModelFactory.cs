using GLOW.Core.Domain.Models.OprData;
using GLOW.Scenes.GachaContent.Domain.Model;

namespace GLOW.Scenes.GachaContent.Domain.UseCases
{
    public interface IStepUpGachaContentUseCaseModelFactory
    {
        StepUpGachaContentUseCaseModel Create(OprGachaModel gachaModel);
    }
}

