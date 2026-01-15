using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-1_ヒーローキャラ表示
    /// </summary>
    public class EncyclopediaUnitDetailViewController : HomeBaseViewController<EncyclopediaUnitDetailView>,
        IUnitAvatarPageListDelegate
    {
        public record Argument(IReadOnlyList<MasterDataId> MstUnitIds, MasterDataId SelectedMstUnitId);

        [Inject] IEncyclopediaUnitDetailViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IUnitAttackViewInfoSetLoader UnitAttackViewInfoSetLoader { get; }
        [Inject] IUnitAttackViewInfoSetContainer UnitAttackViewInfoSetContainer { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            ActualView.UnitAvatarPageListComponent.Delegate = this;
            ActualView.UnitAvatarPageListComponent.Setup(
                ViewFactory,
                this,
                Args.MstUnitIds,
                Args.SelectedMstUnitId
                );
        }

        public void SetupInfo(EncyclopediaUnitDetailViewModel viewModel)
        {
            ActualView.Setup(viewModel);
            DoAsync.Invoke(View, async cancellationToken =>
            {
                if (!viewModel.UnitAssetKey.IsEmpty())
                {
                    await UnitAttackViewInfoSetLoader.Load(viewModel.UnitAssetKey, cancellationToken);
                    var unitAttackViewInfoSet = UnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(viewModel.UnitAssetKey);
                    
                    var attackViewInfo = unitAttackViewInfoSet != null
                        ? unitAttackViewInfoSet.SpecialAttackViewInfo
                        : null;
                    
                    var interactable = attackViewInfo != null && (attackViewInfo.CutInPrefab_background != null);
                    
                    ActualView.SetSpecialAttackButton(interactable);
                }
            });
        }

        void IUnitAvatarPageListDelegate.SwitchUnit(MasterDataId mstUnitId)
        {
            ViewDelegate.OnSwitchUnit(mstUnitId);
        }

        void IUnitAvatarPageListDelegate.WillTransitionTo()
        {
            ActualView.UserInteraction = false;
        }

        void IUnitAvatarPageListDelegate.DidFinishAnimating(bool finished, bool transitionCompleted)
        {
            ActualView.UserInteraction = finished;
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

        [UIAction]
        void OnPlaySpecialAttackButtonTapped()
        {
            ViewDelegate.OnPlaySpecialAttackButtonTapped();
        }
    }
}
