using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.GachaList.Domain.Model;

namespace GLOW.Scenes.GachaContent.Domain.UseCases
{
    public interface IGachaListElementUseCaseModelFactory
    {
        GachaListElementUseCaseModel Create(MasterDataId oprGachaId);
    }
}