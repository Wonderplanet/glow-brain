using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView;
using GLOW.Scenes.ItemDetail.Domain.UseCase;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeStageInfoPresenter : IHomeStageInfoViewDelegate
    {
        [Inject] HomeStageInfoViewController ViewController { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ShowArtworkDetailUseCase ShowArtworkDetailUseCase { get; }

        public void OnViewDidLoad(HomeStageInfoViewModel viewModel)
        {
            ApplicationLog.Log(nameof(HomeStageInfoPresenter), nameof(IHomeStageInfoViewDelegate.OnViewDidLoad));

            ViewController.Initialize(viewModel);
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(HomeStageInfoPresenter), nameof(IHomeStageInfoViewDelegate.OnViewDidUnload));
        }

        public void OnClose()
        {
            ViewController.Dismiss();
        }

        public void OnTappedPlayerResourceIcon(PlayerResourceIconViewModel viewModel)
        {
            if (viewModel.ResourceType == ResourceType.ArtworkFragment)
            {
                var artworkId = ShowArtworkDetailUseCase.GetArtworkIdOfArtworkFragment(viewModel.Id);
                var artworkList = new List<MasterDataId>() { artworkId };

                var argument = new EncyclopediaArtworkDetailViewController.Argument(artworkList, artworkId);
                var viewController = ViewFactory.Create<EncyclopediaArtworkDetailViewController, EncyclopediaArtworkDetailViewController.Argument>(argument);
                viewController.OnClosed = () =>
                {
                    ViewController.ReopenStageInfoAction();
                };
                HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
                ViewController.Dismiss();
            }
            else
            {
                ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
            }
        }
    }
}
