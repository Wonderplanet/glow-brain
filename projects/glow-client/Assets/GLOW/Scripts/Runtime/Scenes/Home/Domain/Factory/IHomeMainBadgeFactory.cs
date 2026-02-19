using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public interface IHomeMainBadgeFactory
    {
        HomeMainBadgeModel GetHomeMainBadgeModel();
    }
}