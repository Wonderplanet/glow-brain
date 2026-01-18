using GLOW.Modules.CommonToast.Presentation;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ClientUpdate.Presentation.View
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-6_アップデートダイアログ
    /// </summary>
    public class ClientUpdateDialogViewController : UIViewController<ClientUpdateDialogView>, IEscapeResponder
    {
        [Inject] IClientUpdateDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }
        
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            
            EscapeResponderRegistry.Unregister(this);
        }

        [UIAction]
        void OnButtonOpenPlatformStore()
        {
            ViewDelegate.OnPlatformStoreSelected();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }
            CommonToastWireFrame.ShowInvalidOperationMessage();
            return true;
        }
    }
}
