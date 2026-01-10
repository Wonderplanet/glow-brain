using GLOW.Scenes.PassShop.Domain.Updater;
using Zenject;

namespace GLOW.Scenes.PassShop.Domain.UseCase
{
    public class InitializePassEffectUseCase
    {
        [Inject] IHeldPassEffectRepositoryUpdater HeldPassEffectRepositoryUpdater { get; }
        
        public void InitializeValidPassEffect()
        {
            HeldPassEffectRepositoryUpdater.RegisterPassEffect();
        }
    }
}