using GLOW.Scenes.Home.Domain.Models;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public sealed class HomeMainBadgeUseCase
    {
        [Inject] IHomeMainBadgeFactory HomeMainBadgeFactory { get; }
        public HomeMainBadgeModel GetHomeMainBadgeModel()
        {
            return HomeMainBadgeFactory.GetHomeMainBadgeModel();
        }
    }
}
