using GLOW.Scenes.PassShop.Domain.Factory;
using GLOW.Scenes.PassShop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.PassShop.Domain.UseCase
{
    public class GetHeldAdSkipPassInfoUseCase
    {
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }
        
        public HeldAdSkipPassInfoModel GetHeldAdSkipPassInfo()
        {
            return HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();
        }
    }
}