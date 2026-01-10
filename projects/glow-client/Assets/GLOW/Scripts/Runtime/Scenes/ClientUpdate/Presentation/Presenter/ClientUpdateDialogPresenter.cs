using GLOW.Scenes.ClientUpdate.Presentation.View;
using WPFramework.Modules.Platform;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ClientUpdate.Presentation.Presenter
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-6_アップデートダイアログ
    /// </summary>
    public class ClientUpdateDialogPresenter : IClientUpdateDialogViewDelegate
    {
        [Inject] PlatformStoreLinker PlatformStoreLinker { get; }
        [Inject] ClientUpdateDialogViewController ViewController { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        void IClientUpdateDialogViewDelegate.OnPlatformStoreSelected()
        {
            // NOTE: ストアを開いた後に表示は閉じない
            PlatformStoreLinker.OpenURL();
        }
    }
}
