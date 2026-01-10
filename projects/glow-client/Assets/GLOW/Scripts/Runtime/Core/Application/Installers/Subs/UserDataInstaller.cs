using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Repositories;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class UserDataInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: ユーザー情報関連をインストール

            Container.BindInterfacesTo<UserPropertyDataLocalDataStore>().AsCached();
            Container.BindInterfacesTo<UserPropertyRepository>().AsCached();
        }
    }
}
