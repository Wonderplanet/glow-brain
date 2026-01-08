using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UserEmblem.Presentation.ViewModels;
using UIKit;
using WonderPlanet.ResourceManagement;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UserEmblem.Presentation.Views
{
    public class UserEmblemViewController : UIViewController<UserEmblemView>, IEscapeResponder
    {
        [Inject] IAssetSource AssetSource { get; }
        [Inject] IUserEmblemViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.OnIconTapped = ViewDelegate.OnIconTapped;

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ViewDelegate.OnViewDidAppear();
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public override void ViewDidDisappear()
        {
            base.ViewDidDisappear();
            EscapeResponderRegistry.Unregister(this);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            UISoundEffector.Main.PlaySeEscape();
            ViewDelegate.OnCloseSelected();
            return true;
        }

        public void SetCurrentEmblem(HeaderUserEmblemCellViewModel viewModel)
        {
            bool isEmpty = viewModel.IsEmpty() || !AssetSource.IsAddressExists(viewModel.AssetPath.Value);
            ActualView.SetCurrentEmblem(viewModel, isEmpty);
        }

        public void Setup()
        {
            ActualView.Setup();
        }

        public void EmblemListReload(IReadOnlyList<HeaderUserEmblemCellViewModel> viewModels, MasterDataId selectedId)
        {
            ActualView.EmblemListReload(viewModels, selectedId);
        }
        
        public void PlayEmblemListCellAppearanceAnimation()
        {
            ActualView.PlayEmblemListCellAppearanceAnimation();
        }

        public void SetTabButtonSelected(EmblemType tabType, bool seriesNotice,
            bool eventNotice)
        {
            ActualView.SetTabButtonSelected(tabType, seriesNotice, eventNotice);
        }

        [UIAction]
        void OnSeriesTabSelected()
        {
            ViewDelegate.OnSeriesTabSelected();
        }

        [UIAction]
        void OnEventTabSelected()
        {
            ViewDelegate.OnEventTabSelected();
        }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
