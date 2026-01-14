using GLOW.Scenes.UnitDetail.Presentation.Views;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.UnitDetailModal.Presentation.Presenters
{
    public class UnitDetailModalPresenter : IUnitDetailModalViewDelegate
    {
        [Inject] UnitDetailModalViewController ViewController { get; }
        public void OnCloseButtonTapped()
        {
            ViewController.Dismiss(false);
        }
    }
}
