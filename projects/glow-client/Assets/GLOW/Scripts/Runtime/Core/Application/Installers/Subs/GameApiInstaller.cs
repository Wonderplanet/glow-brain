using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.DataStores.Agreement;
using GLOW.Core.Data.DataStores.Announcement;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public class GameApiInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.Bind<SystemApi>().AsCached();
            Container.Bind<GameApi>().AsCached();
            Container.Bind<UserApi>().AsCached();
            Container.Bind<ShopApi>().AsCached();
            Container.Bind<StageApi>().AsCached();
            Container.Bind<AdventBattleApi>().AsCached();
            Container.Bind<PartyApi>().AsCached();
            Container.Bind<OutpostApi>().AsCached();
            Container.Bind<IdleIncentiveApi>().AsCached();
            Container.Bind<MissionApi>().AsCached();
            Container.Bind<MessageApi>().AsCached();
            Container.Bind<UnitApi>().AsCached();
            Container.Bind<ItemApi>().AsCached();
            Container.Bind<EncyclopediaApi>().AsCached();
            Container.Bind<AnnouncementApi>().AsCached();
            Container.Bind<GachaApi>().AsCached();
            Container.Bind<TutorialApi>().AsCached();
            Container.Bind<AgreementApi>().AsCached();
            Container.Bind<PvpApi>().AsCached();
            Container.Bind<BoxGachaApi>().AsCached();
        }
    }
}
