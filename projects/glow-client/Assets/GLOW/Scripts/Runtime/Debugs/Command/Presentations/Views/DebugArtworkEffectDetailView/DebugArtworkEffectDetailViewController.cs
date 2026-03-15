using GLOW.Debugs.Command.Domains.UseCase;
using UIKit;

namespace GLOW.Scenes.DebugArtworkEffectDetail.Presentation
{
    public class DebugArtworkEffectDetailViewController : UIViewController<DebugArtworkEffectDetailView>
    {

        public void Setup(DebugArtworkEffectElementUseCaseModel useCaseModel)
        {
            ActualView.Setup(useCaseModel);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            Dismiss();
        }
    }
}
