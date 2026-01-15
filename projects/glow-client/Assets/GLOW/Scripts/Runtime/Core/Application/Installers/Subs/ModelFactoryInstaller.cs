using GLOW.Core.Domain.ModelFactories;
using GLOW.Scenes.BattleResult.Domain.Factory;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class ModelFactoryInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindInterfacesTo<PlayerResourceModelFactory>().AsCached();
            Container.BindInterfacesTo<UserLevelUpEffectModelFactory>().AsCached();
            Container.BindInterfacesTo<UserExpGainModelsFactory>().AsCached();
            Container.BindInterfacesTo<SpecialRoleSpecialAttackFactory>().AsCached();
            Container.BindInterfacesTo<SpecialAttackInfoModelFactory>().AsCached();
            Container.BindInterfacesTo<ArtworkFragmentAcquisitionModelFactory>().AsCached();
        }
    }
}
