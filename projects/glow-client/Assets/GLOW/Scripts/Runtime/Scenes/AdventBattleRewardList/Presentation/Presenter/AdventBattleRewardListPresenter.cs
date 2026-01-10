using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Domain.UseCase;
using GLOW.Scenes.AdventBattleRewardList.Presentation.Translator;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ValueObject;
using GLOW.Scenes.AdventBattleRewardList.Presentation.View;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using Zenject;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Presenter
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-5_報酬
    /// 　　44-5-1_報酬一覧画面
    /// </summary>
    public class AdventBattleRewardListPresenter : IAdventBattleRewardListViewDelegate
    {
        [Inject] AdventBattleRewardListViewController ViewController { get; }
        [Inject] AdventBattleRewardListViewController.Argument Argument { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] ShowAdventBattleRewardListUseCase ShowAdventBattleRewardListUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        
        CancellationToken AdventBattleRewardListCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        
        AdventBattleRewardListViewModel _adventBattleRewardListViewModel;
        RemainingTimeSpan _remainingTimeSpan;
        
        AdventBattleRewardListTabType _currentTabType = AdventBattleRewardListTabType.Ranking;
        
        void IAdventBattleRewardListViewDelegate.OnViewDidLoad()
        {
            var model = ShowAdventBattleRewardListUseCase.FetchAdventBattleRewardList(Argument.MstAdventBattleId);
            var viewModel = AdventBattleRewardListViewModelTranslator.ToAdventBattleRewardListViewModel(model);
            _adventBattleRewardListViewModel = viewModel;
            _remainingTimeSpan = viewModel.RemainingTimeSpan;

            _currentTabType = AdventBattleRewardListTabType.Ranking;
            ViewController.SetupAdventBattleRewardList(_adventBattleRewardListViewModel, _currentTabType);
            UpdateAdventBattleRemainingTime(AdventBattleRewardListCancellationToken).Forget();
        }

        void IAdventBattleRewardListViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IAdventBattleRewardListViewDelegate.OnRankingTabButtonTapped()
        {
            SetUpAdventBattleRewardListViewModel(AdventBattleRewardListTabType.Ranking);
        }
        
        void IAdventBattleRewardListViewDelegate.OnRankTabButtonTapped()
        {
            SetUpAdventBattleRewardListViewModel(AdventBattleRewardListTabType.Rank);
        }

        void IAdventBattleRewardListViewDelegate.OnRaidTabButtonTapped()
        {
            SetUpAdventBattleRewardListViewModel(AdventBattleRewardListTabType.Raid);
        }

        void IAdventBattleRewardListViewDelegate.OnItemIconTapped(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }
        
        void SetUpAdventBattleRewardListViewModel(AdventBattleRewardListTabType tabType)
        {
            // 現在のタブと同じ場合は何もしない
            if (_currentTabType == tabType) return;
            _currentTabType = tabType;
            
            ViewController.SetupAdventBattleRewardList(_adventBattleRewardListViewModel, _currentTabType);
        }

        async UniTask UpdateAdventBattleRemainingTime(CancellationToken cancellationToken)
        {
            while (true)
            {
                await UniTask.Delay(TimeSpan.FromSeconds(1), cancellationToken: cancellationToken);
                _remainingTimeSpan = _remainingTimeSpan.Subtract(TimeSpan.FromSeconds(1));
                ViewController.SetupRemainingTime(_remainingTimeSpan);
            }
        }
    }
}