using GLOW.Core.Domain.Models;
using GLOW.Scenes.GachaRatio.Domain.Model;

namespace GLOW.Scenes.GachaDetailDialog.Domain.UseCases
{
    public interface IGachaRatioPageModelFactory
    {
        GachaRatioPageModel Create(GachaPrizePageModel prizePageModel);
    }
}