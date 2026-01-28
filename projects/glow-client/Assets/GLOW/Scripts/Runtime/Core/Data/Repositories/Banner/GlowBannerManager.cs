using GLOW.Core.Domain.Repositories;
using WonderPlanet.ResourceManagement;

namespace GLOW.Core.Data.Repositories.Banner
{
    public class GlowBannerManager : IGlowBannerManager
    {
        public void ClearAllHttpCache()
        {
            // NOTE: バナー関連の削除
            BannerManager.ClearAllHttpCache();
        }
    }
}
