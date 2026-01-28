using GLOW.Core.Domain.Models;
using GLOW.Scenes.GachaLineupDialog.Domain.Models;

namespace GLOW.Scenes.GachaLineupDialog.Domain.Factory
{
    public interface IGachaLineupPageModelFactory
    {
        GachaLineupPageModel Create(GachaPrizePageModel prizePageModel);
    }
}