using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitList.Domain.UseCases;
using GLOW.Scenes.UnitList.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Presentation.Views;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Translators;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;
using GLOW.Scenes.UnitTab.Presentation.Interface;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitList.Presentation.Presenters
{
    public class UnitListPresenter : IUnitListViewDelegate
    {
        public UnitListPresenter(IHomeFooterDelegate homeFooterDelegate)
        {
            HomeFooterDelegate = homeFooterDelegate;
        }

        [Inject] UnitListViewController ViewController { get; }
        [Inject] GetUnitListUseCase GetUnitListUseCase { get; }
        [Inject] UpdateUnitListFilterUseCase UpdateUnitListFilterUseCase { get; }
        [Inject] GetUnitSortAndFilterUseCase GetUnitSortAndFilterUseCase { get; }
        [Inject] SetupUnitListConditionalFilterUseCase SetupUnitListConditionalFilterUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] UnitSortAndFilterDialogViewModelTranslator UnitSortAndFilterDialogViewModelTranslator { get; }

        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [InjectOptional] IUnitTabDelegate UnitTabDelegate { get; }

        float _scrollNormalizedPositionBeforeSelectUnit = 1.0f;


        void IUnitListViewDelegate.ViewWillAppear()
        {
            SetupUnitListConditionalFilterUseCase.Setup();
            UpdateUnitList(_scrollNormalizedPositionBeforeSelectUnit);
            ViewController.PlayCellAppearanceAnimation();
        }

        void IUnitListViewDelegate.OnSelectUnit(UserDataId userUnitId)
        {
            _scrollNormalizedPositionBeforeSelectUnit = ViewController.GetScrollVerticalNormalizedPosition();
            var model = GetUnitListUseCase.GetUnitList();
            var userUnitIds = model.Units.Select(unit => unit.UserUnitId).ToList();
            var args = new UnitViewController.Argument(userUnitId, userUnitIds);
            var viewController = ViewFactory.Create<UnitViewController, UnitViewController.Argument>(args);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: cancellationToken);
                HomeFooterDelegate.UpdateBadgeStatus();
                //TODO: 暫定対応...homeTOP > 編成ボタンを押すとZenjectExceptionになる対策
                UnitTabDelegate?.UpdateTabBadge();
            });
        }

        void IUnitListViewDelegate.OnSortAndFilter()
        {
            var useCaseModel =
                GetUnitSortAndFilterUseCase.GetUnitSortAndFilterDialogModel(UnitSortFilterCacheType.UnitList);
            var viewModel = UnitSortAndFilterDialogViewModelTranslator.ToTranslate(
                useCaseModel,
                MasterDataId.Empty, // ユニット一覧ではSpecialRuleの情報は必要ない
                InGameContentType.Stage);
            var argument = new UnitSortAndFilterDialogViewController.Argument(
                viewModel,
                null,
                () =>
                {
                    UpdateUnitList();
                });
            var dialogViewController = ViewFactory.Create<
                UnitSortAndFilterDialogViewController,
                UnitSortAndFilterDialogViewController.Argument>(argument);
            ViewController.PresentModally(dialogViewController);
        }

        void IUnitListViewDelegate.OnSortAscending()
        {
            UpdateUnitListFilterUseCase.UpdateSortOrder(UnitListSortOrder.Ascending, UnitSortFilterCacheType.UnitList);
            UpdateUnitList();
        }

        void IUnitListViewDelegate.OnSortDescending()
        {
            UpdateUnitListFilterUseCase.UpdateSortOrder(UnitListSortOrder.Descending, UnitSortFilterCacheType.UnitList);
            UpdateUnitList();
        }

        void UpdateUnitList(float scrollNormalizedPosition = 1.0f)
        {
            var model = GetUnitListUseCase.GetUnitList();
            var cellViewModels = model.Units.Select(TranslateCellViewModel).ToList();
            var viewModel = new UnitListViewModel(
                cellViewModels,
                model.CategoryModel);
            ViewController.Setup(viewModel);

            ViewController.SetScrollVerticalNormalizedPosition(scrollNormalizedPosition);
        }

        UnitListCellViewModel TranslateCellViewModel(UnitListCellModel model)
        {
            var characterIconViewModel = CharacterIconViewModelTranslator.Translate(model.CharacterIconModel);
            return new UnitListCellViewModel(
                model.UserUnitId,
                characterIconViewModel,
                model.NotificationBadge,
                model.SortType);
        }
    }
}
