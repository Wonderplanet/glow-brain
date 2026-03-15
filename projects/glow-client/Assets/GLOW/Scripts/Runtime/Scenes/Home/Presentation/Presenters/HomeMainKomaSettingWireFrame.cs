using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting;
using GLOW.Scenes.HomeMainKomaSettingFilter.Presentation;
using GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeMainKomaSettingWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }

        HomeMainKomaSettingViewController _viewController;

        public void SetViewController(HomeMainKomaSettingViewController viewController)
        {
            _viewController = viewController;
        }

        public void UnsetViewController()
        {
            _viewController = null;
        }

        public void ShowUnitSelectView(
            HomeMainKomaSettingUnitSelectViewController.Argument argument,
            Action<MasterDataId> onCloseAction)
        {
            var vc = ViewFactory.Create<
                HomeMainKomaSettingUnitSelectViewController,
                HomeMainKomaSettingUnitSelectViewController.Argument>(argument);

            vc.OnCloseAction = onCloseAction;
            _viewController?.PresentModally(vc);
        }

        public void ShowFilterView(Action onConfirm, Action onCancel)
        {
            var vc = ViewFactory.Create<HomeMainKomaSettingFilterViewController>();
            vc.OnConfirmAction = onConfirm;
            vc.OnCancelAction = onCancel;

            _viewController?.PresentModally(vc);
        }

        public void CloseFilterViewFromConfirmButton(
            HomeMainKomaSettingFilterViewController closeVc,
            Action viewControllerConfirmAction)
        {
            viewControllerConfirmAction?.Invoke();
            closeVc.Dismiss();
        }

        public void CloseFilterViewFromCancelButton(
            HomeMainKomaSettingFilterViewController closeVc,
            Action viewControllerCancelAction)
        {
            viewControllerCancelAction?.Invoke();
            closeVc.Dismiss();
        }
    }
}
