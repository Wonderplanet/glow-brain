using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.TutorialTipDialog.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.ArtworkFormation.Domain.Models;
using GLOW.Scenes.ArtworkFormation.Domain.UseCases;
using GLOW.Scenes.ArtworkFormation.Presentation.Translator;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFormation.Presentation.Views;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Translator;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views;
using GLOW.Scenes.Home.Domain.Misc;
using GLOW.Scenes.Home.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Presenters
{
    public class ArtworkFormationPresenter : IArtworkFormationViewDelegate
    {
        [Inject] ArtworkFormationViewController ViewController { get; }
        [Inject] ArtworkFormationUseCase ArtworkFormationUseCase { get; }
        [Inject] GetArtworkSortAndFilterUseCase GetArtworkSortAndFilterUseCase { get; }
        [Inject] UpdateArtworkSortOrderUseCase UpdateArtworkSortOrderUseCase { get; }
        [Inject] IArtworkFormationApplier ArtworkFormationApplier { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] TutorialTipDialogViewWireFrame TutorialTipDialogViewWireFrame { get; }

        IReadOnlyList<MasterDataId> _assignedArtworkIds = new List<MasterDataId>();
        IReadOnlyList<ArtworkFormationArtworkModel> _allArtworkListModels = new List<ArtworkFormationArtworkModel>();
        ArtworkFormationListViewModel _listViewModel;
        bool _isFirstAppear = true;

        void IArtworkFormationViewDelegate.OnViewDidLoad()
        {
            // 空配列を渡すことでDBの編成済みIDsを優先ソートに使用する
            var useCaseModel = ArtworkFormationUseCase.GetArtworkFormationUseCaseModel(new List<MasterDataId>());
            var viewModel = ArtworkFormationViewModelTranslator.TranslateToViewModel(useCaseModel);

            _assignedArtworkIds = useCaseModel.AssignedFormationArtworkIds;
            _allArtworkListModels = useCaseModel.AllArtworkModels;
            _listViewModel = viewModel.ListViewModel;

            ViewController.SetUp(viewModel);
        }

        void IArtworkFormationViewDelegate.OnViewWillAppear()
        {
            // ViewDidLoadの直後のViewWillAppearはOnViewDidLoadで取得済みのデータを使うためスキップする
            if (_isFirstAppear)
            {
                _isFirstAppear = false;
                return;
            }

            // 編成状態(_assignedArtworkIds)を維持しつつ、編成中の原画を先頭にデフォルトソート順で一覧・パーティを更新する
            var useCaseModel = ArtworkFormationUseCase.GetArtworkFormationUseCaseModel(_assignedArtworkIds);
            var viewModel = ArtworkFormationViewModelTranslator.TranslateToViewModel(useCaseModel);

            _allArtworkListModels = useCaseModel.AllArtworkModels;

            // TranslateToViewModelはuseCaseModel.AssignedFormationArtworkIds(DB値)を使うため、
            // 画面上の編成状態(_assignedArtworkIds)を改めて反映する
            _listViewModel = viewModel.ListViewModel.WithUpdatedAllAssignments(_assignedArtworkIds);

            ViewController.UpdateArtworkList(_listViewModel);
            ViewController.UpdateArtworkPartyView();
        }

        void IArtworkFormationViewDelegate.OnViewWillDisappear()
        {
            // 原画編成の更新
            ArtworkFormationApplier.AsyncApplyArtworkFormation(_assignedArtworkIds);
        }

        void IArtworkFormationViewDelegate.OnIncompleteArtworkTapped(MasterDataId mstArtworkId)
        {
            // 未完成で編成不可の場合トースト表示のみ
            CommonToastWireFrame.ShowScreenCenterToast("この原画は未完成の為、編成することができません。");
        }

        void IArtworkFormationViewDelegate.OnArtworkTappedWhenPartyFull(MasterDataId mstArtworkId)
        {
            // 最大編成数の場合は何もしない
        }

        void IArtworkFormationViewDelegate.OnTapRemoveLastArtwork(MasterDataId mstArtworkId)
        {
            // 最後の1つを外そうとした場合は警告を表示
            CommonToastWireFrame.ShowScreenCenterToast("編成を空にすることはできません。");
        }

        void IArtworkFormationViewDelegate.OnArtworkAssignmentToggled(
            MasterDataId mstArtworkId,
            bool isCurrentlyAssigned)
        {
            if (isCurrentlyAssigned)
            {
                // 編成済みの場合は外す
                _assignedArtworkIds = _assignedArtworkIds.Where(id => id != mstArtworkId).ToList();
            }
            else
            {
                // 編成されていない場合は追加
                var updatedAssignedArtworkIds = _assignedArtworkIds.ToList();
                updatedAssignedArtworkIds.Add(mstArtworkId);
                _assignedArtworkIds = updatedAssignedArtworkIds;
            }

            // 編成の更新に合わせてViewModelも更新
            _listViewModel = _listViewModel.WithUpdatedAllAssignments(_assignedArtworkIds);
        }

        void IArtworkFormationViewDelegate.OnListCellLongTapped(
            MasterDataId mstArtworkId,
            List<ArtworkFormationListCellViewModel> cellViewModels)
        {
            var argument = new ArtworkEnhanceViewController.Argument(
                mstArtworkId,
                cellViewModels.Select(vm => vm.MstArtworkId).ToList());
            var viewController = ViewFactory.Create<
                ArtworkEnhanceViewController,
                ArtworkEnhanceViewController.Argument>(argument);

            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IArtworkFormationViewDelegate.OnRecommendButtonTapped()
        {
            // _listViewModel.CellViewModelsをソートする
            // 1. Gradeが高い順
            // 2. Rarityが高い順
            // 3. MstArtworkIdの昇順
            var recommendArtworkIds = _listViewModel.CellViewModels
                .Where(vm => vm.IsCompleted)
                .OrderByDescending(vm => vm.Grade)
                .ThenByDescending(vm => vm.Rarity)
                .ThenBy(vm => vm.MstArtworkId)
                .Take(10)
                .Select(vm => vm.MstArtworkId)
                .ToList();

            // おすすめ編成に更新
            _assignedArtworkIds = recommendArtworkIds;
            _listViewModel = _listViewModel.WithUpdatedAllAssignments(_assignedArtworkIds);

            ViewController.UpdateArtworkList(_listViewModel);
            ViewController.UpdateArtworkPartyView();
        }

        void IArtworkFormationViewDelegate.OnSortAndFilterButtonTapped()
        {
            var useCaseModel =
                GetArtworkSortAndFilterUseCase.GetArtworkSortAndFilterDialogModel(
                    ArtworkSortFilterCacheType.PartyFormation);
            var viewModel = ArtworkSortAndFilterDialogViewModelTranslator.Translate(useCaseModel);

            var argument = new ArtworkSortAndFilterDialogViewController.Argument(
                viewModel,
                null,
                UpdateArtworkList);
            var dialogViewController = ViewFactory.Create<
                ArtworkSortAndFilterDialogViewController,
                ArtworkSortAndFilterDialogViewController.Argument>(argument);
            ViewController.PresentModally(dialogViewController);
        }

        void IArtworkFormationViewDelegate.OnSortButtonTapped()
        {
            var switchSortOrder = _listViewModel.SortFilterCategoryModel.SortOrder == ArtworkListSortOrder.Ascending
                ? ArtworkListSortOrder.Descending
                : ArtworkListSortOrder.Ascending;

            UpdateArtworkSortOrderUseCase.UpdateSortOrder(switchSortOrder, ArtworkSortFilterCacheType.PartyFormation);
            UpdateArtworkList();
        }

        void UpdateArtworkList()
        {
            var useCaseModel = ArtworkFormationUseCase.GetArtworkFormationUseCaseModel(_assignedArtworkIds);
            var viewModel = ArtworkFormationViewModelTranslator.TranslateToViewModel(useCaseModel);

            _allArtworkListModels = useCaseModel.AllArtworkModels;

            // TranslateToViewModelはuseCaseModel.AssignedFormationArtworkIds(DB値)を使うため、
            // 画面上の編成状態(_assignedArtworkIds)を改めて反映する
            _listViewModel = viewModel.ListViewModel.WithUpdatedAllAssignments(_assignedArtworkIds);

            ViewController.UpdateArtworkList(_listViewModel);
        }

        void IArtworkFormationViewDelegate.OnHelpButtonTapped()
        {
            var functionName = HelpDialogIdDefinitions.ArtworkEffect;
            TutorialTipDialogViewWireFrame.ShowTutorialTipDialogs(ViewController, functionName);

        }

        ArtworkFormationPartyViewModel IArtworkFormationViewDelegate.GetPartyViewModel()
        {
            return ArtworkFormationViewModelTranslator.TranslateToPartyViewModel(
                _assignedArtworkIds,
                _allArtworkListModels);
        }

        ArtworkFormationListViewModel IArtworkFormationViewDelegate.GetListViewModel()
        {
            return _listViewModel;
        }

        ArtworkFormationListCellViewModel IArtworkFormationViewDelegate.GetListCellViewModel(MasterDataId mstArtworkId)
        {
            var cellViewModel = _listViewModel.CellViewModels.Find(vm => vm.MstArtworkId == mstArtworkId);
            return cellViewModel ?? ArtworkFormationListCellViewModel.Empty;
        }

        IReadOnlyList<MasterDataId> IArtworkFormationViewDelegate.GetAssignedArtworkIds()
        {
            return _assignedArtworkIds;
        }
    }
}
