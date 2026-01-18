using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.OutpostEnhance.Presentation.Views;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.UnitList.Domain.UseCases;
using GLOW.Scenes.UnitList.Presentation.Views;
using GLOW.Scenes.UnitTab.Domain.Constants;
using GLOW.Scenes.UnitTab.Domain.UseCase;
using GLOW.Scenes.UnitTab.Presentation.Interface;
using GLOW.Scenes.UnitTab.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitTab.Presentation.Presenters
{
    public class UnitTabPresenter : IUnitTabViewDelegate, IUnitTabDelegate
    {
        [Inject] UnitTabViewController ViewController { get; }
        [InjectOptional] UnitTabViewController.Argument Argument { get; }
        [Inject] UpdateUnitListFilterUseCase UpdateUnitListFilterUseCase { get; }
        [Inject] GetUnitNoticeUseCase GetUnitNoticeUseCase { get; }
        [Inject] GetOutpostNoticeUseCase GetOutpostNoticeUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        UnitTabType _currentTab;

        void IUnitTabViewDelegate.ViewDidLoad()
        {
            SwitchContent(Argument?.Type ?? UnitTabType.UnitList);
        }

        void IUnitTabViewDelegate.UnloadView()
        {
        }

        void IUnitTabViewDelegate.UnitListTabSelect()
        {
            SwitchContent(UnitTabType.UnitList);
        }

        void IUnitTabViewDelegate.PartyFormationTabSelect()
        {
            SwitchContent(UnitTabType.PartyFormation);
        }

        void IUnitTabViewDelegate.OutpostEnhanceTabSelect()
        {
            SwitchContent(UnitTabType.OutpostEnhance);
        }

        public void UpdateTabBadge()
        {
            var unitBadge = GetUnitNoticeUseCase.GetUnitNotification();
            var outpostBadge = GetOutpostNoticeUseCase.GetUnitNotification();
            ViewController.SetBadge(unitBadge, outpostBadge);
        }

        void SwitchContent(UnitTabType next)
        {
            if (_currentTab == next) return;
            _currentTab = next;

            ViewController.CurrentContentViewController?.Dismiss();
            ViewController.SetTabOn(next);
            var viewController = CreateContentViewController(next);
            ViewController.ShowCurrentContent(viewController, worldPositionStays: false);
            SetBackgroundRectTop(next);

            HomeFooterDelegate.UpdateBadgeStatus();
            UpdateTabBadge();
        }

        void SetBackgroundRectTop(UnitTabType type)
        {
            switch (type)
            {
                case UnitTabType.OutpostEnhance:
                    ViewController.SetBackgroundRectTop(450);
                    break;
                case UnitTabType.PartyFormation:
                    ViewController.SetBackgroundRectTop(450);
                    break;
                case UnitTabType.UnitList:
                    ViewController.SetBackgroundRectTop(40);
                    break;
            }
        }

        UIViewController CreateContentViewController(UnitTabType tabType)
        {
            return tabType switch
            {
                UnitTabType.UnitList => ViewFactory.Create<UnitListViewController>(),
                UnitTabType.PartyFormation => ViewFactory.Create<PartyFormationViewController>(),
                UnitTabType.OutpostEnhance => ViewFactory.Create<OutpostEnhanceViewController>(),
                _ => throw new System.NotImplementedException(),
            };
        }
    }
}
