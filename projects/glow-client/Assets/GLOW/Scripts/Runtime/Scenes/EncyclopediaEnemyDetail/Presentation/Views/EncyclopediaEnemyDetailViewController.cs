using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-2_ファントムキャラ表示
    /// </summary>
    public class EncyclopediaEnemyDetailViewController : UIViewController<EncyclopediaEnemyDetailView>,
        IUnitAvatarPageListDelegate
    {
        public record Argument(IReadOnlyList<MasterDataId> MstEnemyIds, MasterDataId SelectedMstEnemyCharacterId);

        [Inject] IEncyclopediaEnemyDetailViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            ActualView.UnitAvatarPageListComponent.Delegate = this;
            ActualView.UnitAvatarPageListComponent.Setup(
                ViewFactory,
                this,
                Args.MstEnemyIds,
                Args.SelectedMstEnemyCharacterId
                );
        }

        public void SetupInfo(EncyclopediaEnemyDetailViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        void IUnitAvatarPageListDelegate.SwitchUnit(MasterDataId mstUnitId)
        {
            ViewDelegate.OnSwitchUnit(mstUnitId);
        }

        void IUnitAvatarPageListDelegate.WillTransitionTo()
        {
            ActualView.Interactable = false;
        }

        void IUnitAvatarPageListDelegate.DidFinishAnimating(bool finished, bool transitionCompleted)
        {
            ActualView.Interactable = finished;
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }

        [UIAction]
        void OnAvatarPageRightButtonTapped()
        {
            ActualView.UnitAvatarPageListComponent.ScrollToNextPage(scrollFinishSeSuppression:true);
        }

        [UIAction]
        void OnAvatarPageLeftButtonTapped()
        {
            ActualView.UnitAvatarPageListComponent.ScrollToPrevPage(scrollFinishSeSuppression:true);
        }
    }
}
