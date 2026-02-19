using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using GLOW.Scenes.OutpostEnhance.Presentation.Views.Component;
using UIKit;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views
{
    public class OutpostEnhanceViewController : HomeBaseViewController<OutpostEnhanceView>,
        IOutpostEnhanceArtworkListComponentDelegate
    {
        [Inject] IOutpostEnhanceViewDelegate ViewDelegate { get; }

        OutpostEnhanceViewModel _viewModel;
        OutpostEnhanceArtworkListViewModel _artworkListViewModel;

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public override void ViewWillDisappear(bool animated)
        {
            base.ViewWillDisappear(animated);
            ViewDelegate.OnViewWillDisappear();
        }

        public void Setup(OutpostEnhanceViewModel viewModel)
        {
            ActualView.ClearButtonChildren();
            foreach (var buttonModel in viewModel.Buttons)
            {
                CreateOutpostEnhanceTypeButtonComponent(buttonModel);
            }
        }

        public void SetNewOutpostArtworkBadge(NotificationBadge badge)
        {
            ActualView.SetNewOutpostArtworkBadge(badge);
        }

        public void SetOutpostArtwork(ArtworkAssetPath path)
        {
            ActualView.SetOutpostArtwork(path);
        }

        public void SetOutpostHp(HP hp)
        {
            ActualView.SetOutpostHp(hp);
        }

        public void UpdateButtons(OutpostEnhanceViewModel viewModel)
        {
            ActualView.SetupOutpostEnhanceTypeButtonComponentTexts(viewModel, ViewDelegate.OnGateTypeButtonSelected);
        }

        public void SetTouchGuard(bool isGuard)
        {
            ActualView.SetTouchGuard(isGuard);
        }

        public void SetGrayOut(bool isGrayOut)
        {
            ActualView.SetGrayOut(isGrayOut);
        }

        public void SetupOutpostEnhanceWindow(OutpostEnhanceTypeButtonViewModel viewModel)
        {
            ActualView.SetupEnhanceWindow(viewModel, ViewDelegate.OnEnhanceButtonSelected);
        }

        public void SetSkipButtonAction(System.Action action)
        {
            ActualView.SetSkipButtonAction(action);
        }

        public async UniTask PlayEnhanceEffectAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayEnhanceEffectAnimation(cancellationToken);
        }

        public async UniTask PlayEnhanceWindowAnimation(
            OutpostEnhanceResultViewModel model,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayEnhanceWindowAnimation(model, cancellationToken);
        }

        public void SkipEnhanceEffectAnimation()
        {
            ActualView.SkipEnhanceEffectAnimation();
        }

        public void EndAnimation()
        {
            ActualView.EndAnimation();
        }

        public void PlayEnhanceButtonListCellAppearanceAnimation()
        {
            ActualView.PlayEnhanceButtonListCellAppearanceAnimation();
        }

        public void HideEnhanceWindow(bool isArtworkChangeButtonInteractable = true)
        {
            ActualView.HideEnhanceWindow(isArtworkChangeButtonInteractable);
        }

        public void SetInteractableArtworkChangeButton(bool isInteractable)
        {
            ActualView.SetInteractableArtworkChangeButton(isInteractable);
        }

        public void ShowArtworkList(OutpostEnhanceArtworkListViewModel viewModel)
        {
            _artworkListViewModel = viewModel;
            ActualView.ShowArtworkList(this, viewModel, playAnimation:true);
        }

        public void HideArtworkList()
        {
            ActualView.HideArtworkList();
        }

        public void UpdateArtworkSelection(MasterDataId selectedMstArtworkId)
        {
            if (null == _artworkListViewModel) return;

            _artworkListViewModel = _artworkListViewModel.WithUpdatedSelection(selectedMstArtworkId);
            ActualView.ShowArtworkList(this, _artworkListViewModel, playAnimation: false);
        }

        void CreateOutpostEnhanceTypeButtonComponent(OutpostEnhanceTypeButtonViewModel viewModel)
        {
            ActualView.InstantiateOutpostEnhanceTypeButtonComponent(viewModel, ViewDelegate.OnGateTypeButtonSelected);
        }

        [UIAction]
        void OnBackLayerTapped()
        {
            HideEnhanceWindow();
        }

        [UIAction]
        void OnOutpostImageChanceButtonTapped()
        {
            ViewDelegate.ShowArtworkList();
        }

        [UIAction]
        void OnOutpostEnhanceButtonTapped()
        {
            ViewDelegate.ShowEnhanceList();
        }

        void IOutpostEnhanceArtworkListComponentDelegate.ChangeArtworkSelection(MasterDataId mstArtworkId)
        {
            ViewDelegate.ChangeArtworkSelection(mstArtworkId);
        }

        void IOutpostEnhanceArtworkListComponentDelegate.ShowArtworkDetail(
            MasterDataId mstArtworkId,
            OutpostEnhanceArtworkListViewModel artworkList)
        {
            var list = artworkList.Cells.Select(cell => cell.MstArtworkId).ToList();
            ViewDelegate.ShowArtworkDetail(mstArtworkId, list);
        }
    }
}
