using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.PvpRewardList.Domain.UseCase;
using GLOW.Scenes.PvpRewardList.Presentation.Enum;
using GLOW.Scenes.PvpRewardList.Presentation.Translator;
using GLOW.Scenes.PvpRewardList.Presentation.View;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;
using Zenject;

namespace GLOW.Scenes.PvpRewardList.Presentation.Presenter
{
    /// <summary>
    /// 043-01_報酬一覧画面
    /// </summary>
    public class PvpRewardListPresenter : IPvpRewardListViewDelegate
    {
        [Inject] PvpRewardListViewController ViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] ShowPvpRewardListUseCase ShowPvpRewardListUseCase { get; }
        
        PvpRewardListViewModel _pvpRewardListViewModel;
        
        PvpRewardListTabType _currentTabType = PvpRewardListTabType.Ranking;
        
        void IPvpRewardListViewDelegate.OnViewDidLoad()
        {
            var rewardListModel = ShowPvpRewardListUseCase.FetchPvpRewardListModel();
            _pvpRewardListViewModel = PvpRewardListViewModelTranslator.ToPvpRewardListViewModel(rewardListModel);
            
            ViewController.SetUpPvpRewardList(_pvpRewardListViewModel, PvpRewardListTabType.Ranking);
        }

        void IPvpRewardListViewDelegate.OnItemIconTapped(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        void IPvpRewardListViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IPvpRewardListViewDelegate.OnRankingTabButtonTapped()
        {
            if (_currentTabType == PvpRewardListTabType.Ranking) return;
            
            SetUpPvpRewardList(_pvpRewardListViewModel, PvpRewardListTabType.Ranking);
        }

        void IPvpRewardListViewDelegate.OnRankRewardTabButtonTapped()
        {
            if (_currentTabType == PvpRewardListTabType.RankClass) return;
            
            SetUpPvpRewardList(_pvpRewardListViewModel, PvpRewardListTabType.RankClass);
        }

        void IPvpRewardListViewDelegate.OnTotalScoreTabButtonTapped()
        {
            if (_currentTabType == PvpRewardListTabType.TotalScore) return;
            
            SetUpPvpRewardList(_pvpRewardListViewModel, PvpRewardListTabType.TotalScore);
        }

        void SetUpPvpRewardList(
            PvpRewardListViewModel viewModel,
            PvpRewardListTabType tabType)
        {
            _currentTabType = tabType;
            ViewController.SetUpPvpRewardList(viewModel, tabType);
        }
    }
}