using System.Linq;
using GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-3_キャラ図鑑ランク
    /// </summary>
    public class EncyclopediaRewardViewController : HomeBaseViewController<EncyclopediaRewardView>
    {
        [Inject] IEncyclopediaRewardViewDelegate ViewDelegate { get; }

        EncyclopediaRewardViewModel _viewModel;

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void Setup(EncyclopediaRewardViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.Setup(viewModel, ViewDelegate.OnSelectReward, ViewDelegate.OnSelectLockReward);
        }

        [UIAction]
        void OnReceivedAllRewardButtonTapped()
        {
            var receiveCells = _viewModel.ReleasedCells
                .Where(vm => vm.Badge.Value)
                .ToList();
            ViewDelegate.OnReceiveAllRewardButtonTapped(receiveCells);
        }

        [UIAction]
        void OnShowEncyclopediaEffectButtonTapped()
        {
            ViewDelegate.OnShowEncyclopediaEffectButtonTapped();
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }

        [UIAction]
        void OnBackToHomeButtonTapped()
        {
            ViewDelegate.OnBackToHomeButtonTapped();
        }
    }
}
