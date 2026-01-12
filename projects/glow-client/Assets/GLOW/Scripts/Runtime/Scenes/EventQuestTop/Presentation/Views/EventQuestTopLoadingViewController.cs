using System.Threading;
using Cysharp.Threading.Tasks;
using UIKit;

namespace GLOW.Scenes.EventQuestTop.Presentation.Views
{
    public class EventQuestTopLoadingViewController : UIViewController<EventQuestTopLoadingView>
    {
        public void Initlialize()
        {
            ActualView.FitView();
        }

        public async UniTask ShowLoadingView(CancellationToken ct)
        {
            await ActualView.ShowLoadingView(ct);
        }
        public async UniTask OutLoadingView(CancellationToken ct)
        {
            await ActualView.OutLoadingView(ct);
        }

        public void DismissLoadingView()
        {
            Dismiss();
        }
    }
}
