using GLOW.Core.Data.Services;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public class LoginInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: 認証処理を読み込む
            Container.BindInterfacesTo<AuthenticateService>().AsCached();
            // NOTE: ログイン時の情報を読み込む
            Container.BindInterfacesTo<UserService>().AsCached();
        }
    }
}
