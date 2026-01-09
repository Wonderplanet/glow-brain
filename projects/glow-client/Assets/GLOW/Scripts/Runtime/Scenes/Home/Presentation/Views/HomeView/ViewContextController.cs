using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.QuestContentTop.Presentation;
using GLOW.Scenes.ShopTab.Presentation.View;
using GLOW.Scenes.UnitTab.Presentation.Views;
using UIKit;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class ViewContextController
    {
        #region 画面最初になるViewController
        readonly ContextFirstViewController<HomeMainViewController> _homeMainViewController;
        readonly ContextFirstViewController<UnitTabViewController> _unitTabViewController;
        readonly ContextFirstViewController<QuestContentTopViewController> _questContentTopViewController;
        readonly ContextFirstViewController<ShopTabViewController> _shopTabViewController;
        readonly ContextFirstViewController<GachaListViewController> _gachaListViewController;

        public ContextFirstViewController<HomeMainViewController> HomeMainViewController => _homeMainViewController;
        public ContextFirstViewController<UnitTabViewController> UnitTabViewController => _unitTabViewController;
        public ContextFirstViewController<QuestContentTopViewController> QuestContentTopViewController => _questContentTopViewController;
        public ContextFirstViewController<ShopTabViewController> ShopTabViewController => _shopTabViewController;
        public ContextFirstViewController<GachaListViewController> GachaListViewController => _gachaListViewController;
        #endregion

        readonly List<UIViewController> _currentStackViewControllers = new();
        public UIViewController TopViewController => _currentStackViewControllers.LastOrDefault();
        public IReadOnlyList<UIViewController> CurrentStackViewControllers => _currentStackViewControllers;

        HomeContentTypes _currentContentType;
        public HomeContentTypes CurrentContentType => _currentContentType;

        public ViewContextController(
            ContextFirstViewController<HomeMainViewController> homeMainViewController,
            ContextFirstViewController<UnitTabViewController> unitTabViewController,
            ContextFirstViewController<QuestContentTopViewController> questContentTopViewController,
            ContextFirstViewController<ShopTabViewController> shopTabViewController,
            ContextFirstViewController<GachaListViewController> gachaListViewController)
        {
            _homeMainViewController = homeMainViewController;
            _unitTabViewController = unitTabViewController;
            _questContentTopViewController = questContentTopViewController;
            _shopTabViewController = shopTabViewController;
            _gachaListViewController = gachaListViewController;

            _homeMainViewController.ViewController.View.Hidden = true;
            _unitTabViewController.ViewController.View.Hidden = true;
            _questContentTopViewController.ViewController.View.Hidden = true;
            _shopTabViewController.ViewController.View.Hidden = true;
            _gachaListViewController.ViewController.View.Hidden = true;
        }

        public bool IsFirstView<T>(T checkViewController) where T : UIViewController
        {
            return checkViewController == _homeMainViewController.ViewController
                   || checkViewController == _unitTabViewController.ViewController
                   || checkViewController == _questContentTopViewController.ViewController
                   || checkViewController == _shopTabViewController.ViewController
                   || checkViewController == _gachaListViewController.ViewController;
        }

        public void SetCurrentContentType(HomeContentTypes type)
        {
            _currentContentType = type;
        }

        public void AddCurrentStackViewController(UIViewController controller)
        {
            _currentStackViewControllers.Add(controller);
        }
        public void RemoveCurrentStackViewController(UIViewController controller)
        {
            _currentStackViewControllers.Remove(controller);
        }
        public void ClearCurrentStackViewControllers()
        {
            _currentStackViewControllers.Clear();
        }

        public (UIViewController vc, HomeContentDisplayType viewType) GetFirstViewControllerAndShowHomeViewType(HomeContentTypes contentType)
        {
            return contentType switch
            {
                HomeContentTypes.Main => (_homeMainViewController.ViewController, ShowHomeViewType: _homeMainViewController.HomeContentDisplayType),
                HomeContentTypes.Character => (_unitTabViewController.ViewController, ShowHomeViewType: _unitTabViewController.HomeContentDisplayType),
                HomeContentTypes.Content => (_questContentTopViewController.ViewController, ShowHomeViewType: _questContentTopViewController.HomeContentDisplayType),
                HomeContentTypes.Shop => (_shopTabViewController.ViewController, ShowHomeViewType: _shopTabViewController.HomeContentDisplayType),
                HomeContentTypes.Gacha => (_gachaListViewController.ViewController, ShowHomeViewType: _gachaListViewController.HomeContentDisplayType),
                _ => throw new System.NotImplementedException("No define view type : " + contentType)
            };
        }
    }
}
