using UIKit;
using Zenject;

namespace GLOW.Scenes.HomeMenu.Presentation.View
{
    public class HomeMenuViewController : UIViewController<HomeMenuView>
    {
        [Inject] IHomeMenuDelegate ViewDelegate { get; }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }

        [UIAction]
        void OnSettingSelected()
        {
            ViewDelegate.OnSettingSelected();
        }

        [UIAction]
        void OnAccountCooperateSelected()
        {
            ViewDelegate.OnAccountCooperateSelected();
        }

        [UIAction]
        void OnHelpSelected()
        {
            ViewDelegate.OnHelpSelected();
        }

        [UIAction]
        void OnCommunitySelected()
        {
            ViewDelegate.OnCommunitySelected();
        }

        [UIAction]
        void OnInquirySelected()
        {
            ViewDelegate.OnInquirySelected();
        }

        [UIAction]
        void OnOtherMenuSelected()
        {
            ViewDelegate.OnOtherMenuSelected();
        }

        [UIAction]
        void OnTitleBackSelected()
        {
            ViewDelegate.OnTitleBackSelected();
        }
    }
}
