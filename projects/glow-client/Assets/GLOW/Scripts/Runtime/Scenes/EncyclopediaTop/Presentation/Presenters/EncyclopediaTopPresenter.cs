using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaReward.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Presentation.Views;
using GLOW.Scenes.EncyclopediaTop.Domain.Models;
using GLOW.Scenes.EncyclopediaTop.Domain.UseCases;
using GLOW.Scenes.EncyclopediaTop.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaTop.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using WPFramework.Exceptions;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaTop.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-1_図鑑TOP画面
    /// </summary>
    public class EncyclopediaTopPresenter : IEncyclopediaTopViewDelegate
    {
        [Inject] EncyclopediaTopViewController ViewController { get; }
        [Inject] GetEncyclopediaTopUseCase GetEncyclopediaTopUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        void IEncyclopediaTopViewDelegate.OnViewWillAppear()
        {
            var model = GetEncyclopediaTopUseCase.GetSeriesList();
            var viewModel = Translate(model);
            ViewController.Setup(viewModel);
            ViewController.PlayCellAppearanceAnimation();
        }

        void IEncyclopediaTopViewDelegate.OnSelectSeries(MasterDataId mstSeriesId)
        {
            var argument = new EncyclopediaSeriesViewController.Argument(mstSeriesId);
            var viewController = ViewFactory.Create<EncyclopediaSeriesViewController, EncyclopediaSeriesViewController.Argument>(argument);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IEncyclopediaTopViewDelegate.OnSelectEncyclopediaBonusButton()
        {
            var viewController = ViewFactory.Create<EncyclopediaRewardViewController>();
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IEncyclopediaTopViewDelegate.OnSelectSortButton()
        {
            NotImpl.Handle();
        }
        void IEncyclopediaTopViewDelegate.OnClose()
        {
            HomeViewNavigation.TryPop();
        }

        EncyclopediaTopViewModel Translate(EncyclopediaTopModel model)
        {
            var cellViewModels = model.Cells.Select(TranslateCellViewModel).ToList();
            return new EncyclopediaTopViewModel(cellViewModels, model.TotalGrade, model.BonusBadge);
        }

        EncyclopediaTopSeriesCellViewModel TranslateCellViewModel(EncyclopediaTopSeriesCellModel model)
        {
            return new EncyclopediaTopSeriesCellViewModel(
                model.MstSeriesId,
                model.ImagePath,
                model.Name,
                model.MaxCount,
                model.UnlockCount,
                model.Badge);
        }
    }
}
